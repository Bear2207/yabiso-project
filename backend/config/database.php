<?php
class Database
{
    private $host = "localhost";
    private $port = "5432";
    private $db_name = "yabiso";
    private $username = "bearing";
    private $password = "23525689";
    private $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $dsn = "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name};";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Erreur de connexion à la base : " . $exception->getMessage();
        }
        return $this->conn;
    }
}
