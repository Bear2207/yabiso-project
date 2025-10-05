<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email) && !empty($data->password)) {

    $query = "SELECT utilisateur_id, nom, prenom, email, role FROM utilisateurs 
              WHERE email = :email AND mot_de_passe = :password";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $data->password);

    if ($stmt->execute()) {
        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            session_start();
            $_SESSION['user_id'] = $row['utilisateur_id'];
            $_SESSION['user_name'] = $row['prenom'] . ' ' . $row['nom'];
            $_SESSION['user_role'] = $row['role'];
            $_SESSION['user_email'] = $row['email'];

            echo json_encode([
                'success' => true,
                'message' => 'Connexion réussie',
                'user' => $row
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Email ou mot de passe incorrect'
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la connexion'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Données manquantes'
    ]);
}
