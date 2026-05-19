<?php
// E:\call_center\app\Models\Expense.php

namespace App\Models;

use App\Core\Model;

class Expense extends Model
{
    protected string $table = 'expenses';

    public function listAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['project_id'])) {
            $where[]  = 'e.project_id = ?';
            $params[] = $filters['project_id'];
        }
        if (!empty($filters['deal_id'])) {
            $where[]  = 'e.deal_id = ?';
            $params[] = $filters['deal_id'];
        }
        if (!empty($filters['category'])) {
            $where[]  = 'e.category = ?';
            $params[] = $filters['category'];
        }
        if (!empty($filters['search'])) {
            $like     = "%{$filters['search']}%";
            $where[]  = 'e.description LIKE ?';
            $params[] = $like;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $countSql  = "SELECT COUNT(e.id) FROM expenses e {$whereStr}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "
            SELECT e.*,
                   p.title AS project_title,
                   b.company_name,
                   u.name AS created_by_name
            FROM expenses e
            LEFT JOIN projects p    ON p.id = e.project_id
            LEFT JOIN deals d       ON d.id = e.deal_id
            LEFT JOIN businesses b  ON b.id = d.business_id
            LEFT JOIN users u       ON u.id = e.created_by
            {$whereStr}
            ORDER BY e.created_at DESC
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

    public function forProject(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT e.*, u.name AS created_by_name
            FROM expenses e
            LEFT JOIN users u ON u.id = e.created_by
            WHERE e.project_id = ?
            ORDER BY e.expense_date DESC, e.created_at DESC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function totalByCategory(): array
    {
        $stmt = $this->db->query("
            SELECT category, COALESCE(SUM(amount), 0) AS total, COUNT(*) AS cnt
            FROM expenses
            GROUP BY category
            ORDER BY total DESC
        ");
        return $stmt->fetchAll();
    }

    public function summaryStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COALESCE(SUM(amount), 0)                                         AS total_expenses,
                COUNT(id)                                                         AS expense_count,
                COALESCE(SUM(CASE WHEN category='hosting'       THEN amount END), 0) AS hosting,
                COALESCE(SUM(CASE WHEN category='software'      THEN amount END), 0) AS software,
                COALESCE(SUM(CASE WHEN category='hardware'      THEN amount END), 0) AS hardware,
                COALESCE(SUM(CASE WHEN category='subcontractor' THEN amount END), 0) AS subcontractor,
                COALESCE(SUM(CASE WHEN category='marketing'     THEN amount END), 0) AS marketing,
                COALESCE(SUM(CASE WHEN category='salary'        THEN amount END), 0) AS salary,
                COALESCE(SUM(CASE WHEN category='tax'           THEN amount END), 0) AS tax,
                COALESCE(SUM(CASE WHEN category='other'         THEN amount END), 0) AS other_cat
            FROM expenses
        ");
        return $stmt->fetch() ?: [];
    }
}
