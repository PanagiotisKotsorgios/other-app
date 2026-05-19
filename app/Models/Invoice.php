<?php
// E:\call_center\app\Models\Invoice.php

namespace App\Models;

use App\Core\Model;

class Invoice extends Model
{
    protected string $table = 'invoices';

    public function forDeal(int $dealId): array
    {
        $stmt = $this->db->prepare("
            SELECT i.*, u.name AS uploader_name
            FROM invoices i
            LEFT JOIN users u ON u.id = i.uploaded_by
            WHERE i.deal_id = ?
            ORDER BY i.created_at DESC
        ");
        $stmt->execute([$dealId]);
        return $stmt->fetchAll();
    }

    public function listAll(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $where  = [];
        $params = [];

        if (!empty($filters['status'])) {
            $where[]  = 'i.status = ?';
            $params[] = $filters['status'];
        }
        if (!empty($filters['deal_id'])) {
            $where[]  = 'i.deal_id = ?';
            $params[] = $filters['deal_id'];
        }
        if (!empty($filters['caller_id'])) {
            $where[]  = 'd.caller_id = ?';
            $params[] = $filters['caller_id'];
        }
        if (!empty($filters['search'])) {
            $like     = "%{$filters['search']}%";
            $where[]  = '(i.invoice_no LIKE ? OR b.company_name LIKE ?)';
            $params[] = $like;
            $params[] = $like;
        }

        $whereStr = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $offset   = ($page - 1) * $perPage;

        $countSql = "SELECT COUNT(i.id)
                     FROM invoices i
                     JOIN deals d      ON d.id = i.deal_id
                     JOIN businesses b ON b.id = d.business_id
                     {$whereStr}";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        $sql = "
            SELECT i.*, b.company_name, d.caller_id, u.name AS caller_name,
                   up.name AS uploader_name
            FROM invoices i
            JOIN deals d      ON d.id = i.deal_id
            JOIN businesses b ON b.id = d.business_id
            JOIN users u      ON u.id = d.caller_id
            LEFT JOIN users up ON up.id = i.uploaded_by
            {$whereStr}
            ORDER BY i.created_at DESC
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

    public function markPaid(int $id): bool
    {
        return $this->update($id, [
            'status'  => 'paid',
            'paid_at' => date('Y-m-d'),
        ]);
    }

    public function totalStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COALESCE(SUM(total_amount), 0)                                        AS total_invoiced,
                COALESCE(SUM(CASE WHEN status = 'paid' THEN total_amount ELSE 0 END), 0) AS collected,
                COALESCE(SUM(CASE WHEN status != 'paid' THEN total_amount ELSE 0 END), 0) AS outstanding,
                COUNT(id)                                                              AS invoice_count,
                COUNT(CASE WHEN status = 'paid' THEN 1 END)                           AS paid_count,
                COUNT(CASE WHEN status = 'draft' THEN 1 END)                          AS draft_count,
                COUNT(CASE WHEN status = 'sent' THEN 1 END)                           AS sent_count
            FROM invoices
        ");
        return $stmt->fetch() ?: [];
    }

    public function findWithDetails(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT i.*, b.company_name, d.caller_id, u.name AS caller_name
            FROM invoices i
            JOIN deals d      ON d.id = i.deal_id
            JOIN businesses b ON b.id = d.business_id
            JOIN users u      ON u.id = d.caller_id
            WHERE i.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
