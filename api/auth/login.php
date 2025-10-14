<?php
// Activer l'affichage des erreurs pour le debug
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: application/json');

try {
    // Inclure les fichiers
    require_once '../includes/config.php';
    require_once '../includes/database.php';
    require_once '../includes/auth.php';

    // Tester la connexion DB d'abord
    $dbTest = Database::testConnection();
    if (!$dbTest['success']) {
        throw new Exception($dbTest['message']);
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Méthode non autorisée');
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        throw new Exception('Données JSON invalides');
    }

    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        throw new Exception('Email et mot de passe requis');
    }

    $auth = new Auth();
    $result = $auth->login($email, $password);

    echo json_encode($result);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
