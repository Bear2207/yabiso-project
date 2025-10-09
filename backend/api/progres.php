<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            $utilisateur_id = $_GET['utilisateur_id'] ?? null;

            if ($utilisateur_id) {
                $query = "SELECT * FROM progres WHERE utilisateur_id = :id ORDER BY date_mesure DESC";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $utilisateur_id);
            } else {
                $query = "
                    SELECT p.*, u.prenom, u.nom 
                    FROM progres p 
                    JOIN utilisateurs u ON p.utilisateur_id = u.utilisateur_id 
                    ORDER BY p.date_mesure DESC
                ";
                $stmt = $db->prepare($query);
            }

            $stmt->execute();
            $progres = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $progres
            ]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->utilisateur_id) && !empty($data->date_mesure)) {
                $query = "INSERT INTO progres (utilisateur_id, date_mesure, poids, taille, masse_musculaire, masse_graisseuse) 
                         VALUES (:utilisateur_id, :date_mesure, :poids, :taille, :masse_musculaire, :masse_graisseuse)";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':utilisateur_id', $data->utilisateur_id);
                $stmt->bindParam(':date_mesure', $data->date_mesure);
                $stmt->bindParam(':poids', $data->poids);
                $stmt->bindParam(':taille', $data->taille);
                $stmt->bindParam(':masse_musculaire', $data->masse_musculaire);
                $stmt->bindParam(':masse_graisseuse', $data->masse_graisseuse);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Progrès enregistré avec succès'
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
