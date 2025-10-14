<?php
// Inclure les dépendances une seule fois
require_once 'database.php';

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($nom, $prenom, $email, $password, $role = 'client')
    {
        // Validation des données
        if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Tous les champs sont requis'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email invalide'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères'];
        }

        // Vérifier si l'email existe déjà
        try {
            $stmt = $this->db->prepare("SELECT utilisateur_id FROM utilisateurs WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Cet email est déjà utilisé'];
            }

            // Hacher le mot de passe
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Insérer l'utilisateur
            $stmt = $this->db->prepare(
                "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
                 VALUES (?, ?, ?, ?, ?)"
            );

            $stmt->execute([$nom, $prenom, $email, $hashedPassword, $role]);

            return [
                'success' => true,
                'message' => 'Compte créé avec succès'
            ];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur lors de la création du compte'];
        }
    }

    public function login($email, $password)
    {
        try {
            $stmt = $this->db->prepare(
                "SELECT utilisateur_id, nom, prenom, email, mot_de_passe, role 
                 FROM utilisateurs 
                 WHERE email = ? AND statut = 'actif'"
            );

            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['mot_de_passe'])) {
                // Créer la session
                $_SESSION['user_id'] = $user['utilisateur_id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];

                return [
                    'success' => true,
                    'message' => 'Connexion réussie',
                    'user' => [
                        'id' => $user['utilisateur_id'],
                        'name' => $user['prenom'] . ' ' . $user['nom'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ];
            }

            return ['success' => false, 'message' => 'Email ou mot de passe incorrect'];
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erreur de connexion'];
        }
    }

    public function logout()
    {
        session_unset();
        session_destroy();
        return ['success' => true, 'message' => 'Déconnexion réussie'];
    }

    public function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    public function getUserRole()
    {
        return $_SESSION['user_role'] ?? null;
    }

    public function requireAuth()
    {
        if (!$this->isLoggedIn()) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Non authentifié']);
            exit;
        }
    }
}
