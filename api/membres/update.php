<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    $input = json_decode(file_get_contents('php://input'), true);
    $user_id = $input['user_id'] ?? null;

    if (!$user_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID utilisateur manquant']);
        exit;
    }

    // Champs autorisés à la modification
    $allowedFields = ['nom', 'prenom', 'telephone', 'date_naissance', 'adresse', 'statut'];
    $updateFields = [];
    $updateValues = [];

    foreach ($allowedFields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $updateValues[] = $input[$field];
        }
    }

    if (empty($updateFields)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Aucun champ à modifier']);
        exit;
    }

    $updateValues[] = $user_id;

    try {
        $db = Database::getInstance()->getConnection();

        $sql = "UPDATE utilisateurs SET " . implode(', ', $updateFields) . " WHERE utilisateur_id = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute($updateValues);

        if ($stmt->rowCount() > 0) {
            $auth->logActivity($_SESSION['user_id'], 'Modification membre', "ID: $user_id");
            echo json_encode(['success' => true, 'message' => 'Membre mis à jour avec succès']);
        } else {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Membre non trouvé']);
        }
    } catch (PDOException $e) {
        error_log("Update member error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de la mise à jour']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
