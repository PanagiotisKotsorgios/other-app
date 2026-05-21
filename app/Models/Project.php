<?php
// E:\call_center\app\Models\Project.php

namespace App\Models;

use App\Core\Model;

class Project extends Model
{
    protected string $table = 'projects';

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT p.*,
                   d.amount AS deal_amount, d.status AS deal_status, d.caller_id,
                   d.contract_signed,
                   b.company_name, b.city, b.category AS biz_category, b.phone AS biz_phone,
                   caller.name AS caller_name,
                   dev.name AS developer_name, dev.email AS developer_email,
                   s.name AS service_name
            FROM projects p
            JOIN deals d        ON d.id = p.deal_id
            JOIN businesses b   ON b.id = d.business_id
            JOIN users caller   ON caller.id = d.caller_id
            LEFT JOIN users dev ON dev.id = p.developer_id
            LEFT JOIN services s ON s.id = d.service_id
            WHERE p.id = ?
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function listAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'p.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['developer_id'])) {
            $where[]  = 'p.developer_id = ?';
            $params[] = $filters['developer_id'];
        }
        if (!empty($filters['priority'])) {
            $where[]  = 'p.priority = ?';
            $params[] = $filters['priority'];
        }
        if (!empty($filters['search'])) {
            $like     = "%{$filters['search']}%";
            $where[]  = '(p.title LIKE ? OR b.company_name LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(p.id)
                     FROM projects p
                     JOIN deals d      ON d.id = p.deal_id
                     JOIN businesses b ON b.id = d.business_id
                     {$whereStr}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "
            SELECT p.*,
                   b.company_name,
                   dev.name AS developer_name,
                   d.amount AS deal_amount,
                   (SELECT COUNT(*) FROM project_phases ph WHERE ph.project_id = p.id) AS phase_count,
                   (SELECT COUNT(*) FROM project_phases ph WHERE ph.project_id = p.id AND ph.status = 'completed') AS phases_done
            FROM projects p
            JOIN deals d      ON d.id = p.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN users dev ON dev.id = p.developer_id
            {$whereStr}
            ORDER BY p.created_at DESC
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

    public function forDeveloper(int $devId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters['developer_id'] = $devId;
        return $this->listAll($filters, $page, $perPage);
    }

    // ── Phases ───────────────────────────────────────────────────────────────

    public function addPhase(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_phases (project_id, name, description, status, order_num, due_date)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['project_id'],
            $data['name'],
            $data['description'] ?? null,
            $data['status'] ?? 'pending',
            $data['order_num'] ?? 0,
            $data['due_date'] ?? null,
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function updatePhase(int $phaseId, array $data): bool
    {
        $sets   = [];
        $params = [];
        $allowed = ['name', 'description', 'status', 'order_num', 'due_date', 'completed_at'];
        foreach ($allowed as $col) {
            if (array_key_exists($col, $data)) {
                $sets[]   = "`{$col}` = ?";
                $params[] = $data[$col];
            }
        }
        if (empty($sets)) return false;
        // Auto-set completed_at when marking completed
        if (($data['status'] ?? '') === 'completed' && !array_key_exists('completed_at', $data)) {
            $sets[]   = '`completed_at` = ?';
            $params[] = date('Y-m-d H:i:s');
        }
        $params[] = $phaseId;
        $stmt = $this->db->prepare("UPDATE project_phases SET " . implode(', ', $sets) . " WHERE id = ?");
        return $stmt->execute($params);
    }

    public function deletePhase(int $phaseId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM project_phases WHERE id = ?");
        return $stmt->execute([$phaseId]);
    }

    public function getPhases(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM project_phases WHERE project_id = ? ORDER BY order_num ASC, id ASC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function getPhase(int $phaseId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM project_phases WHERE id = ? LIMIT 1");
        $stmt->execute([$phaseId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ── Notes ────────────────────────────────────────────────────────────────

    public function addNote(array $data): int
    {
        $stmt = $this->db->prepare("
            INSERT INTO project_notes (project_id, user_id, body, is_internal)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['project_id'],
            $data['user_id'],
            $data['body'],
            (int)($data['is_internal'] ?? 0),
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function getNotes(int $projectId, bool $includeInternal = true): array
    {
        $sql = "
            SELECT n.*, u.name AS author_name, u.role AS author_role
            FROM project_notes n
            JOIN users u ON u.id = n.user_id
            WHERE n.project_id = ?
        ";
        if (!$includeInternal) {
            $sql .= " AND n.is_internal = 0";
        }
        $sql .= " ORDER BY n.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    // ── Stats ────────────────────────────────────────────────────────────────

    public function developerStats(int $devId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(p.id)                                                         AS total_projects,
                COUNT(CASE WHEN p.status = 'in_progress' THEN 1 END)               AS in_progress,
                COUNT(CASE WHEN p.status = 'completed' THEN 1 END)                 AS completed,
                COUNT(CASE WHEN p.deadline < CURDATE() AND p.status NOT IN ('completed','on_hold') THEN 1 END) AS overdue,
                COALESCE(SUM(p.budget), 0)                                          AS total_budget,
                COALESCE((
                    SELECT SUM(c.amount) FROM commissions c
                    WHERE c.caller_id = ? AND c.role_type = 'developer'
                ), 0)                                                               AS commission_earned
            FROM projects p
            WHERE p.developer_id = ?
        ");
        $stmt->execute([$devId, $devId]);
        return $stmt->fetch() ?: [];
    }

    public function adminProjectStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(id)                                                       AS total,
                COUNT(CASE WHEN status = 'awaiting_assignment' THEN 1 END)     AS awaiting,
                COUNT(CASE WHEN status = 'in_progress' THEN 1 END)             AS in_progress,
                COUNT(CASE WHEN status = 'testing' THEN 1 END)                 AS testing,
                COUNT(CASE WHEN status = 'on_hold' THEN 1 END)                 AS on_hold,
                COUNT(CASE WHEN status = 'completed' THEN 1 END)               AS completed,
                COUNT(CASE WHEN deadline < CURDATE() AND status NOT IN ('completed','on_hold') THEN 1 END) AS overdue,
                COALESCE(SUM(budget), 0)                                        AS total_budget
            FROM projects
        ");
        return $stmt->fetch() ?: [];
    }

    public function upcomingDeadlines(int $days = 7): array
    {
        $stmt = $this->db->prepare("
            SELECT p.*, b.company_name, dev.name AS developer_name
            FROM projects p
            JOIN deals d      ON d.id = p.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN users dev ON dev.id = p.developer_id
            WHERE p.deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL ? DAY)
              AND p.status NOT IN ('completed','on_hold')
            ORDER BY p.deadline ASC
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }

    public function getAssignments(int $projectId): array
    {
        $stmt = $this->db->prepare("
            SELECT pa.*, u.name AS user_name, u.email AS user_email,
                   a.name AS assigned_by_name,
                   uc.name AS category_name, uc.color AS category_color
            FROM project_assignments pa
            JOIN users u ON u.id = pa.user_id
            LEFT JOIN users a ON a.id = pa.assigned_by
            LEFT JOIN user_categories uc ON uc.id = u.category_id
            WHERE pa.project_id = ?
            ORDER BY pa.assigned_at ASC
        ");
        $stmt->execute([$projectId]);
        return $stmt->fetchAll();
    }

    public function addAssignment(int $projectId, int $userId, string $roleType, int $assignedBy, string $notes = ''): void
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO project_assignments
              (project_id, user_id, role_type, assigned_by, notes)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$projectId, $userId, $roleType, $assignedBy, $notes]);
    }

    public function removeAssignment(int $assignmentId): void
    {
        $this->db->prepare("DELETE FROM project_assignments WHERE id = ?")->execute([$assignmentId]);
    }
}
