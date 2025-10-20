<?php
// Fonctions utilitaires

// Vérifie si l'utilisateur est connecté
function checkLogin($role = null)
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['utilisateur_id'])) {
        header("Location: ../index.php");
        exit;
    }

    // Vérifie le rôle (admin ou abonné)
    if ($role && $_SESSION['role'] !== $role) {
        header("Location: ../index.php");
        exit;
    }
}

// Formate une date lisible
function formatDate($date)
{
    return date('d/m/Y', strtotime($date));
}

// Formate une date avec heure
function formatDateTime($date)
{
    return date('d/m/Y H:i', strtotime($date));
}

// Envoie une notification à un abonné
function sendNotification($pdo, $abonne_id, $type, $message)
{
    $stmt = $pdo->prepare("INSERT INTO notifications (abonne_id, type, message) VALUES (?, ?, ?)");
    return $stmt->execute([$abonne_id, $type, $message]);
}

// Récupère le nom de l'utilisateur connecté
function getCurrentUserName($pdo)
{
    if (isset($_SESSION['utilisateur_id'])) {
        $stmt = $pdo->prepare("SELECT nom FROM utilisateurs WHERE id = ?");
        $stmt->execute([$_SESSION['utilisateur_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $user['nom'] : 'Utilisateur';
    }
    return 'Utilisateur';
}

// Vérifie si un email existe déjà
function emailExists($pdo, $email, $exclude_user_id = null)
{
    $sql = "SELECT COUNT(*) FROM utilisateurs WHERE email = ?";
    $params = [$email];

    if ($exclude_user_id) {
        $sql .= " AND id != ?";
        $params[] = $exclude_user_id;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchColumn() > 0;
}

// Hash un mot de passe
function hashPassword($password)
{
    return password_hash($password, PASSWORD_DEFAULT);
}

// Vérifie un mot de passe
function verifyPassword($password, $hashedPassword)
{
    return password_verify($password, $hashedPassword);
}

// Génère un mot de passe aléatoire
function generateRandomPassword($length = 8)
{
    $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()';
    $password = '';
    for ($i = 0; $i < $length; $i++) {
        $password .= $chars[random_int(0, strlen($chars) - 1)];
    }
    return $password;
}

// Redirige avec un message
function redirectWithMessage($url, $type, $message)
{
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: $url");
    exit;
}

// Affiche un message flash
function displayFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $alertClass = '';

        switch ($message['type']) {
            case 'success':
                $alertClass = 'alert-success';
                break;
            case 'error':
                $alertClass = 'alert-danger';
                break;
            case 'warning':
                $alertClass = 'alert-warning';
                break;
            case 'info':
                $alertClass = 'alert-info';
                break;
            default:
                $alertClass = 'alert-primary';
        }

        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($message['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';

        unset($_SESSION['flash_message']);
    }
}
