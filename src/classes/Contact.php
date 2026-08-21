<?php
namespace CT275\Labs;

use PDO;

class Contact
{
    private ?PDO $db;

    public int $id = -1;
    public string $name = '';
    public string $phone = '';
    public string $notes = '';
    public string $created_at = '';
    public string $updated_at = '';

    public function __construct(?PDO $pdo)
    {
        $this->db = $pdo;
    }

    protected function fillFromDbRow(array $row): Contact
    {
        $this->id = $row['id'];
        $this->name = $row['name'];
        $this->phone = $row['phone'];
        $this->notes = $row['notes'];
        $this->created_at = $row['created_at'];
        $this->updated_at = $row['updated_at'];
        return $this;
    }

    public function all(): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts');
        $statement->execute();
        while ($row = $statement->fetch()) {
            $contact = new Contact($this->db);
            $contacts[] = $contact->fillFromDbRow($row);
        }
        return $contacts;
    }

    public function count(): int
    {
        $statement = $this->db->prepare('SELECT COUNT(*) FROM contacts');
        $statement->execute();
        return (int) $statement->fetchColumn();
    }

    public function paginate(int $offset = 0, int $limit = 10): array
    {
        $contacts = [];
        $statement = $this->db->prepare('SELECT * FROM contacts LIMIT :limit OFFSET :offset');
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->execute();
        while ($row = $statement->fetch()) {
            $contact = new Contact($this->db);
            $contacts[] = $contact->fillFromDbRow($row);
        }
        return $contacts;
    }

    public function find(int $id): ?Contact
    {
        $statement = $this->db->prepare('SELECT * FROM contacts WHERE id = :id');
        $statement->execute(['id' => $id]);
        if ($row = $statement->fetch()) {
            return $this->fillFromDbRow($row);
        }
        return null;
    }

    public function save(): bool
    {
        if ($this->id >= 0) {
            $statement = $this->db->prepare(
                'UPDATE contacts SET name = :name, phone = :phone, notes = :notes, updated_at = NOW() WHERE id = :id'
            );
            return $statement->execute([
                'name' => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes,
                'id' => $this->id
            ]);
        } else {
            $statement = $this->db->prepare(
                'INSERT INTO contacts (name, phone, notes, created_at, updated_at) VALUES (:name, :phone, :notes, NOW(), NOW())'
            );
            $result = $statement->execute([
                'name' => $this->name,
                'phone' => $this->phone,
                'notes' => $this->notes
            ]);
            if ($result) {
                $this->id = (int) $this->db->lastInsertId();
            }
            return $result;
        }
    }

    public function delete(): bool
    {
        $statement = $this->db->prepare('DELETE FROM contacts WHERE id = :id');
        return $statement->execute(['id' => $this->id]);
    }
}