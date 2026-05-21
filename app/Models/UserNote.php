<?php

namespace App\Models;

use App\Core\Model;

class UserNote extends Model
{
    protected string $table = 'user_notes';

    public function forUser(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT n.*, a.name AS author_name
            FROM user_notes n
            JOIN users a ON a.id = n.created_by
            WHERE n.user_id = ?
            ORDER BY n.is_pinned DESC, n.created_at DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function add(int $userId, int $createdBy, string $body, bool $isPinned = false): int
    {
        return $this->create([
            'user_id'    => $userId,
            'created_by' => $createdBy,
            'body'       => $body,
            'is_pinned'  => $isPinned ? 1 : 0,
        ]);
    }
}
