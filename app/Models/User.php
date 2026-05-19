<?php
// E:\call_center\app\Models\User.php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected string $table = 'users';

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function callers(): array
    {
        $stmt = $this->db->query("SELECT * FROM users WHERE role = 'caller' AND is_active = 1 ORDER BY name");
        return $stmt->fetchAll();
    }

    public function callersPaginated(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        if ($search) {
            return $this->paginate($page, $perPage, "role = 'caller' AND (name LIKE ? OR email LIKE ?)", ["%$search%", "%$search%"], 'name');
        }
        return $this->paginate($page, $perPage, "role = 'caller'", [], 'name');
    }

    public function createUser(array $data): int
    {
        $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
        return $this->create($data);
    }

    public function updatePassword(int $id, string $password): bool
    {
        return $this->update($id, ['password' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12])]);
    }

    // ── Role management (user_roles pivot) ──────────────────────────────────

    public function hasRole(int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("SELECT 1 FROM user_roles WHERE user_id = ? AND role = ? LIMIT 1");
        $stmt->execute([$userId, $role]);
        return (bool)$stmt->fetchColumn();
    }

    public function addRole(int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("
            INSERT IGNORE INTO user_roles (user_id, role) VALUES (?, ?)
        ");
        return $stmt->execute([$userId, $role]);
    }

    public function removeRole(int $userId, string $role): bool
    {
        $stmt = $this->db->prepare("DELETE FROM user_roles WHERE user_id = ? AND role = ?");
        return $stmt->execute([$userId, $role]);
    }

    public function getRoles(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT role FROM user_roles WHERE user_id = ?");
        $stmt->execute([$userId]);
        return array_column($stmt->fetchAll(), 'role');
    }

    public function syncRoles(int $userId, array $roles): void
    {
        $this->db->prepare("DELETE FROM user_roles WHERE user_id = ?")->execute([$userId]);
        foreach ($roles as $role) {
            $this->addRole($userId, $role);
        }
    }

    // ── Developers ───────────────────────────────────────────────────────────

    public function developers(): array
    {
        $stmt = $this->db->query("
            SELECT u.*
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id AND ur.role = 'developer'
            WHERE u.is_active = 1
            ORDER BY u.name
        ");
        return $stmt->fetchAll();
    }

    public function developersPaginated(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $where  = ["ur.role = 'developer'", "u.is_active = 1"];
        $params = [];
        if ($search) {
            $where[]  = "(u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("
            SELECT COUNT(u.id) FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            WHERE {$whereStr}
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            WHERE {$whereStr}
            ORDER BY u.name
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    // ── Partners ─────────────────────────────────────────────────────────────

    public function partners(): array
    {
        $stmt = $this->db->query("
            SELECT u.*
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id AND ur.role = 'partner'
            WHERE u.is_active = 1
            ORDER BY u.name
        ");
        return $stmt->fetchAll();
    }

    public function partnersPaginated(int $page = 1, int $perPage = 20, string $search = ''): array
    {
        $where  = ["ur.role = 'partner'", "u.is_active = 1"];
        $params = [];
        if ($search) {
            $where[]  = "(u.name LIKE ? OR u.email LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        $whereStr = implode(' AND ', $where);
        $offset   = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("
            SELECT COUNT(u.id) FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            WHERE {$whereStr}
        ");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT u.* FROM users u
            JOIN user_roles ur ON ur.user_id = u.id
            WHERE {$whereStr}
            ORDER BY u.name
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute($params);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    // ── Stats ────────────────────────────────────────────────────────────────

    public function callerStats(int $callerId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT ca.business_id)                                  AS assigned_businesses,
                COUNT(DISTINCT CASE WHEN i.type='call'  THEN i.id END)         AS total_calls,
                COUNT(DISTINCT CASE WHEN i.type='email' THEN i.id END)         AS total_emails,
                COUNT(DISTINCT CASE WHEN i.type='offer' THEN i.id END)         AS total_offers,
                COUNT(DISTINCT CASE WHEN i.type='demo'  THEN i.id END)         AS total_demos,
                COUNT(DISTINCT CASE WHEN d.status='pending'     THEN d.id END) AS deals_pending,
                COUNT(DISTINCT CASE WHEN d.status='approved'    THEN d.id END) AS deals_approved,
                COUNT(DISTINCT CASE WHEN d.status='in_progress' THEN d.id END) AS deals_in_progress,
                COALESCE(SUM(CASE WHEN d.status IN ('approved','in_progress','completed') THEN d.amount ELSE 0 END), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN c.is_paid = 0 AND c.role_type = 'caller' THEN c.amount ELSE 0 END), 0) AS commission_owed,
                COALESCE(SUM(CASE WHEN c.is_paid = 0 AND c.role_type = 'developer' THEN c.amount ELSE 0 END), 0) AS dev_commission_owed
            FROM users u
            LEFT JOIN caller_assignments ca ON ca.caller_id = u.id
            LEFT JOIN interactions i        ON i.caller_id = u.id
            LEFT JOIN deals d               ON d.caller_id = u.id
            LEFT JOIN commissions c         ON c.caller_id = u.id
            WHERE u.id = ?
        ");
        $stmt->execute([$callerId]);
        return $stmt->fetch() ?: [];
    }

    public function developerStats(int $devId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT p.id)                                                     AS total_projects,
                COUNT(DISTINCT CASE WHEN p.status = 'in_progress' THEN p.id END)        AS in_progress,
                COUNT(DISTINCT CASE WHEN p.status = 'completed' THEN p.id END)          AS completed,
                COUNT(DISTINCT CASE WHEN p.deadline < CURDATE() AND p.status NOT IN ('completed','on_hold') THEN p.id END) AS overdue,
                COALESCE(SUM(p.budget), 0)                                               AS total_budget,
                COALESCE((
                    SELECT SUM(c2.amount) FROM commissions c2
                    WHERE c2.caller_id = ? AND c2.role_type = 'developer'
                ), 0)                                                                    AS commission_earned,
                COALESCE((
                    SELECT SUM(c3.amount) FROM commissions c3
                    WHERE c3.caller_id = ? AND c3.role_type = 'developer' AND c3.is_paid = 0
                ), 0)                                                                    AS commission_owed
            FROM projects p
            WHERE p.developer_id = ?
        ");
        $stmt->execute([$devId, $devId, $devId]);
        return $stmt->fetch() ?: [];
    }

    public function partnerStats(int $partnerId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(DISTINCT d.id)                                                      AS total_referrals,
                COALESCE(SUM(CASE WHEN d.status IN ('approved','in_progress','completed') THEN d.amount ELSE 0 END), 0) AS revenue_generated,
                COALESCE((
                    SELECT SUM(c.amount) FROM commissions c
                    WHERE c.caller_id = ? AND c.role_type = 'partner'
                ), 0)                                                                     AS commission_earned,
                COALESCE((
                    SELECT SUM(c2.amount) FROM commissions c2
                    WHERE c2.caller_id = ? AND c2.role_type = 'partner' AND c2.is_paid = 0
                ), 0)                                                                     AS commission_owed
            FROM deals d
            WHERE d.partner_id = ?
        ");
        $stmt->execute([$partnerId, $partnerId, $partnerId]);
        return $stmt->fetch() ?: [];
    }

    public function rankingTable(): array
    {
        $stmt = $this->db->query("
            SELECT
                u.id, u.name, u.email,
                COUNT(DISTINCT ca.business_id)  AS assigned,
                COUNT(DISTINCT i.id)            AS interactions,
                COUNT(DISTINCT d.id)            AS deals,
                COALESCE(SUM(CASE WHEN d.status IN ('approved','in_progress','completed') THEN d.amount ELSE 0 END), 0) AS revenue,
                COALESCE(SUM(CASE WHEN c.role_type = 'caller' THEN c.amount ELSE 0 END), 0) AS commissions
            FROM users u
            LEFT JOIN caller_assignments ca ON ca.caller_id = u.id
            LEFT JOIN interactions i  ON i.caller_id  = u.id
            LEFT JOIN deals d         ON d.caller_id  = u.id
            LEFT JOIN commissions c   ON c.caller_id  = u.id
            WHERE u.role = 'caller' AND u.is_active = 1
            GROUP BY u.id, u.name, u.email
            ORDER BY revenue DESC
        ");
        return $stmt->fetchAll();
    }

    public function partnerRankingTable(): array
    {
        $stmt = $this->db->query("
            SELECT
                u.id, u.name, u.email,
                COUNT(DISTINCT d.id)                                                      AS referrals,
                COALESCE(SUM(CASE WHEN d.status IN ('approved','in_progress','completed') THEN d.amount ELSE 0 END), 0) AS revenue_generated,
                COALESCE(SUM(c.amount), 0)                                                AS commission_earned,
                COALESCE(SUM(CASE WHEN c.is_paid = 0 THEN c.amount ELSE 0 END), 0)       AS commission_owed
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id AND ur.role = 'partner'
            LEFT JOIN deals d         ON d.partner_id = u.id
            LEFT JOIN commissions c   ON c.caller_id = u.id AND c.role_type = 'partner'
            WHERE u.is_active = 1
            GROUP BY u.id, u.name, u.email
            ORDER BY revenue_generated DESC
        ");
        return $stmt->fetchAll();
    }
}
