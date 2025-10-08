<?php
class Database
{
    private $host = "localhost";
    private $port = "5432";
    private $db_name = "yabiso";
    private $username = "Lulu";
    private $password = "23525689";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $exception) {
            echo "Erreur de connexion à PostgreSQL : " . $exception->getMessage();
        }

        return $this->conn;
    }
}
