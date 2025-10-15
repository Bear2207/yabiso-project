<?php
function formatDate($date, $format = 'd/m/Y')
{
    if (empty($date)) return '';
    $datetime = new DateTime($date);
    return $datetime->format($format);
}

function formatCurrency($amount, $currency = '€')
{
    return number_format($amount, 2, ',', ' ') . ' ' . $currency;
}

function calculateAge($birthdate)
{
    if (empty($birthdate)) return null;
    $today = new DateTime();
    $birth = new DateTime($birthdate);
    return $today->diff($birth)->y;
}

function getDaysUntilExpiry($expiryDate)
{
    if (empty($expiryDate)) return null;
    $today = new DateTime();
    $expiry = new DateTime($expiryDate);

    if ($today > $expiry) {
        return -$today->diff($expiry)->days;
    }

    return $today->diff($expiry)->days;
}

function sendNotification($userId, $title, $message, $type = 'info')
{
    try {
        $db = Database::getInstance()->getConnection();

        $stmt = $db->prepare(
            "INSERT INTO notifications (utilisateur_id, titre, message, type_notification) 
             VALUES (?, ?, ?, ?)"
        );

        return $stmt->execute([$userId, $title, $message, $type]);
    } catch (PDOException $e) {
        error_log("Send notification error: " . $e->getMessage());
        return false;
    }
}

function checkExpiringSubscriptions()
{
    try {
        $db = Database::getInstance()->getConnection();

        // Trouver les abonnements qui expirent dans 7 jours
        $expiryDate = date('Y-m-d', strtotime('+7 days'));

        $stmt = $db->prepare(
            "SELECT au.*, u.email, u.prenom, u.nom 
             FROM abonnements_utilisateurs au 
             JOIN utilisateurs u ON au.utilisateur_id = u.utilisateur_id 
             WHERE au.date_fin = ? AND au.statut = 'actif'"
        );

        $stmt->execute([$expiryDate]);
        $expiringSubscriptions = $stmt->fetchAll();

        foreach ($expiringSubscriptions as $subscription) {
            sendNotification(
                $subscription['utilisateur_id'],
                'Abonnement expirant',
                "Votre abonnement expire le " . formatDate($subscription['date_fin']),
                'warning'
            );
        }

        return count($expiringSubscriptions);
    } catch (PDOException $e) {
        error_log("Check expiring subscriptions error: " . $e->getMessage());
        return false;
    }
}
function validateEmail($email)
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
