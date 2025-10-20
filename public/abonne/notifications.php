<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkLogin('abonne');

$utilisateur_id = $_SESSION['utilisateur_id'];

// Récupération de l'ID de l'abonné
$stmt_abonne_id = $pdo->prepare("SELECT id FROM abonnes WHERE utilisateur_id = ?");
$stmt_abonne_id->execute([$utilisateur_id]);
$abonne_data = $stmt_abonne_id->fetch(PDO::FETCH_ASSOC);
$abonne_id = $abonne_data['id'];

// Récupération des notifications
$stmt = $pdo->prepare("
    SELECT type, message, date_envoi, lu
    FROM notifications 
    WHERE abonne_id = ? 
    ORDER BY date_envoi DESC
");
$stmt->execute([$abonne_id]);
$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marquer toutes les notifications comme lues
$pdo->prepare("UPDATE notifications SET lu = TRUE WHERE abonne_id = ?")->execute([$abonne_id]);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📢 Mes Notifications</h2>
        <span class="badge bg-primary"><?= count($notifications) ?> notification(s)</span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($notifications): ?>
                <div class="list-group">
                    <?php foreach ($notifications as $notification): ?>
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge 
                                            <?= $notification['type'] === 'Alerte' ? 'bg-danger' : ($notification['type'] === 'Rappel' ? 'bg-warning' : 'bg-info') ?> me-2">
                                            <?= $notification['type'] ?>
                                        </span>
                                        <small class="text-muted"><?= formatDateTime($notification['date_envoi']) ?></small>
                                    </div>
                                    <p class="mb-0"><?= htmlspecialchars($notification['message']) ?></p>
                                </div>
                                <?php if (!$notification['lu']): ?>
                                    <span class="badge bg-success ms-2">Nouveau</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune notification</h5>
                    <p class="text-muted">Vous n'avez aucune notification pour le moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>