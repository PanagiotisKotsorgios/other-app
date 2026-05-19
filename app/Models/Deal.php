<?php
// E:\call_center\app\Models\Deal.php

namespace App\Models;

use App\Core\Model;

define('DEVELOPER_COMMISSION_RATE', 20);
define('PARTNER_COMMISSION_RATE', 20);

class Deal extends Model
{
    protected string $table = 'deals';

    public function withDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, b.company_name, b.city, b.category,
                   u.name AS caller_name, s.name AS service_name,
                   a.name AS approved_by_name,
                   dev.name AS developer_name, dev.email AS developer_email,
                   partner.name AS partner_name, partner.email AS partner_email
            FROM deals d
            JOIN businesses b      ON b.id = d.business_id
            JOIN users u           ON u.id = d.caller_id
            LEFT JOIN services s   ON s.id = d.service_id
            LEFT JOIN users a      ON a.id = d.approved_by
            LEFT JOIN users dev    ON dev.id = d.developer_id
            LEFT JOIN users partner ON partner.id = d.partner_id
            WHERE d.id = ?
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
            $where[]  = 'd.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['caller_id'])) {
            $where[]  = 'd.caller_id = ?';
            $params[] = $filters['caller_id'];
        }
        if (!empty($filters['developer_id'])) {
            $where[]  = 'd.developer_id = ?';
            $params[] = $filters['developer_id'];
        }
        if (!empty($filters['partner_id'])) {
            $where[]  = 'd.partner_id = ?';
            $params[] = $filters['partner_id'];
        }
        if (!empty($filters['search'])) {
            $like     = "%{$filters['search']}%";
            $where[]  = '(b.company_name LIKE ? OR u.name LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(d.id) FROM deals d
                     JOIN businesses b ON b.id=d.business_id
                     JOIN users u ON u.id=d.caller_id
                     {$whereStr}";
        $stmt = $this->db->prepare($countSql);
        $stmt->execute($params);
        $total = (int)$stmt->fetchColumn();

        $sql = "
            SELECT d.*, b.company_name, u.name AS caller_name, s.name AS service_name,
                   dev.name AS developer_name, partner.name AS partner_name
            FROM deals d
            JOIN businesses b          ON b.id = d.business_id
            JOIN users u               ON u.id = d.caller_id
            LEFT JOIN services s       ON s.id = d.service_id
            LEFT JOIN users dev        ON dev.id = d.developer_id
            LEFT JOIN users partner    ON partner.id = d.partner_id
            {$whereStr}
            ORDER BY d.created_at DESC
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

    public function forCaller(int $callerId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters['caller_id'] = $callerId;
        return $this->listAll($filters, $page, $perPage);
    }

    public function forPartner(int $partnerId, array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $filters['partner_id'] = $partnerId;
        return $this->listAll($filters, $page, $perPage);
    }

    public function approve(int $id, int $approvedBy): bool
    {
        $ok = $this->update($id, [
            'status'      => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
        ]);

        if ($ok) {
            $deal            = $this->find($id);
            $commissionModel = new Commission();

            // Caller commission (10%)
            $commissionModel->createForRole(
                $id,
                $deal['caller_id'],
                $deal['amount'] * COMMISSION_RATE / 100,
                COMMISSION_RATE,
                'caller'
            );

            // Developer commission (20%) if assigned
            if (!empty($deal['developer_id'])) {
                $commissionModel->createForRole(
                    $id,
                    $deal['developer_id'],
                    $deal['amount'] * DEVELOPER_COMMISSION_RATE / 100,
                    DEVELOPER_COMMISSION_RATE,
                    'developer'
                );
            }

            // Partner commission (20%) if assigned
            if (!empty($deal['partner_id'])) {
                $commissionModel->createForRole(
                    $id,
                    $deal['partner_id'],
                    $deal['amount'] * PARTNER_COMMISSION_RATE / 100,
                    PARTNER_COMMISSION_RATE,
                    'partner'
                );
            }
        }

        return $ok;
    }

    public function signContract(int $dealId): bool
    {
        $ok = $this->update($dealId, ['contract_signed' => 1]);
        if ($ok) {
            // Create project record if it doesn't exist
            $stmt = $this->db->prepare("SELECT id FROM projects WHERE deal_id = ? LIMIT 1");
            $stmt->execute([$dealId]);
            if (!$stmt->fetchColumn()) {
                $deal = $this->withDetails($dealId);
                $projectModel = new Project();
                $projectModel->create([
                    'deal_id'     => $dealId,
                    'developer_id'=> $deal['developer_id'] ?? null,
                    'title'       => 'Project for ' . ($deal['company_name'] ?? 'Deal #' . $dealId),
                    'description' => null,
                    'status'      => $deal['developer_id'] ? 'in_progress' : 'awaiting_assignment',
                    'priority'    => 'medium',
                    'budget'      => $deal['amount'] ?? 0,
                ]);
            }
        }
        return $ok;
    }

    public function assignDeveloper(int $dealId, int $developerId): bool
    {
        $ok = $this->update($dealId, ['developer_id' => $developerId]);
        if ($ok) {
            // Also update the project if it exists
            $stmt = $this->db->prepare("UPDATE projects SET developer_id = ?, status = 'in_progress' WHERE deal_id = ?");
            $stmt->execute([$developerId, $dealId]);

            // Create developer commission if deal is approved
            $deal = $this->find($dealId);
            if ($deal && in_array($deal['status'], ['approved', 'in_progress', 'completed'])) {
                $commModel = new Commission();
                $commModel->createForRole(
                    $dealId,
                    $developerId,
                    $deal['amount'] * DEVELOPER_COMMISSION_RATE / 100,
                    DEVELOPER_COMMISSION_RATE,
                    'developer'
                );
            }
        }
        return $ok;
    }

    public function assignPartner(int $dealId, int $partnerId): bool
    {
        $ok = $this->update($dealId, ['partner_id' => $partnerId]);
        if ($ok) {
            $deal = $this->find($dealId);
            if ($deal && in_array($deal['status'], ['approved', 'in_progress', 'completed'])) {
                $commModel = new Commission();
                $commModel->createForRole(
                    $dealId,
                    $partnerId,
                    $deal['amount'] * PARTNER_COMMISSION_RATE / 100,
                    PARTNER_COMMISSION_RATE,
                    'partner'
                );
            }
        }
        return $ok;
    }

    public function adminStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(CASE WHEN status='pending'     THEN 1 END) AS pending,
                COUNT(CASE WHEN status='approved'    THEN 1 END) AS approved,
                COUNT(CASE WHEN status='in_progress' THEN 1 END) AS in_progress,
                COUNT(CASE WHEN status='completed'   THEN 1 END) AS completed,
                COUNT(CASE WHEN status='rejected'    THEN 1 END) AS rejected,
                COALESCE(SUM(CASE WHEN status IN ('approved','in_progress','completed') THEN amount ELSE 0 END),0) AS total_revenue
            FROM deals
        ");
        return $stmt->fetch() ?: [];
    }

    public function revenueChart(int $months = 6): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                   SUM(amount) AS revenue, COUNT(*) AS cnt
            FROM deals
            WHERE status IN ('approved','in_progress','completed')
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function revenueByMonth(int $months = 12): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS month,
                   COALESCE(SUM(amount), 0) AS revenue,
                   COUNT(*) AS deal_count
            FROM deals
            WHERE status IN ('approved','in_progress','completed')
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY DATE_FORMAT(created_at, '%Y-%m')
            ORDER BY month
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public function topRevenueDeals(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT d.*, b.company_name, u.name AS caller_name, s.name AS service_name,
                   (SELECT COALESCE(SUM(amount),0) FROM expenses WHERE deal_id = d.id) AS total_expenses,
                   (SELECT COALESCE(SUM(amount),0) FROM commissions WHERE deal_id = d.id) AS total_commissions
            FROM deals d
            JOIN businesses b ON b.id = d.business_id
            JOIN users u      ON u.id = d.caller_id
            LEFT JOIN services s ON s.id = d.service_id
            WHERE d.status IN ('approved','in_progress','completed')
            ORDER BY d.amount DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function perProjectFinancials(): array
    {
        $stmt = $this->db->query("
            SELECT d.id, d.amount AS deal_amount, d.status,
                   b.company_name,
                   p.title AS project_title, p.budget,
                   COALESCE((SELECT SUM(amount) FROM expenses  WHERE deal_id = d.id), 0) AS expenses,
                   COALESCE((SELECT SUM(amount) FROM commissions WHERE deal_id = d.id), 0) AS commissions
            FROM deals d
            JOIN businesses b ON b.id = d.business_id
            LEFT JOIN projects p ON p.deal_id = d.id
            WHERE d.status IN ('approved','in_progress','completed')
            ORDER BY d.amount DESC
            LIMIT 50
        ");
        return $stmt->fetchAll();
    }
}
