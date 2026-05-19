<?php

namespace App\Core;

abstract class Model
{
    protected \PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = \Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir  = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $stmt = $this->db->query("SELECT * FROM `{$this->table}` ORDER BY `{$orderBy}` {$dir}");
        return $stmt->fetchAll();
    }

    public function create(array $data): int
    {
        $cols = implode('`, `', array_keys($data));
        $phs  = implode(', ', array_fill(0, count($data), '?'));
        $stmt = $this->db->prepare("INSERT INTO `{$this->table}` (`{$cols}`) VALUES ({$phs})");
        $stmt->execute(array_values($data));
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sets = implode(' = ?, ', array_map(fn($c) => "`{$c}`", array_keys($data))) . ' = ?';
        $stmt = $this->db->prepare("UPDATE `{$this->table}` SET {$sets} WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([...array_values($data), $id]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = ?");
        return $stmt->execute([$id]);
    }

    public function count(string $where = '', array $params = []): int
    {
        $sql  = "SELECT COUNT(*) FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int)$stmt->fetchColumn();
    }

    public function paginate(int $page, int $perPage, string $where = '', array $params = [], string $orderBy = 'id', string $dir = 'ASC'): array
    {
        $dir    = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        $offset = ($page - 1) * $perPage;
        $total  = $this->count($where, $params);
        $sql    = "SELECT * FROM `{$this->table}`";
        if ($where) $sql .= " WHERE {$where}";
        $sql .= " ORDER BY `{$orderBy}` {$dir} LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return [
            'data'        => $stmt->fetchAll(),
            'total'       => $total,
            'per_page'    => $perPage,
            'current_page'=> $page,
            'last_page'   => (int)ceil($total / $perPage),
        ];
    }
}
