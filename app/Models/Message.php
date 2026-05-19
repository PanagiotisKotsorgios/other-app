<?php

namespace App\Models;

use App\Core\Model;

class Message extends Model
{
    protected string $table = 'messages';

    public function inbox(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ?");
        $countStmt->execute([$userId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.receiver_id = ?
            ORDER BY m.created_at DESC
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

    public function sent(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE sender_id = ?");
        $countStmt->execute([$userId]);
        $total = (int)$countStmt->fetchColumn();

        $stmt = $this->db->prepare("
            SELECT m.*, u.name AS receiver_name
            FROM messages m
            JOIN users u ON u.id = m.receiver_id
            WHERE m.sender_id = ?
            ORDER BY m.created_at DESC
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

    public function thread(int $messageId): array
    {
        $stmt = $this->db->prepare("
            SELECT m.*, u.name AS sender_name
            FROM messages m
            JOIN users u ON u.id = m.sender_id
            WHERE m.id = ? OR m.parent_id = ?
            ORDER BY m.created_at ASC
        ");
        $stmt->execute([$messageId, $messageId]);
        return $stmt->fetchAll();
    }

    public function markRead(int $messageId, int $userId): void
    {
        $this->db->prepare("UPDATE messages SET is_read=1, read_at=NOW() WHERE id=? AND receiver_id=?")
                 ->execute([$messageId, $userId]);
    }

    public function unreadCount(int $userId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id=? AND is_read=0");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public function send(int $senderId, int $receiverId, string $subject, string $body, ?int $parentId = null): int
    {
        return $this->create([
            'sender_id'   => $senderId,
            'receiver_id' => $receiverId,
            'subject'     => $subject,
            'body'        => $body,
            'parent_id'   => $parentId,
        ]);
    }
}
