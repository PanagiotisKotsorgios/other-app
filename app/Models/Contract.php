<?php
// E:\call_center\app\Models\Contract.php

namespace App\Models;

use App\Core\Model;

class Contract extends Model
{
    protected string $table = 'contracts';

    public function forDeal(int $dealId): array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name AS uploader_name
            FROM contracts c
            JOIN users u ON u.id = c.uploaded_by
            WHERE c.deal_id = ?
            ORDER BY c.uploaded_at DESC
        ");
        $stmt->execute([$dealId]);
        return $stmt->fetchAll();
    }

    public function findWithUploader(int $id): ?array
    {
        $stmt = $this->db->prepare("
            SELECT c.*, u.name AS uploader_name, d.business_id
            FROM contracts c
            JOIN users u ON u.id = c.uploaded_by
            JOIN deals d ON d.id = c.deal_id
            WHERE c.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
