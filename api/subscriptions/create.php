<?php
require_once '../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $auth = new Auth();
    $auth->requireRole(['admin', 'coach']);

    $input = json_decode(file_get_contents('php://input'), true);

    $user_id = $input['user_id'] ?? null;
    $abonnement_id = $input['abonnement_id'] ?? null;
    $date_debut = $input['date_debut'] ?? date('Y-m-d');

    if (!$user_id || !$abonnement_id) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID utilisateur et abonnement requis']);
        exit;
    }

    try {
        $db = Database::getInstance()->getConnection();

        // Récupérer les infos de l'abonnement
        $abonnementStmt = $db->prepare(
            "SELECT prix, duree FROM abonnements WHERE abonnement_id = ? AND statut = 'actif'"
        );
        $abonnementStmt->execute([$abonnement_id]);
        $abonnement = $abonnementStmt->fetch();

        if (!$abonnement) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Abonnement non trouvé ou inactif']);
            exit;
        }

        // Calculer la date de fin
        $date_fin = date('Y-m-d', strtotime($date_debut . " + {$abonnement['duree']} days"));

        // Désactiver les anciens abonnements actifs
        $disableStmt = $db->prepare(
            "UPDATE abonnements_utilisateurs 
             SET statut = 'expire' 
             WHERE utilisateur_id = ? AND statut = 'actif'"
        );
        $disableStmt->execute([$user_id]);

        // Créer le nouvel abonnement
        $stmt = $db->prepare(
            "INSERT INTO abonnements_utilisateurs (utilisateur_id, abonnement_id, date_debut, date_fin, statut) 
             VALUES (?, ?, ?, ?, 'actif')"
        );

        $stmt->execute([$user_id, $abonnement_id, $date_debut, $date_fin]);
        $abonnement_utilisateur_id = $db->lastInsertId();

        // Logger l'action
        $actorId = $_SESSION['user_id'] ?? null;
        $logMessage = "User: $user_id, Abonnement: $abonnement_id";
        if (is_callable([$auth, 'logActivity'])) {
            call_user_func([$auth, 'logActivity'], $actorId, 'Attribution abonnement', $logMessage);
        } else {
            error_log("Attribution abonnement - Actor: " . ($actorId ?? 'unknown') . ", $logMessage");
        }

        // Créer une notification pour l'utilisateur
        $userStmt = $db->prepare("SELECT prenom, nom FROM utilisateurs WHERE utilisateur_id = ?");
        $userStmt->execute([$user_id]);
        $user = $userStmt->fetch();

        $notificationStmt = $db->prepare(
            "INSERT INTO notifications (utilisateur_id, titre, message, type_notification) 
             VALUES (?, 'Nouvel abonnement', ?, 'success')"
        );
        $notificationStmt->execute([
            $user_id,
            "Votre abonnement a été activé. Date d'expiration: $date_fin"
        ]);

        echo json_encode([
            'success' => true,
            'message' => 'Abonnement attribué avec succès',
            'data' => [
                'abonnement_utilisateur_id' => $abonnement_utilisateur_id,
                'date_fin' => $date_fin,
                'prix' => $abonnement['prix']
            ]
        ]);
    } catch (PDOException $e) {
        error_log("Create subscription error: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'attribution de l\'abonnement']);
    }
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
}
