<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupérer la liste des abonnés
$abonnes = $pdo->query("
    SELECT a.id, u.nom 
    FROM abonnes a 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY u.nom
")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $abonne_id = $_POST['abonne_id'] !== 'all' ? $_POST['abonne_id'] : null;
        $type = $_POST['type'];
        $message_text = trim($_POST['message']);

        if ($abonne_id) {
            // Notification à un abonné spécifique
            sendNotification($pdo, $abonne_id, $type, $message_text);
            $count = 1;
        } else {
            // Notification à tous les abonnés
            $stmt = $pdo->query("SELECT id FROM abonnes");
            $abonnes_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $count = 0;

            foreach ($abonnes_ids as $id) {
                sendNotification($pdo, $id, $type, $message_text);
                $count++;
            }
        }

        redirectWithMessage('liste_notifications.php', 'success', "✅ Notification envoyée avec succès à $count destinataire(s) !");
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✉️ Envoyer une notification</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Destinataire *</label>
                        <select name="abonne_id" class="form-control" required>
                            <option value="all">📢 Tous les abonnés</option>
                            <optgroup label="Abonnés individuels">
                                <?php foreach ($abonnes as $abonne): ?>
                                    <option value="<?= $abonne['id'] ?>">
                                        👤 <?= htmlspecialchars($abonne['nom']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type de notification *</label>
                        <select name="type" class="form-control" required>
                            <option value="Information">ℹ️ Information</option>
                            <option value="Alerte">⚠️ Alerte</option>
                            <option value="Rappel">🔔 Rappel</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Message *</label>
                    <textarea name="message" class="form-control" rows="5" placeholder="Rédigez votre message ici..." required></textarea>
                    <small class="text-muted">Maximum 500 caractères</small>
                </div>

                <div class="mb-3">
                    <div class="alert alert-info">
                        <strong>💡 Conseils :</strong>
                        <ul class="mb-0">
                            <li>Les <strong>Informations</strong> sont pour les annonces générales</li>
                            <li>Les <strong>Alertes</strong> pour les situations importantes/urgentes</li>
                            <li>Les <strong>Rappels</strong> pour les échéances et renouvellements</li>
                        </ul>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-paper-plane"></i> Envoyer la notification
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Limiter la longueur du message
    document.querySelector('textarea[name="message"]').addEventListener('input', function() {
        if (this.value.length > 500) {
            this.value = this.value.substring(0, 500);
        }
    });
</script>

<?php include_once '../../../includes/footer.php'; ?>