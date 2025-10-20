<?php

/**
 * Script CRON pour vérifier les expirations d'abonnements
 * À exécuter quotidiennement - Remplace notify_expired.php
 */

require_once '../config/database.php';

// Créer le dossier cron s'il n'existe pas
$cron_dir = __DIR__;
if (!is_dir($cron_dir)) {
    mkdir($cron_dir, 0755, true);
}

// Chemin du fichier de log
$log_file = $cron_dir . '/cron_log.txt';

// Journalisation
$timestamp = date('Y-m-d H:i:s');
$log_entry = "=== Exécution CRON du $timestamp ===\n";

try {
    // 1. Rappels 7 jours avant expiration
    $sql_rappel_7j = "
    SELECT 
        ab.id AS abonnement_id,
        a.id AS abonne_id,
        u.nom AS abonne_nom,
        u.email,
        ab.date_fin,
        ab.type
    FROM abonnements ab
    JOIN abonnes a ON ab.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE ab.statut = 'Actif' 
    AND DATEDIFF(ab.date_fin, CURDATE()) = 7
    AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.abonne_id = a.id 
        AND n.message LIKE CONCAT('%expire le ', DATE_FORMAT(ab.date_fin, '%d/%m/%Y'), '%')
        AND n.date_envoi > DATE_SUB(NOW(), INTERVAL 2 DAY)
    )
    ";

    $abonnements_rappel_7j = $pdo->query($sql_rappel_7j)->fetchAll(PDO::FETCH_ASSOC);
    $count_rappel_7j = 0;

    foreach ($abonnements_rappel_7j as $abonnement) {
        $message = "🔔 Rappel : Votre abonnement " . $abonnement['type'] . " expire dans 7 jours (le " .
            date('d/m/Y', strtotime($abonnement['date_fin'])) . "). Pensez à le renouveler !";

        $stmt = $pdo->prepare("INSERT INTO notifications (abonne_id, type, message) VALUES (?, 'Rappel', ?)");
        $stmt->execute([$abonnement['abonne_id'], $message]);

        $count_rappel_7j++;
        $log_entry .= "✅ Rappel 7j envoyé à " . $abonnement['abonne_nom'] . " (" . $abonnement['email'] . ")\n";
    }

    // 2. Rappels 3 jours avant expiration
    $sql_rappel_3j = "
    SELECT 
        ab.id AS abonnement_id,
        a.id AS abonne_id,
        u.nom AS abonne_nom,
        u.email,
        ab.date_fin,
        ab.type
    FROM abonnements ab
    JOIN abonnes a ON ab.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE ab.statut = 'Actif' 
    AND DATEDIFF(ab.date_fin, CURDATE()) = 3
    AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.abonne_id = a.id 
        AND n.message LIKE CONCAT('%expire le ', DATE_FORMAT(ab.date_fin, '%d/%m/%Y'), '%')
        AND n.date_envoi > DATE_SUB(NOW(), INTERVAL 2 DAY)
    )
    ";

    $abonnements_rappel_3j = $pdo->query($sql_rappel_3j)->fetchAll(PDO::FETCH_ASSOC);
    $count_rappel_3j = 0;

    foreach ($abonnements_rappel_3j as $abonnement) {
        $message = "⚠️ Important : Votre abonnement " . $abonnement['type'] . " expire dans 3 jours (le " .
            date('d/m/Y', strtotime($abonnement['date_fin'])) . "). Renouvelez dès maintenant !";

        $stmt = $pdo->prepare("INSERT INTO notifications (abonne_id, type, message) VALUES (?, 'Alerte', ?)");
        $stmt->execute([$abonnement['abonne_id'], $message]);

        $count_rappel_3j++;
        $log_entry .= "✅ Rappel 3j envoyé à " . $abonnement['abonne_nom'] . " (" . $abonnement['email'] . ")\n";
    }

    // 3. Alertes pour abonnements expirés aujourd'hui
    $sql_expire = "
    SELECT 
        ab.id AS abonnement_id,
        a.id AS abonne_id,
        u.nom AS abonne_nom,
        u.email,
        ab.date_fin,
        ab.type
    FROM abonnements ab
    JOIN abonnes a ON ab.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE ab.statut = 'Actif' 
    AND ab.date_fin = CURDATE()
    ";

    $abonnements_expires = $pdo->query($sql_expire)->fetchAll(PDO::FETCH_ASSOC);
    $count_expire = 0;

    foreach ($abonnements_expires as $abonnement) {
        $message = "❌ URGENT : Votre abonnement " . $abonnement['type'] . " a expiré aujourd'hui. Accès suspendu jusqu'au renouvellement.";

        $stmt = $pdo->prepare("INSERT INTO notifications (abonne_id, type, message) VALUES (?, 'Alerte', ?)");
        $stmt->execute([$abonnement['abonne_id'], $message]);

        // Marquer l'abonnement comme expiré
        $stmt = $pdo->prepare("UPDATE abonnements SET statut = 'Expiré' WHERE id = ?");
        $stmt->execute([$abonnement['abonnement_id']]);

        $count_expire++;
        $log_entry .= "⚠️ Alerte expiration envoyée à " . $abonnement['abonne_nom'] . " - Abonnement expiré\n";
    }

    // 4. Nettoyage des anciennes notifications (plus de 30 jours)
    $sql_cleanup = "DELETE FROM notifications WHERE date_envoi < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $deleted_count = $pdo->exec($sql_cleanup);

    // 5. Notification de bienvenue pour nouveaux abonnés (inscrits aujourd'hui)
    $sql_bienvenue = "
    SELECT 
        a.id AS abonne_id,
        u.nom AS abonne_nom,
        u.email
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE DATE(a.date_inscription) = CURDATE()
    AND NOT EXISTS (
        SELECT 1 FROM notifications n 
        WHERE n.abonne_id = a.id 
        AND n.message LIKE '%Bienvenue au Reforme Center%'
    )
    ";

    $nouveaux_abonnes = $pdo->query($sql_bienvenue)->fetchAll(PDO::FETCH_ASSOC);
    $count_bienvenue = 0;

    foreach ($nouveaux_abonnes as $abonne) {
        $message = "🎉 Bienvenue au Reforme Center " . $abonne['abonne_nom'] . " ! Nous sommes ravis de vous compter parmi nous. 💪";

        $stmt = $pdo->prepare("INSERT INTO notifications (abonne_id, type, message) VALUES (?, 'Information', ?)");
        $stmt->execute([$abonne['abonne_id'], $message]);

        $count_bienvenue++;
        $log_entry .= "👋 Notification bienvenue envoyée à " . $abonne['abonne_nom'] . "\n";
    }

    // Résumé
    $summary = "📊 RÉSUMÉ CRON :\n";
    $summary .= "   • $count_bienvenue notification(s) de bienvenue\n";
    $summary .= "   • $count_rappel_7j rappel(s) 7 jours avant\n";
    $summary .= "   • $count_rappel_3j rappel(s) 3 jours avant\n";
    $summary .= "   • $count_expire alerte(s) d'expiration\n";
    $summary .= "   • $deleted_count notification(s) nettoyée(s)\n";

    $log_entry .= $summary;
    $log_entry .= "==============================\n\n";

    // Écrire dans le fichier log
    file_put_contents($log_file, $log_entry, FILE_APPEND | LOCK_EX);

    echo $summary;
} catch (Exception $e) {
    $error_msg = "❌ ERREUR CRON : " . $e->getMessage() . "\n";
    file_put_contents($log_file, $error_msg, FILE_APPEND | LOCK_EX);
    echo $error_msg;
}
