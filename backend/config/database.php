<?php
class Database
{
    private $host = 'postgres';
    private $db_name = 'yabiso';
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
            $this->conn->exec("set names utf8");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Erreur de connexion: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
