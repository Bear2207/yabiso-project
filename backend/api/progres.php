<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Récupérer les progrès avec les informations utilisateur
    $query = "
        SELECT 
            p.progres_id,
            u.utilisateur_id,
            u.nom,
            u.prenom,
            p.date_mesure,
            p.poids,
            p.taille,
            p.masse_musculaire,
            p.masse_graisseuse,
            ROUND(p.poids / (p.taille * p.taille), 2) as imc
        FROM progres p
        INNER JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id
        ORDER BY p.date_mesure DESC, u.nom, u.prenom
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();

    $progres = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $progres[] = $row;
    }

    echo json_encode([
        'success' => true,
        'data' => $progres,
        'total' => count($progres)
    ]);
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
