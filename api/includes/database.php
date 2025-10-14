<?php
class Database
{
    private $connection;
    private static $instance = null;

    private function __construct()
    {
        try {
            // Essayer plusieurs configurations de connexion
            $dsn = "mysql:host=localhost;dbname=yabiso_db;charset=utf8";
            $username = "root";
            $password = "";

            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
        } catch (PDOException $e) {
            // Log l'erreur complète
            error_log("Database connection error: " . $e->getMessage());

            // Message plus détaillé pour le debug
            $errorInfo = [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'dsn' => $dsn ?? 'non défini'
            ];

            throw new Exception("Erreur de connexion à la base de données: " . json_encode($errorInfo));
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }

    // Méthode pour tester la connexion
    public static function testConnection()
    {
        try {
            $db = self::getInstance();
            $conn = $db->getConnection();
            $conn->query("SELECT 1");
            return ['success' => true, 'message' => 'Connexion OK'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
