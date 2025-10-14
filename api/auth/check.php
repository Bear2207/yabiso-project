<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $auth = new Auth();

    if ($auth->isLoggedIn()) {
        // Récupérer les informations de l'utilisateur
        $user_id = $_SESSION['user_id'];

        try {
            $db = Database::getInstance()->getConnection();

            $stmt = $db->prepare(
                "SELECT utilisateur_id, nom, prenom, email, role 
                 FROM utilisateurs 
                 WHERE utilisateur_id = ?"
            );

            $stmt->execute([$user_id]);
            $user = $stmt->fetch();

            if ($user) {
                echo json_encode([
                    'success' => true,
                    'user' => [
                        'id' => $user['utilisateur_id'],
                        'name' => $user['prenom'] . ' ' . $user['nom'],
                        'email' => $user['email'],
                        'role' => $user['role']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Utilisateur non trouvé']);
            }
        } catch (PDOException $e) {
            error_log("Check auth error: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur de vérification']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Non authentifié']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
