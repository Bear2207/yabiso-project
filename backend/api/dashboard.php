<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    $dashboardData = [];

    // 1. Statistiques de base
    $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role IN ('client', 'coach')";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['total_membres'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT COUNT(*) as total FROM utilisateurs WHERE role = 'coach'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['total_coachs'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $query = "SELECT COALESCE(SUM(montant), 0) as total FROM paiements WHERE statut = 'payé'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['revenus_totaux'] = floatval($stmt->fetch(PDO::FETCH_ASSOC)['total']);

    $query = "SELECT COUNT(*) as total FROM seances WHERE date_seance >= NOW() AND statut = 'réservée'";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['seances_a_venir'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Données pour le graphique circulaire (abonnements)
    $query = "
        SELECT a.nom_abonnement, COUNT(p.paiement_id) as total
        FROM abonnements a
        LEFT JOIN paiements p ON a.abonnement_id = p.abonnement_id AND p.statut = 'payé'
        GROUP BY a.abonnement_id, a.nom_abonnement
        ORDER BY total DESC
        LIMIT 5
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['abonnements_data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Revenus mensuels (6 derniers mois)
    $query = "
        SELECT 
            TO_CHAR(date_paiement, 'YYYY-MM') as mois,
            COALESCE(SUM(montant), 0) as revenus
        FROM paiements 
        WHERE statut = 'payé' 
            AND date_paiement >= CURRENT_DATE - INTERVAL '6 months'
        GROUP BY TO_CHAR(date_paiement, 'YYYY-MM')
        ORDER BY mois DESC
        LIMIT 6
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $revenusMensuels = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Compléter les mois manquants
    $dashboardData['revenus_mensuels'] = completeMissingMonths($revenusMensuels);

    // 4. Derniers membres inscrits
    $query = "
        SELECT utilisateur_id, nom, prenom, email, role, date_creation
        FROM utilisateurs 
        WHERE role IN ('client', 'coach')
        ORDER BY date_creation DESC 
        LIMIT 5
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['derniers_membres'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 5. Prochaines séances
    $query = "
        SELECT 
            s.seance_id,
            s.date_seance,
            s.type_seance,
            uc.prenom as client_prenom,
            uc.nom as client_nom,
            uco.prenom as coach_prenom,
            uco.nom as coach_nom
        FROM seances s
        JOIN utilisateurs uc ON s.utilisateur_id = uc.utilisateur_id
        JOIN utilisateurs uco ON s.coach_id = uco.utilisateur_id
        WHERE s.date_seance >= NOW() 
            AND s.statut = 'réservée'
        ORDER BY s.date_seance ASC
        LIMIT 5
    ";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $dashboardData['prochaines_seances'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $dashboardData
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}

// Fonction pour compléter les mois manquants dans les revenus
function completeMissingMonths($revenusData)
{
    $completedData = [];
    $currentDate = new DateTime();

    for ($i = 5; $i >= 0; $i--) {
        $monthDate = clone $currentDate;
        $monthDate->modify("-$i months");
        $monthKey = $monthDate->format('Y-m');

        $found = false;
        foreach ($revenusData as $revenu) {
            if ($revenu['mois'] === $monthKey) {
                $completedData[] = $revenu;
                $found = true;
                break;
            }
        }

        if (!$found) {
            $completedData[] = [
                'mois' => $monthKey,
                'revenus' => 0
            ];
        }
    }

    return $completedData;
}
