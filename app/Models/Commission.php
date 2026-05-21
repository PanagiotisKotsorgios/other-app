<?php
// E:\call_center\app\Models\Commission.php

namespace App\Models;

use App\Core\Model;

class Commission extends Model
{
    protected string $table = 'commissions';

    public function findByDeal(int $dealId, string $roleType = 'caller'): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM commissions WHERE deal_id = ? AND role_type = ? LIMIT 1");
        $stmt->execute([$dealId, $roleType]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function forCaller(int $callerId, int $page = 1, int $perPage = 20): array
    {
        $offset    = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM commissions WHERE caller_id = ? AND role_type = 'caller'");
        $countStmt->execute([$callerId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, d.amount AS deal_amount, d.status AS deal_status,
                   b.company_name, s.name AS service_name
            FROM commissions c
            JOIN deals d      ON d.id = c.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN services s ON s.id = d.service_id
            WHERE c.caller_id = ? AND c.role_type = 'caller'
            ORDER BY c.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$callerId]);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function forDeveloper(int $devId, int $page = 1, int $perPage = 20): array
    {
        $offset    = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM commissions WHERE caller_id = ? AND role_type = 'developer'");
        $countStmt->execute([$devId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, d.amount AS deal_amount, d.status AS deal_status,
                   b.company_name, s.name AS service_name,
                   p.title AS project_title
            FROM commissions c
            JOIN deals d      ON d.id = c.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN services s  ON s.id = d.service_id
            LEFT JOIN projects p  ON p.deal_id = d.id
            WHERE c.caller_id = ? AND c.role_type = 'developer'
            ORDER BY c.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$devId]);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function forPartner(int $partnerId, int $page = 1, int $perPage = 20): array
    {
        $offset    = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM commissions WHERE caller_id = ? AND role_type = 'partner'");
        $countStmt->execute([$partnerId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, d.amount AS deal_amount, d.status AS deal_status,
                   b.company_name, s.name AS service_name
            FROM commissions c
            JOIN deals d      ON d.id = c.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN services s ON s.id = d.service_id
            WHERE c.caller_id = ? AND c.role_type = 'partner'
            ORDER BY c.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$partnerId]);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function listAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (isset($filters['is_paid']) && $filters['is_paid'] !== '') {
            $where[]  = 'c.is_paid = ?';
            $params[] = (int)$filters['is_paid'];
        }
        if (!empty($filters['caller_id'])) {
            $where[]  = 'c.caller_id = ?';
            $params[] = $filters['caller_id'];
        }
        if (!empty($filters['role_type'])) {
            $where[]  = 'c.role_type = ?';
            $params[] = $filters['role_type'];
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $countStmt = $this->db->prepare("SELECT COUNT(c.id) FROM commissions c {$whereStr}");
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, u.name AS caller_name, b.company_name, d.amount AS deal_amount,
                   s.name AS service_name
            FROM commissions c
            JOIN users u      ON u.id = c.caller_id
            JOIN deals d      ON d.id = c.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN services s ON s.id = d.service_id
            {$whereStr}
            ORDER BY c.created_at DESC
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

    public function markPaid(int $id, int $paidBy): bool
    {
        return $this->update($id, [
            'is_paid' => 1,
            'paid_at' => date('Y-m-d H:i:s'),
            'paid_by' => $paidBy,
        ]);
    }

    public function summaryStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COALESCE(SUM(amount), 0)                                                   AS total_commissions,
                COALESCE(SUM(CASE WHEN is_paid=1 THEN amount ELSE 0 END), 0)               AS paid,
                COALESCE(SUM(CASE WHEN is_paid=0 THEN amount ELSE 0 END), 0)               AS owed,
                COALESCE(SUM(CASE WHEN role_type='caller'    AND is_paid=0 THEN amount END), 0) AS owed_callers,
                COALESCE(SUM(CASE WHEN role_type='developer' AND is_paid=0 THEN amount END), 0) AS owed_developers,
                COALESCE(SUM(CASE WHEN role_type='partner'   AND is_paid=0 THEN amount END), 0) AS owed_partners
            FROM commissions
        ");
        return $stmt->fetch() ?: [];
    }

    public function owedPerCaller(): array
    {
        $stmt = $this->db->query("
            SELECT u.id, u.name, COALESCE(SUM(c.amount),0) AS owed
            FROM users u
            JOIN commissions c ON c.caller_id = u.id AND c.is_paid = 0 AND c.role_type = 'caller'
            WHERE u.role = 'caller'
            GROUP BY u.id, u.name
            ORDER BY owed DESC
        ");
        return $stmt->fetchAll();
    }

    public function owedPerRole(): array
    {
        $stmt = $this->db->query("
            SELECT role_type,
                   COALESCE(SUM(amount), 0)                                          AS total,
                   COALESCE(SUM(CASE WHEN is_paid=1 THEN amount ELSE 0 END), 0)      AS paid,
                   COALESCE(SUM(CASE WHEN is_paid=0 THEN amount ELSE 0 END), 0)      AS owed
            FROM commissions
            GROUP BY role_type
        ");
        $rows   = $stmt->fetchAll();
        $result = ['caller' => [], 'developer' => [], 'partner' => []];
        foreach ($rows as $row) {
            $result[$row['role_type']] = $row;
        }
        return $result;
    }

    public function forUser(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset    = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM commissions WHERE caller_id = ?");
        $countStmt->execute([$userId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT c.*, d.amount AS deal_amount, d.status AS deal_status,
                   b.company_name, s.name AS service_name,
                   p.title AS project_title
            FROM commissions c
            JOIN deals d      ON d.id = c.deal_id
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN services s ON s.id = d.service_id
            LEFT JOIN projects p ON p.deal_id = d.id
            WHERE c.caller_id = ?
            ORDER BY c.created_at DESC
            LIMIT {$perPage} OFFSET {$offset}
        ");
        $stmt->execute([$userId]);

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => (int)ceil($total / $perPage),
        ];
    }

    public function createForRole(int $dealId, int $userId, float $amount, float $rate, string $roleType): int
    {
        // Check if already exists
        $existing = $this->findByDeal($dealId, $roleType);
        if ($existing) return $existing['id'];

        return $this->create([
            'deal_id'   => $dealId,
            'caller_id' => $userId,
            'amount'    => round($amount, 2),
            'rate'      => $rate,
            'role_type' => $roleType,
            'is_paid'   => 0,
        ]);
    }

    public function earningsPerDeveloper(): array
    {
        $stmt = $this->db->query("
            SELECT u.id, u.name,
                   COALESCE(SUM(c.amount), 0)                                    AS total_earned,
                   COALESCE(SUM(CASE WHEN c.is_paid=0 THEN c.amount ELSE 0 END), 0) AS owed
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id AND ur.role = 'developer'
            LEFT JOIN commissions c ON c.caller_id = u.id AND c.role_type = 'developer'
            GROUP BY u.id, u.name
            ORDER BY total_earned DESC
        ");
        return $stmt->fetchAll();
    }

    public function earningsPerPartner(): array
    {
        $stmt = $this->db->query("
            SELECT u.id, u.name,
                   COALESCE(SUM(c.amount), 0)                                    AS total_earned,
                   COALESCE(SUM(CASE WHEN c.is_paid=0 THEN c.amount ELSE 0 END), 0) AS owed
            FROM users u
            JOIN user_roles ur ON ur.user_id = u.id AND ur.role = 'partner'
            LEFT JOIN commissions c ON c.caller_id = u.id AND c.role_type = 'partner'
            GROUP BY u.id, u.name
            ORDER BY total_earned DESC
        ");
        return $stmt->fetchAll();
    }
}
