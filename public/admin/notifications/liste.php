<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Marquer comme lu quand on visualise une notification
if (isset($_GET['voir']) && is_numeric($_GET['voir'])) {
    $notification_id = $_GET['voir'];
    $stmt = $pdo->prepare("UPDATE notifications SET lu = TRUE WHERE id = ?");
    $stmt->execute([$notification_id]);
}

// Récupération des notifications
$notifications = $pdo->query("
    SELECT 
        n.id,
        n.type,
        n.message,
        n.date_envoi,
        n.lu,
        u.nom AS abonne_nom
    FROM notifications n
    LEFT JOIN abonnes a ON n.abonne_id = a.id
    LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id
    ORDER BY n.date_envoi DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$total_notifications = count($notifications);
$non_lues = $pdo->query("SELECT COUNT(*) FROM notifications WHERE lu = FALSE")->fetchColumn();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📢 Gestion des notifications</h2>
        <div>
            <a href="envoyer.php" class="btn btn-success">
                <i class="fas fa-paper-plane"></i> Envoyer une notification
            </a>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total</h5>
                    <p class="display-6"><?= $total_notifications ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Non lues</h5>
                    <p class="display-6"><?= $non_lues ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Types</h5>
                    <p class="display-6">3</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($notifications): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Type</th>
                                <th>Destinataire</th>
                                <th>Message</th>
                                <th>Date</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($notifications as $notification): ?>
                                <tr class="<?= !$notification['lu'] ? 'table-warning' : '' ?>">
                                    <td>
                                        <span class="badge 
                                            <?= $notification['type'] === 'Alerte' ? 'bg-danger' : ($notification['type'] === 'Rappel' ? 'bg-warning' : 'bg-info') ?>">
                                            <?= $notification['type'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $notification['abonne_nom'] ? htmlspecialchars($notification['abonne_nom']) :
                                            '<span class="badge bg-secondary">Tous</span>' ?>
                                    </td>
                                    <td>
                                        <div title="<?= htmlspecialchars($notification['message']) ?>">
                                            <?= strlen($notification['message']) > 80 ?
                                                htmlspecialchars(substr($notification['message'], 0, 80)) . '...' :
                                                htmlspecialchars($notification['message']) ?>
                                        </div>
                                    </td>
                                    <td>
                                        <small><?= formatDateTime($notification['date_envoi']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $notification['lu'] ? 'bg-success' : 'bg-warning' ?>">
                                            <?= $notification['lu'] ? 'Lu' : 'Non lu' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="?voir=<?= $notification['id'] ?>#notification-<?= $notification['id'] ?>"
                                                class="btn btn-outline-info"
                                                title="Marquer comme lu et voir le message"
                                                onclick="afficherMessage('<?= addslashes($notification['message']) ?>', <?= $notification['id'] ?>)">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $notification['id'] ?>"
                                                class="btn btn-outline-danger"
                                                title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette notification ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Total : <?= $total_notifications ?> notification(s) |
                        Non lues : <?= $non_lues ?>
                    </small>
                    <a href="marquer_tout_lu.php" class="btn btn-sm btn-outline-success">
                        <i class="fas fa-check-double"></i> Marquer tout comme lu
                    </a>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune notification</h5>
                    <p class="text-muted">Aucune notification n'a été envoyée pour le moment.</p>
                    <a href="envoyer.php" class="btn btn-success">Envoyer la première notification</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal pour afficher le message complet -->
<div class="modal fade" id="messageModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Message complet</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="messageComplet"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    function afficherMessage(message, notificationId = null) {
        document.getElementById('messageComplet').textContent = message;
        var modal = new bootstrap.Modal(document.getElementById('messageModal'));

        // Si une notification ID est fournie, marquer comme lu après fermeture du modal
        if (notificationId) {
            modal.show();

            // Marquer comme lu quand le modal est fermé
            document.getElementById('messageModal').addEventListener('hidden.bs.modal', function() {
                // Actualiser la page pour mettre à jour le statut
                window.location.href = 'liste_notifications.php?voir=' + notificationId;
            });
        } else {
            modal.show();
        }
    }

    // Si un paramètre 'voir' est présent dans l'URL, afficher le message automatiquement
    window.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const voirId = urlParams.get('voir');

        if (voirId) {
            // Trouver le message correspondant dans le tableau
            const notificationRow = document.querySelector('tr[href*="voir=' + voirId + '"]');
            if (notificationRow) {
                const message = notificationRow.querySelector('td:nth-child(3) div').getAttribute('title');
                afficherMessage(message);
            }

            // Retirer le paramètre de l'URL sans recharger la page
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    });
</script>

<?php include_once '../../../includes/footer.php'; ?>