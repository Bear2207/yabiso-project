<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

if (!isset($_GET['id'])) {
    header('Location: liste_abonnements.php');
    exit;
}

$abonnement_id = $_GET['id'];

// Récupérer l'abonnement
$stmt = $pdo->prepare("
    SELECT 
        ab.*,
        u.nom AS abonne_nom,
        a.telephone
    FROM abonnements ab
    JOIN abonnes a ON ab.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE ab.id = ?
");
$stmt->execute([$abonnement_id]);
$abonnement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$abonnement) {
    header('Location: liste_abonnements.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $type = $_POST['type'];
        $date_debut = $_POST['date_debut'];
        $date_fin = $_POST['date_fin'];
        $montant = $_POST['montant'];
        $statut = $_POST['statut'];

        // Mettre à jour l'abonnement
        $stmt = $pdo->prepare("UPDATE abonnements SET type = ?, date_debut = ?, date_fin = ?, montant = ?, statut = ? WHERE id = ?");
        $stmt->execute([$type, $date_debut, $date_fin, $montant, $statut, $abonnement_id]);

        $message = '<div class="alert alert-success">✅ Abonnement modifié avec succès !</div>';

        // Mettre à jour les données affichées
        $abonnement = array_merge($abonnement, [
            'type' => $type,
            'date_debut' => $date_debut,
            'date_fin' => $date_fin,
            'montant' => $montant,
            'statut' => $statut
        ]);
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Modifier l'abonnement</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Abonné :</strong> <?= htmlspecialchars($abonnement['abonne_nom']) ?>
                </div>
                <div class="col-md-6">
                    <strong>Téléphone :</strong>
                    <?= $abonnement['telephone'] ? htmlspecialchars($abonnement['telephone']) : 'Non renseigné' ?>
                </div>
            </div>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Type d'abonnement *</label>
                        <select name="type" class="form-control" required>
                            <option value="Mensuel" <?= $abonnement['type'] === 'Mensuel' ? 'selected' : '' ?>>Mensuel</option>
                            <option value="Trimestriel" <?= $abonnement['type'] === 'Trimestriel' ? 'selected' : '' ?>>Trimestriel</option>
                            <option value="Annuel" <?= $abonnement['type'] === 'Annuel' ? 'selected' : '' ?>>Annuel</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de début *</label>
                        <input type="date" name="date_debut" class="form-control" value="<?= $abonnement['date_debut'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de fin *</label>
                        <input type="date" name="date_fin" class="form-control" value="<?= $abonnement['date_fin'] ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Montant ($) *</label>
                        <input type="number" step="0.01" name="montant" class="form-control" value="<?= $abonnement['montant'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Statut *</label>
                        <select name="statut" class="form-control" required>
                            <option value="Actif" <?= $abonnement['statut'] === 'Actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="Expiré" <?= $abonnement['statut'] === 'Expiré' ? 'selected' : '' ?>>Expiré</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>