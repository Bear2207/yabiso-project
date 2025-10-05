<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->email)) {

    // Vérifier si l'email existe
    $query = "SELECT utilisateur_id, nom, prenom, email FROM utilisateurs WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(":email", $data->email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // En production, vous enverriez un email ici
        // Pour l'instant, on simule l'envoi

        echo json_encode([
            'success' => true,
            'message' => 'Un email de réinitialisation a été envoyé à ' . $data->email
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Aucun compte trouvé avec cet email'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez entrer votre email'
    ]);
}
