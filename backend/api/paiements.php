<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $query = "
                SELECT 
                    p.paiement_id,
                    p.montant,
                    p.date_paiement,
                    p.mode_paiement,
                    p.statut,
                    u.nom,
                    u.prenom,
                    u.email,
                    a.nom_abonnement,
                    a.prix
                FROM paiements p
                JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id
                JOIN abonnements a ON p.abonnement_id = a.abonnement_id
                ORDER BY p.date_paiement DESC
            ";

            $stmt = $db->prepare($query);
            $stmt->execute();

            $paiements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $paiements
            ]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->utilisateur_id) && !empty($data->abonnement_id) && !empty($data->montant)) {
                $query = "INSERT INTO paiements (utilisateur_id, abonnement_id, montant, mode_paiement, statut) 
                         VALUES (:utilisateur_id, :abonnement_id, :montant, :mode_paiement, :statut)";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':utilisateur_id', $data->utilisateur_id);
                $stmt->bindParam(':abonnement_id', $data->abonnement_id);
                $stmt->bindParam(':montant', $data->montant);
                $stmt->bindParam(':mode_paiement', $data->mode_paiement);
                $statut = $data->statut ?? 'payé';
                $stmt->bindParam(':statut', $statut);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Paiement enregistré avec succès'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de l\'enregistrement'
                    ]);
                }
            }
            break;
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
