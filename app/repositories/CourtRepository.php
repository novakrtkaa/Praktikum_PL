<?php

class CourtRepository
{
    private $db;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function all()
    {
        return $this->db->query("SELECT * FROM courts ORDER BY id DESC")->fetchAll();
    }

    public function find($id)
    {
        $stmt = $this->db->query("SELECT * FROM courts WHERE id=?", [$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $sql = "INSERT INTO courts (name, type) VALUES (?, ?)";
        return $this->db->query($sql, [$data['name'], $data['type']]);
    }

    public function delete($id)
    {
        $sql = "DELETE FROM courts WHERE id=?";
        return $this->db->query($sql, [$id]);
    }
}
