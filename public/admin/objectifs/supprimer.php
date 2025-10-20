<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');

if (!isset($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$abonne_id = $_GET['id'];

try {
    // Réinitialiser les objectifs de l'abonné
    $stmt = $pdo->prepare("UPDATE abonnes SET poids_cible = 0, objectif = NULL, objectif_duree = 4, frequence = 3, tour_de_bras = 0, tour_de_hanches = 0, tour_de_fessier = 0 WHERE id = ?");
    $stmt->execute([$abonne_id]);

    // Envoyer une notification
    sendNotification($pdo, $abonne_id, 'Information', "Votre objectif a été réinitialisé. Contactez un coach pour définir un nouvel objectif.");

    redirectWithMessage('liste.php', 'success', '✅ Objectif supprimé avec succès !');
} catch (Exception $e) {
    redirectWithMessage('liste.php', 'error', '❌ Erreur lors de la suppression : ' . $e->getMessage());
}
