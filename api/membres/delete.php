<?php
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $auth = new Auth();

        // Call the preferred method if it exists, otherwise try common alternatives,
        // and finally fall back to a session-based admin role check.
        if (method_exists($auth, 'requireRole')) {
            $m = 'requireRole';
            $auth->{$m}(['admin']);
        } elseif (method_exists($auth, 'require_role')) {
            $m = 'require_role';
            $auth->{$m}(['admin']);
        } elseif (method_exists($auth, 'requireAdmin')) {
            $m = 'requireAdmin';
            $auth->{$m}();
        } else {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $userRole = $_SESSION['role'] ?? $_SESSION['user']['role'] ?? null;
            if ($userRole !== 'admin') {
                throw new Exception('Accès refusé: rôle administrateur requis');
            }
        }

        $input = json_decode(file_get_contents('php://input'), true);
        $user_id = $input['user_id'] ?? null;

        if (!$user_id) {
            throw new Exception('ID utilisateur manquant');
        }

        $db = Database::getInstance()->getConnection();

        // Vérifier si l'utilisateur existe
        $stmt = $db->prepare("SELECT utilisateur_id FROM utilisateurs WHERE utilisateur_id = ?");
        $stmt->execute([$user_id]);

        if (!$stmt->fetch()) {
            throw new Exception('Utilisateur non trouvé');
        }

        // Supprimer l'utilisateur (les contraintes CASCADE s'occuperont du reste)
        $stmt = $db->prepare("DELETE FROM utilisateurs WHERE utilisateur_id = ?");
        $stmt->execute([$user_id]);

        echo json_encode([
            'success' => true,
            'message' => 'Membre supprimé avec succès'
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
