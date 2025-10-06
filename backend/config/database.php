<?php
class Database
{
    private $host = 'localhost';
    private $db_name = 'yabiso_db';
    private $username = 'admin';
    private $password = '23525689';
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "pgsql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ATTR_ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            error_log("Erreur de connexion: " . $exception->getMessage());
        }
        return $this->conn;
    }
}
