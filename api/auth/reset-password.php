<?php
require_once '../includes/config.php';
require_once '../includes/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $token = $input['token'] ?? '';
    $newPassword = $input['password'] ?? '';

    if (empty($token) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Token et nouveau mot de passe requis']);
        exit;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(['success' => false, 'message' => 'Le mot de passe doit contenir au moins 6 caractères']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        // Vérifier le token
        $stmt = $db->prepare("SELECT email FROM password_resets WHERE token = ? AND expiry > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();

        if (!$resetRequest) {
            echo json_encode(['success' => false, 'message' => 'Token invalide ou expiré']);
            exit;
        }

        $email = $resetRequest['email'];

        // Mettre à jour le mot de passe
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $db->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE email = ?");
        $stmt->execute([$hashedPassword, $email]);

        // Supprimer le token utilisé
        $stmt = $db->prepare("DELETE FROM password_resets WHERE token = ?");
        $stmt->execute([$token]);

        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe réinitialisé avec succès'
        ]);
    } catch (Exception $e) {
        error_log("Reset password error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la réinitialisation']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
