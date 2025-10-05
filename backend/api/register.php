<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents("php://input"));

if (!empty($data->nom) && !empty($data->prenom) && !empty($data->email) && !empty($data->password)) {

    // Vérifier si l'email existe déjà
    $checkQuery = "SELECT utilisateur_id FROM utilisateurs WHERE email = :email";
    $checkStmt = $db->prepare($checkQuery);
    $checkStmt->bindParam(":email", $data->email);
    $checkStmt->execute();

    if ($checkStmt->rowCount() > 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà utilisé'
        ]);
        exit;
    }

    // Insérer le nouvel utilisateur
    $query = "INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) 
              VALUES (:nom, :prenom, :email, :password, 'client')";

    $stmt = $db->prepare($query);
    $stmt->bindParam(":nom", $data->nom);
    $stmt->bindParam(":prenom", $data->prenom);
    $stmt->bindParam(":email", $data->email);
    $stmt->bindParam(":password", $data->password);

    if ($stmt->execute()) {
        // Récupérer l'utilisateur créé
        $getUserQuery = "SELECT utilisateur_id, nom, prenom, email, role FROM utilisateurs WHERE email = :email";
        $getUserStmt = $db->prepare($getUserQuery);
        $getUserStmt->bindParam(":email", $data->email);
        $getUserStmt->execute();
        $user = $getUserStmt->fetch(PDO::FETCH_ASSOC);

        session_start();
        $_SESSION['user_id'] = $user['utilisateur_id'];
        $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_email'] = $user['email'];

        echo json_encode([
            'success' => true,
            'message' => 'Compte créé avec succès',
            'user' => $user
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Erreur lors de la création du compte'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Tous les champs sont obligatoires'
    ]);
}
