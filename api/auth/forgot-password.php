<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'] ?? '';

    if (empty($email)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Email requis']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        // Vérifier si l'email existe
        $stmt = $db->prepare("SELECT utilisateur_id, prenom, nom FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            // En production, vous enverriez un email ici
            // Pour le moment, on simule l'envoi

            $resetToken = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Stocker le token en base (vous devrez créer une table reset_tokens)
            // $stmt = $db->prepare("INSERT INTO reset_tokens (user_id, token, expiry) VALUES (?, ?, ?)");
            // $stmt->execute([$user['utilisateur_id'], $resetToken, $expiry]);

            // Envoyer une notification à l'utilisateur
            $notificationStmt = $db->prepare(
                "INSERT INTO notifications (utilisateur_id, titre, message, type_notification) 
                 VALUES (?, 'Réinitialisation de mot de passe', ?, 'info')"
            );
            $notificationStmt->execute([
                $user['utilisateur_id'],
                "Une demande de réinitialisation de mot de passe a été effectuée. Si vous n'êtes pas à l'origine de cette demande, ignorez ce message."
            ]);

            // Logger l'action
            error_log("Demande de réinitialisation de mot de passe pour: " . $email);

            echo json_encode([
                'success' => true,
                'message' => 'Un lien de réinitialisation a été envoyé à votre adresse email.'
            ]);
        } else {
            // Pour des raisons de sécurité, on ne révèle pas si l'email existe
            echo json_encode([
                'success' => true,
                'message' => 'Si votre email existe dans notre système, vous recevrez un lien de réinitialisation.'
            ]);
        }
    } catch (PDOException $e) {
        error_log("Forgot password error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors du traitement de la demande']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
