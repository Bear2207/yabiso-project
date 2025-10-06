<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Récupérer tous les utilisateurs avec leurs abonnements
    $query = "
        SELECT 
            u.utilisateur_id,
            u.nom,
            u.prenom,
            u.email,
            u.role,
            u.date_creation,
            a.nom_abonnement,
            p.statut as statut_paiement,
            p.date_paiement
        FROM utilisateurs u
        LEFT JOIN paiements p ON u.utilisateur_id = p.utilisateur_id
        LEFT JOIN abonnements a ON p.abonnement_id = a.abonnement_id
        WHERE u.role IN ('client', 'coach')
        ORDER BY u.date_creation DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $membres = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $membres[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $membres,
        'total' => count($membres)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
