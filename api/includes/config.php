<?php
// Vérifier si les constantes sont déjà définies avant de les définir
if (!defined('DB_HOST')) {
    define('DB_HOST', 'localhost');
}

if (!defined('DB_NAME')) {
    define('DB_NAME', 'yabiso_db');
}

if (!defined('DB_USER')) {
    define('DB_USER', 'root');
}

if (!defined('DB_PASS')) {
    define('DB_PASS', '');
}

if (!defined('BASE_URL')) {
    define('BASE_URL', 'http://localhost/yabiso-project');
}

if (!defined('PASSWORD_COST')) {
    define('PASSWORD_COST', 12);
}

// Headers pour CORS
header('Access-Control-Allow-Origin: http://localhost');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Gestion des requêtes OPTIONS pour CORS
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Démarrer la session une seule fois
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Gestion des erreurs
ini_set('display_errors', 0); // Mettre à 0 en production
error_reporting(E_ALL);
