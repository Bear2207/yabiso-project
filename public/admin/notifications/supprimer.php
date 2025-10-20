<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');

if (!isset($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$notification_id = $_GET['id'];

try {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    $stmt->execute([$notification_id]);

    redirectWithMessage('liste.php', 'success', '✅ Notification supprimée avec succès !');
} catch (Exception $e) {
    redirectWithMessage('liste.php', 'error', '❌ Erreur lors de la suppression : ' . $e->getMessage());
}
