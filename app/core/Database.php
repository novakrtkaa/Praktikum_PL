<?php

class Database
{
    protected array $cfg;
    protected $conn;

    public function __construct(array $cfg = [])
    {
        if (empty($cfg)) {
            $config = require __DIR__ . '/../../config/config.php';
            $cfg = $config['db'];
        }
        
        $this->cfg = $cfg;
        $this->connect();
    }

    protected function connect(): void
    {
        $host = $this->cfg['host'] ?? '127.0.0.1';
        $user = $this->cfg['user'] ?? 'root';
        $pass = $this->cfg['pass'] ?? '';
        $name = $this->cfg['name'] ?? '';
        
        $this->conn = mysqli_connect($host, $user, $pass, $name);
        
        if (!$this->conn) {
            http_response_code(500);
            die('Database connection failed: ' . mysqli_connect_error());
        }
        
        mysqli_set_charset($this->conn, 'utf8mb4');
    }

    public function getConnection()
    {
        return $this->conn;
    }

    public function query($sql, $params = [])
    {
        if (empty($params)) {
            $result = mysqli_query($this->conn, $sql);
            if (!$result) {
                die('Query error: ' . mysqli_error($this->conn));
            }
            return new DatabaseResult($result, $this->conn);
        }

        $stmt = mysqli_prepare($this->conn, $sql);
        if (!$stmt) {
            die('Prepare error: ' . mysqli_error($this->conn));
        }

        if (!empty($params)) {
            $types = '';
            foreach ($params as $param) {
                if (is_int($param)) $types .= 'i';
                elseif (is_float($param)) $types .= 'd';
                else $types .= 's';
            }
            mysqli_stmt_bind_param($stmt, $types, ...$params);
        }

        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        return new DatabaseResult($result, $this->conn, $stmt);
    }

    public function lastInsertId()
    {
        return mysqli_insert_id($this->conn);
    }
}

class DatabaseResult
{
    private $result;
    private $conn;
    private $stmt;

    public function __construct($result, $conn, $stmt = null)
    {
        $this->result = $result;
        $this->conn = $conn;
        $this->stmt = $stmt;
    }

    public function fetch()
    {
        if (!$this->result) return null;
        return mysqli_fetch_assoc($this->result);
    }

    public function fetchAll()
    {
        if (!$this->result) return [];
        $rows = [];
        while ($row = mysqli_fetch_assoc($this->result)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function rowCount()
    {
        if ($this->stmt) {
            return mysqli_stmt_affected_rows($this->stmt);
        }
        return mysqli_affected_rows($this->conn);
    }
}