<?php

namespace App\Models;

use App\Core\Model;

class Category extends Model
{
    protected string $table = 'user_categories';

    public function all(string $orderBy = 'sort_order', string $dir = 'ASC'): array
    {
        $stmt = $this->db->query("SELECT * FROM user_categories ORDER BY sort_order ASC, name ASC");
        return $stmt->fetchAll();
    }

    public function rateForUser(int $userId, string $roleType): float
    {
        $col = match($roleType) {
            'developer' => 'uc.developer_rate',
            'partner'   => 'uc.partner_rate',
            default     => 'uc.caller_rate',
        };
        $stmt = $this->db->prepare("
            SELECT {$col}
            FROM users u
            JOIN user_categories uc ON uc.id = u.category_id
            WHERE u.id = ?
        ");
        $stmt->execute([$userId]);
        $rate = $stmt->fetchColumn();
        return $rate !== false ? (float)$rate : 0.0;
    }
}
