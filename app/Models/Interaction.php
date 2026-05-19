<?php

namespace App\Models;

use App\Core\Model;

class Interaction extends Model
{
    protected string $table = 'interactions';

    public function forBusiness(int $businessId, int $callerId = 0): array
    {
        $sql    = "SELECT i.*, u.name AS caller_name FROM interactions i JOIN users u ON u.id = i.caller_id WHERE i.business_id = ?";
        $params = [$businessId];

        if ($callerId) {
            $sql   .= " AND i.caller_id = ?";
            $params[] = $callerId;
        }
        $sql .= " ORDER BY i.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        foreach ($rows as &$row) {
            $row['services'] = $this->getServices((int)$row['id']);
        }
        return $rows;
    }

    public function createWithServices(array $data, array $serviceIds): int
    {
        $id = $this->create($data);
        if ($serviceIds) {
            $stmt = $this->db->prepare("INSERT INTO interaction_services (interaction_id, service_id) VALUES (?, ?)");
            foreach ($serviceIds as $sid) {
                $stmt->execute([$id, (int)$sid]);
            }
        }
        return $id;
    }

    public function getServices(int $interactionId): array
    {
        $stmt = $this->db->prepare("
            SELECT s.* FROM services s
            JOIN interaction_services is2 ON is2.service_id = s.id
            WHERE is2.interaction_id = ?
        ");
        $stmt->execute([$interactionId]);
        return $stmt->fetchAll();
    }

    public function callerStatsByPeriod(int $callerId, string $period = 'all'): array
    {
        $dateFilter = match($period) {
            'daily'   => "AND DATE(i.created_at) = CURDATE()",
            'weekly'  => "AND YEARWEEK(i.created_at, 1) = YEARWEEK(CURDATE(), 1)",
            'monthly' => "AND YEAR(i.created_at) = YEAR(CURDATE()) AND MONTH(i.created_at) = MONTH(CURDATE())",
            default   => ''
        };

        $stmt = $this->db->prepare("
            SELECT
                COUNT(CASE WHEN type = 'call'      THEN 1 END) AS calls,
                COUNT(CASE WHEN type = 'email'     THEN 1 END) AS emails,
                COUNT(CASE WHEN type = 'offer'     THEN 1 END) AS offers,
                COUNT(CASE WHEN type = 'demo'      THEN 1 END) AS demos,
                COUNT(CASE WHEN type = 'follow_up' THEN 1 END) AS follow_ups,
                COUNT(*) AS total
            FROM interactions i
            WHERE i.caller_id = ? {$dateFilter}
        ");
        $stmt->execute([$callerId]);
        return $stmt->fetch() ?: [];
    }

    public function adminStats(): array
    {
        $stmt = $this->db->query("
            SELECT
                COUNT(CASE WHEN type = 'call'  THEN 1 END) AS total_calls,
                COUNT(CASE WHEN type = 'email' THEN 1 END) AS total_emails,
                COUNT(CASE WHEN type = 'offer' THEN 1 END) AS total_offers,
                COUNT(CASE WHEN type = 'demo'  THEN 1 END) AS total_demos,
                COUNT(*) AS total_interactions
            FROM interactions
        ");
        return $stmt->fetch() ?: [];
    }

    public function chartData(int $days = 30): array
    {
        $stmt = $this->db->prepare("
            SELECT DATE(created_at) AS date, type, COUNT(*) AS cnt
            FROM interactions
            WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY DATE(created_at), type
            ORDER BY date
        ");
        $stmt->execute([$days]);
        return $stmt->fetchAll();
    }
}
