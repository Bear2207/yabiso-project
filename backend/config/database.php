<?php
class Database
{
    private $host = "localhost";
    private $port = "5432";
    private $db_name = "yabiso";
    private $username = "lulu";
    private $password = "23525689";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "pgsql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password,
                array(PDO::ATTR_PERSISTENT => true)
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->exec("SET client_encoding TO 'UTF8'");
        } catch (PDOException $exception) {
            error_log("Erreur PostgreSQL: " . $exception->getMessage());
            echo "Erreur de connexion à la base de données.";
        }

        return $this->conn;
    }
}
