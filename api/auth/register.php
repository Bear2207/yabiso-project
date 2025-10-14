<?php
// Inclure les fichiers nécessaires
require_once '../includes/config.php';
require_once '../includes/database.php';
require_once '../includes/auth.php';

// Vérifier la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée. Utilisez POST.']);
    exit;
}

// Récupérer les données
$input = json_decode(file_get_contents('php://input'), true);

// Si pas de données JSON, essayer avec POST
if (!$input && !empty($_POST)) {
    $input = $_POST;
}

if (!$input) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Données manquantes']);
    exit;
}

$nom = $input['nom'] ?? '';
$prenom = $input['prenom'] ?? '';
$email = $input['email'] ?? '';
$password = $input['password'] ?? '';

// Validation
if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
    http_response_code(400);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis']);
    exit;
}

try {
    $auth = new Auth();
    $result = $auth->register($nom, $prenom, $email, $password);

    header('Content-Type: application/json');
    echo json_encode($result);
} catch (Exception $e) {
    error_log("Register error: " . $e->getMessage());
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Erreur serveur']);
}
