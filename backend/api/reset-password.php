<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->newPassword)) {

    $query = "UPDATE utilisateurs SET mot_de_passe = :password WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":password", $data->newPassword);
    $stmt->bindParam(":email", $data->email);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Mot de passe mis à jour avec succès'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la mise à jour du mot de passe'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Données manquantes'
    ]);
}
