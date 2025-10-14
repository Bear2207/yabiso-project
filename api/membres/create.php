<?php
require_once '../includes/auth.php';

if (!class_exists('Database')) {
    // Minimal Database singleton to provide getConnection(); adjust DSN/credentials as needed
    class Database
    {
        private static $instance = null;
        private $conn;

        private function __construct()
        {
            $host = '127.0.0.1';
            $db   = 'yabiso_db';    // change to your database name
            $user = 'root';      // change to your DB user
            $pass = '';          // change to your DB password
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];

            $this->conn = new PDO($dsn, $user, $pass, $options);
        }

        public static function getInstance()
        {
            if (self::$instance === null) {
                self::$instance = new self();
            }
            return self::$instance;
        }

        public function getConnection()
        {
            return $this->conn;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    $input = json_decode(file_get_contents('php://input'), true);

    $nom = trim($input['nom'] ?? '');
    $prenom = trim($input['prenom'] ?? '');
    $email = trim($input['email'] ?? '');
    $telephone = trim($input['telephone'] ?? '');
    $date_naissance = $input['date_naissance'] ?? '';
    $adresse = trim($input['adresse'] ?? '');

    // Validation
    if (empty($nom) || empty($prenom) || empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Nom, prénom et email sont obligatoires']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        // Vérifier si l'email existe déjà
        $stmt = $db->prepare("SELECT utilisateur_id FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            http_response_code(409);
            echo json_encode(['success' => false, 'message' => 'Cet email est déjà utilisé']);
            exit;
        }

        // Créer le membre (client)
        $password = bin2hex(random_bytes(8)); // Mot de passe temporaire
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $db->prepare(
            "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone, date_naissance, adresse) 
             VALUES (?, ?, ?, ?, 'client', ?, ?, ?)"
        );

        $stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone, $date_naissance, $adresse]);
        $user_id = $db->lastInsertId();

        // Logger l'action
        if (is_object($auth) && method_exists($auth, 'logActivity')) {
            // Safely call logActivity only when available
            call_user_func([$auth, 'logActivity'], $_SESSION['user_id'] ?? 'unknown', 'Création membre', "Membre: $prenom $nom");
        } else {
            // Fallback logging if Auth->logActivity is not available
            $actor = $_SESSION['user_id'] ?? 'unknown';
            $message = sprintf("Création membre by %s: Membre: %s %s", $actor, $prenom, $nom);
            error_log($message);
        }

        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Membre créé avec succès',
            'data' => [
                'user_id' => $user_id,
                'temporary_password' => $password // À envoyer par email en prod
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Create member error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la création du membre']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
