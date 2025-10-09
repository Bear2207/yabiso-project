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
            $query = "
                SELECT 
                    s.*,
                    uc.prenom as client_prenom, uc.nom as client_nom,
                    uco.prenom as coach_prenom, uco.nom as coach_nom
                FROM seances s
                JOIN utilisateurs uc ON s.utilisateur_id = uc.utilisateur_id
                JOIN utilisateurs uco ON s.coach_id = uco.utilisateur_id
                ORDER BY s.date_seance DESC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute();

            $seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'data' => $seances
            ]);
            break;

        case 'POST':
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->coach_id) && !empty($data->utilisateur_id) && !empty($data->date_seance)) {
                $query = "INSERT INTO seances (coach_id, utilisateur_id, date_seance, type_seance) 
                         VALUES (:coach_id, :utilisateur_id, :date_seance, :type_seance)";

                $stmt = $db->prepare($query);
                $stmt->bindParam(':coach_id', $data->coach_id);
                $stmt->bindParam(':utilisateur_id', $data->utilisateur_id);
                $stmt->bindParam(':date_seance', $data->date_seance);
                $stmt->bindParam(':type_seance', $data->type_seance);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Séance créée avec succès'
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
