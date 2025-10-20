<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupérer la liste des abonnés pour le select
$abonnes = $pdo->query("
    SELECT a.id, u.nom, u.email 
    FROM abonnes a 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY u.nom
")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $abonne_id = $_POST['abonne_id'];
        $type = $_POST['type'];
        $date_debut = $_POST['date_debut'];
        $montant = $_POST['montant'];

        // Calculer la date de fin selon le type
        switch ($type) {
            case 'Mensuel':
                $date_fin = date('Y-m-d', strtotime($date_debut . ' +1 month'));
                break;
            case 'Trimestriel':
                $date_fin = date('Y-m-d', strtotime($date_debut . ' +3 months'));
                break;
            case 'Annuel':
                $date_fin = date('Y-m-d', strtotime($date_debut . ' +1 year'));
                break;
            default:
                $date_fin = $date_debut;
        }

        // Désactiver les anciens abonnements de cet abonné
        $stmt = $pdo->prepare("UPDATE abonnements SET statut = 'Expiré' WHERE abonne_id = ? AND statut = 'Actif'");
        $stmt->execute([$abonne_id]);

        // Créer le nouvel abonnement
        $stmt = $pdo->prepare("INSERT INTO abonnements (abonne_id, type, date_debut, date_fin, montant, statut) VALUES (?, ?, ?, ?, ?, 'Actif')");
        $stmt->execute([$abonne_id, $type, $date_debut, $date_fin, $montant]);

        $abonnement_id = $pdo->lastInsertId();

        // Créer un paiement associé
        $stmt = $pdo->prepare("INSERT INTO paiements (abonne_id, abonnement_id, montant, mode_paiement, date_paiement) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$abonne_id, $abonnement_id, $montant, $_POST['mode_paiement'], $date_debut]);

        // Envoyer une notification
        sendNotification($pdo, $abonne_id, 'Information', "Votre abonnement {$type} a été activé jusqu'au " . formatDate($date_fin));

        $message = '<div class="alert alert-success">✅ Abonnement créé avec succès !</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>➕ Créer un abonnement</h2>
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
                        <label class="form-label">Abonné *</label>
                        <select name="abonne_id" class="form-control" required>
                            <option value="">Sélectionner un abonné</option>
                            <?php foreach ($abonnes as $abonne): ?>
                                <option value="<?= $abonne['id'] ?>">
                                    <?= htmlspecialchars($abonne['nom']) ?> (<?= htmlspecialchars($abonne['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Type d'abonnement *</label>
                        <select name="type" class="form-control" required>
                            <option value="Mensuel">Mensuel - 30 jours</option>
                            <option value="Trimestriel">Trimestriel - 90 jours</option>
                            <option value="Annuel">Annuel - 365 jours</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Montant ($) *</label>
                        <input type="number" step="0.01" name="montant" class="form-control" placeholder="0.00" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="Cash">Cash</option>
                            <option value="Carte">Carte</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Banque">Banque</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="alert alert-info">
                            <strong>💡 Information :</strong>
                            La date de fin sera calculée automatiquement selon le type d'abonnement sélectionné.
                            Tout abonnement actif existant sera automatiquement désactivé.
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Créer l'abonnement
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>