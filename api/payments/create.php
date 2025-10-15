<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    $input = json_decode(file_get_contents('php://input'), true);

    $user_id = $input['user_id'] ?? null;
    $abonnement_id = $input['abonnement_id'] ?? null;
    $montant = $input['montant'] ?? null;
    $mode_paiement = $input['mode_paiement'] ?? 'espece';

    if (!$user_id || !$abonnement_id || !$montant) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Données manquantes']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        // Vérifier l'abonnement
        $abonnementStmt = $db->prepare("SELECT prix FROM abonnements WHERE abonnement_id = ?");
        $abonnementStmt->execute([$abonnement_id]);
        $abonnement = $abonnementStmt->fetch();

        if (!$abonnement) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Abonnement non trouvé']);
            exit;
        }

        // Générer une référence unique
        $reference = 'PAY-' . date('Ymd-His') . '-' . rand(1000, 9999);

        // Créer le paiement
        $stmt = $db->prepare(
            "INSERT INTO paiements (utilisateur_id, abonnement_id, montant, mode_paiement, reference_paiement, statut) 
             VALUES (?, ?, ?, ?, ?, 'paye')"
        );

        $stmt->execute([$user_id, $abonnement_id, $montant, $mode_paiement, $reference]);
        $paiement_id = $db->lastInsertId();

        // Logger l'action
        $auth->logActivity($_SESSION['user_id'], 'Enregistrement paiement', "Montant: $montant, User: $user_id");

        echo json_encode([
            'success' => true,
            'message' => 'Paiement enregistré avec succès',
            'data' => [
                'paiement_id' => $paiement_id,
                'reference' => $reference,
                'date_paiement' => date('Y-m-d H:i:s')
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Create payment error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'enregistrement du paiement']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
