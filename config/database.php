<?php

/**
 * =====================================================
 * Fichier : config/database.php
 * Description : Connexion PDO à la base de données
 * Auteur : Bearing Kalela & ChatGPT
 * =====================================================
 */

$host = 'localhost';
$dbname = 'reforme_center_v2';
$username = 'root'; // ou ton user MySQL
$password = ''; // ton mot de passe MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Erreur de connexion : " . $e->getMessage());
}
