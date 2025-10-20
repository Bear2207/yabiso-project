<?php

/**
 * Script manuel pour tester les notifications d'expiration
 * À utiliser pour les tests seulement
 */

require_once '../../config/database.php';

echo "<pre>";
echo "=== TEST MANUEL DES NOTIFICATIONS ===\n";

try {
    // Test des abonnements expirant dans 3 jours
    $sql = "
    SELECT 
        ab.id AS abonnement_id,
        a.id AS abonne_id,
        u.nom AS abonne_nom,
        ab.date_fin,
        ab.type
    FROM abonnements ab
    JOIN abonnes a ON ab.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE ab.statut = 'Actif' 
    AND DATEDIFF(ab.date_fin, CURDATE()) <= 3
    LIMIT 5
    ";

    $abonnements = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

    if (count($abonnements) > 0) {
        echo "Abonnements trouvés : " . count($abonnements) . "\n\n";

        foreach ($abonnements as $abonnement) {
            $jours_restants = date_diff(
                new DateTime(),
                new DateTime($abonnement['date_fin'])
            )->days;

            $message = "🔔 TEST : Votre abonnement " . $abonnement['type'] . " expire dans " . $jours_restants . " jour(s) (le " .
                date('d/m/Y', strtotime($abonnement['date_fin'])) . ")";

            $stmt = $pdo->prepare("INSERT INTO notifications (abonne_id, type, message) VALUES (?, 'Rappel', ?)");
            $stmt->execute([$abonnement['abonne_id'], $message]);

            echo "✅ Notification envoyée à " . $abonnement['abonne_nom'] . " (Expire dans " . $jours_restants . " jours)\n";
        }
    } else {
        echo "Aucun abonnement à notifier pour le moment.\n";
    }

    echo "\n=== TEST TERMINÉ ===\n";
} catch (Exception $e) {
    echo "❌ ERREUR : " . $e->getMessage() . "\n";
}

echo "</pre>";
