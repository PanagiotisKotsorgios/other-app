<?php

namespace App\Models;

use App\Core\Model;

class PartnerDocument extends Model
{
    protected string $table = 'partner_documents';

    public function forPartnerType(int $partnerId, string $docType): array
    {
        $stmt = $this->db->prepare("
            SELECT pd.*, u.name AS uploader_name
            FROM partner_documents pd
            JOIN users u ON u.id = pd.uploaded_by
            WHERE pd.partner_id = ? AND pd.doc_type = ?
            ORDER BY pd.created_at DESC
        ");
        $stmt->execute([$partnerId, $docType]);
        return $stmt->fetchAll();
    }

    public function forPartner(int $partnerId): array
    {
        $stmt = $this->db->prepare("
            SELECT pd.*, u.name AS uploader_name
            FROM partner_documents pd
            JOIN users u ON u.id = pd.uploaded_by
            WHERE pd.partner_id = ?
            ORDER BY pd.doc_type, pd.created_at DESC
        ");
        $stmt->execute([$partnerId]);
        return $stmt->fetchAll();
    }
}
