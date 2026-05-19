<?php

namespace App\Models;

use App\Core\Model;

class Business extends Model
{
    protected string $table = 'businesses';

    public function search(string $q, int $page = 1, int $perPage = 20): array
    {
        $like = "%{$q}%";
        return $this->paginate($page, $perPage,
            "company_name LIKE ? OR contact_name LIKE ? OR city LIKE ? OR category LIKE ?",
            [$like, $like, $like, $like], 'company_name'
        );
    }

    public function filter(array $filters, int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['city'])) {
            $where[]  = 'b.city = ?';
            $params[] = $filters['city'];
        }
        if (!empty($filters['category'])) {
            $where[]  = 'b.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['status'])) {
            $where[]  = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['caller_id'])) {
            $where[]  = 'ca.caller_id = ?';
            $params[] = $filters['caller_id'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(b.company_name LIKE ? OR b.contact_name LIKE ? OR b.email LIKE ?)';
            $like      = "%{$filters['search']}%";
            $params    = array_merge($params, [$like, $like, $like]);
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $totalSql = "SELECT COUNT(DISTINCT b.id) FROM businesses b LEFT JOIN caller_assignments ca ON ca.business_id = b.id {$whereStr}";
        $stmt = $this->db->prepare($totalSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $sql = "
            SELECT b.*, ANY_VALUE(u.name) AS assigned_caller
            FROM businesses b
            LEFT JOIN caller_assignments ca ON ca.business_id = b.id
            LEFT JOIN users u ON u.id = ca.caller_id
            {$whereStr}
            GROUP BY b.id
            ORDER BY b.company_name
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function assignedToCaller(int $callerId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = ['ca.caller_id = ?'];
        $params = [$callerId];

        if (!empty($filters['status'])) {
            $where[]  = 'b.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $like     = "%{$filters['search']}%";
            $where[]  = '(b.company_name LIKE ? OR b.contact_name LIKE ? OR b.city LIKE ?)';
            $params   = array_merge($params, [$like, $like, $like]);
        }

        $whereStr = 'WHERE ' . implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(b.id) FROM businesses b INNER JOIN caller_assignments ca ON ca.business_id = b.id {$whereStr}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $sql = "
            SELECT b.*
            FROM businesses b
            INNER JOIN caller_assignments ca ON ca.business_id = b.id
            {$whereStr}
            ORDER BY b.company_name
            LIMIT {$perPage} OFFSET {$offset}
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function unassigned(int $page = 1, int $perPage = 20): array
    {
        return $this->paginate($page, $perPage,
            "id NOT IN (SELECT business_id FROM caller_assignments)", [], 'company_name'
        );
    }

    public function cities(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT city FROM businesses WHERE city IS NOT NULL AND city <> '' ORDER BY city");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function categories(): array
    {
        $stmt = $this->db->query("SELECT DISTINCT category FROM businesses WHERE category IS NOT NULL AND category <> '' ORDER BY category");
        return $stmt->fetchAll(\PDO::FETCH_COLUMN);
    }

    public function statsPerCity(): array
    {
        $stmt = $this->db->query("
            SELECT b.city,
                COUNT(b.id)                             AS total_businesses,
                COUNT(DISTINCT ca.caller_id)            AS callers,
                COUNT(DISTINCT d.id)                    AS deals,
                COALESCE(SUM(d.amount),0)               AS revenue
            FROM businesses b
            LEFT JOIN caller_assignments ca ON ca.business_id = b.id
            LEFT JOIN deals d ON d.business_id = b.id AND d.status IN ('approved','in_progress','completed')
            WHERE b.city IS NOT NULL AND b.city <> ''
            GROUP BY b.city
            ORDER BY revenue DESC
            LIMIT 20
        ");
        return $stmt->fetchAll();
    }

    public function statsPerCategory(): array
    {
        $stmt = $this->db->query("
            SELECT b.category,
                COUNT(b.id)                  AS total_businesses,
                COUNT(DISTINCT d.id)         AS deals,
                COALESCE(SUM(d.amount),0)    AS revenue
            FROM businesses b
            LEFT JOIN deals d ON d.business_id = b.id AND d.status IN ('approved','in_progress','completed')
            WHERE b.category IS NOT NULL AND b.category <> ''
            GROUP BY b.category
            ORDER BY revenue DESC
            LIMIT 20
        ");
        return $stmt->fetchAll();
    }

    public function bulkAssign(array $businessIds, int $callerId, int $assignedBy): int
    {
        $count = 0;
        $stmt  = $this->db->prepare("
            INSERT IGNORE INTO caller_assignments (business_id, caller_id, assigned_by)
            VALUES (?, ?, ?)
        ");
        foreach ($businessIds as $bid) {
            $stmt->execute([$bid, $callerId, $assignedBy]);
            $count += $stmt->rowCount();
        }
        return $count;
    }

    public function randomAssign(int $callerId, int $qty, int $assignedBy): int
    {
        $stmt = $this->db->prepare("
            SELECT id FROM businesses
            WHERE id NOT IN (SELECT business_id FROM caller_assignments)
            ORDER BY RAND()
            LIMIT ?
        ");
        $stmt->execute([$qty]);
        $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        return $this->bulkAssign($ids, $callerId, $assignedBy);
    }
}
