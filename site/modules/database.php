<?php
declare(strict_types=1);

class Database {
    private PDO $pdo;

    public function __construct(string $path) {
        $this->pdo = new PDO('sqlite:' . $path);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function Execute(string $sql): bool {
        return $this->pdo->exec($sql) !== false;
    }

    public function Fetch(string $sql): array {
        $stmt = $this->pdo->query($sql);
        return $stmt ? $stmt->fetchAll() : [];
    }

    public function Create(string $table, array $data): int {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($data);
        return (int) $this->pdo->lastInsertId();
    }

    public function Read(string $table, int $id): array {
        $stmt = $this->pdo->prepare("SELECT * FROM $table WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: [];
    }

    public function Update(string $table, int $id, array $data): bool {
        $set = [];
        foreach ($data as $key => $value) {
            $set[] = "$key = :$key";
        }
        $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE id = :id";
        $data['id'] = $id;
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($data);
    }

    public function Delete(string $table, int $id): bool {
        $stmt = $this->pdo->prepare("DELETE FROM $table WHERE id = :id");
        return $stmt->execute(['id' => $id]);
    }

    public function Count(string $table): int {
        $stmt = $this->pdo->query("SELECT COUNT(*) as cnt FROM $table");
        return (int) ($stmt->fetch()['cnt'] ?? 0);
    }
}