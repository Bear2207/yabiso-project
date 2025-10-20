<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');

$stmt = $pdo->prepare("UPDATE notifications SET lu = TRUE WHERE lu = FALSE");
$stmt->execute();

redirectWithMessage('liste.php', 'success', '✅ Toutes les notifications ont été marquées comme lues !');
