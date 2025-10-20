<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

if (!isset($_GET['id'])) {
    header('Location: liste_paiements.php');
    exit;
}

$paiement_id = $_GET['id'];

// Récupérer le paiement
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        u.nom AS abonne_nom,
        a.id AS abonne_id,
        ab.type AS abonnement_type
    FROM paiements p
    JOIN abonnes a ON p.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    JOIN abonnements ab ON p.abonnement_id = ab.id
    WHERE p.id = ?
");
$stmt->execute([$paiement_id]);
$paiement = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$paiement) {
    header('Location: liste_paiements.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $montant = $_POST['montant'];
        $mode_paiement = $_POST['mode_paiement'];
        $date_paiement = $_POST['date_paiement'];

        // Mettre à jour le paiement
        $stmt = $pdo->prepare("UPDATE paiements SET montant = ?, mode_paiement = ?, date_paiement = ? WHERE id = ?");
        $stmt->execute([$montant, $mode_paiement, $date_paiement, $paiement_id]);

        $message = '<div class="alert alert-success">✅ Paiement modifié avec succès !</div>';

        // Mettre à jour les données affichées
        $paiement = array_merge($paiement, [
            'montant' => $montant,
            'mode_paiement' => $mode_paiement,
            'date_paiement' => $date_paiement
        ]);
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Modifier le paiement</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Abonné :</strong> <?= htmlspecialchars($paiement['abonne_nom']) ?>
                </div>
                <div class="col-md-6">
                    <strong>Type d'abonnement :</strong>
                    <span class="badge bg-info"><?= $paiement['abonnement_type'] ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Montant ($) *</label>
                        <input type="number" step="0.01" name="montant" class="form-control" value="<?= $paiement['montant'] ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="Cash" <?= $paiement['mode_paiement'] === 'Cash' ? 'selected' : '' ?>>Cash</option>
                            <option value="Carte" <?= $paiement['mode_paiement'] === 'Carte' ? 'selected' : '' ?>>Carte</option>
                            <option value="Mobile Money" <?= $paiement['mode_paiement'] === 'Mobile Money' ? 'selected' : '' ?>>Mobile Money</option>
                            <option value="Banque" <?= $paiement['mode_paiement'] === 'Banque' ? 'selected' : '' ?>>Banque</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de paiement *</label>
                        <input type="date" name="date_paiement" class="form-control" value="<?= $paiement['date_paiement'] ?>" required>
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