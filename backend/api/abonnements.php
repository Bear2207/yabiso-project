<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $query = "SELECT * FROM abonnements ORDER BY prix";
            $stmt = $db->prepare($query);
            $stmt->execute();

            $abonnements = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $abonnements
            ]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->nom_abonnement) && !empty($data->prix) && !empty($data->duree)) {
                $query = "INSERT INTO abonnements (nom_abonnement, prix, duree, description) 
                         VALUES (:nom, :prix, :duree, :description)";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':nom', $data->nom_abonnement);
                $stmt->bindParam(':prix', $data->prix);
                $stmt->bindParam(':duree', $data->duree);
                $stmt->bindParam(':description', $data->description);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Abonnement créé avec succès'
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
