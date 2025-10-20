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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $poids_cible = $_POST['poids_cible'] ?: 0;
    $frequence = $_POST['frequence'] ?: 3;
    $objectif_duree = $_POST['objectif_duree'] ?: 4;
    $objectif = trim($_POST['objectif']);
    $tour_de_bras = $_POST['tour_de_bras'] ?: 0;
    $tour_de_hanches = $_POST['tour_de_hanches'] ?: 0;
    $tour_de_fessier = $_POST['tour_de_fessier'] ?: 0;

    $stmt = $pdo->prepare("
        UPDATE abonnes SET 
            poids_cible = ?, 
            frequence = ?, 
            objectif_duree = ?, 
            objectif = ?,
            tour_de_bras = ?,
            tour_de_hanches = ?,
            tour_de_fessier = ?
        WHERE id = ?
    ");
    $stmt->execute([$poids_cible, $frequence, $objectif_duree, $objectif, $tour_de_bras, $tour_de_hanches, $tour_de_fessier, $abonne_id]);

    redirectWithMessage('objectifs.php', 'success', '✅ Vos objectifs ont été mis à jour avec succès !');
}

$stmt = $pdo->prepare("
    SELECT 
        poids_actuel,
        poids_cible,
        frequence,
        objectif_duree,
        objectif,
        tour_de_bras,
        tour_de_hanches,
        tour_de_fessier
    FROM abonnes 
    WHERE id = ?
");
$stmt->execute([$abonne_id]);
$abonne = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🎯 Mes Objectifs</h2>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <!-- Objectif principal -->
                <h5 class="mb-3 text-primary">Objectif principal</h5>
                <div class="mb-3">
                    <label class="form-label">Description de votre objectif *</label>
                    <textarea name="objectif" class="form-control" rows="3" placeholder="Ex: Perdre 10kg, gagner en masse musculaire, améliorer mon endurance..." required><?= htmlspecialchars($abonne['objectif']) ?></textarea>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Poids actuel (kg)</label>
                        <input type="text" class="form-control" value="<?= $abonne['poids_actuel'] ? number_format($abonne['poids_actuel'], 1) : 'Non renseigné' ?>" readonly style="background-color: #f8f9fa;">
                        <small class="text-muted">Mis à jour via le suivi de progression</small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Poids cible (kg)</label>
                        <input type="number" step="0.1" name="poids_cible" class="form-control" value="<?= $abonne['poids_cible'] ?>" placeholder="0.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée de l'objectif (semaines)</label>
                        <input type="number" name="objectif_duree" class="form-control" value="<?= $abonne['objectif_duree'] ?: 4 ?>" min="1" max="52">
                    </div>
                </div>

                <hr class="my-4">

                <!-- Plan d'entraînement -->
                <h5 class="mb-3 text-primary">Plan d'entraînement</h5>
                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label">Fréquence d'entraînement/semaine</label>
                        <select name="frequence" class="form-control">
                            <option value="1" <?= $abonne['frequence'] == 1 ? 'selected' : '' ?>>1 séance</option>
                            <option value="2" <?= $abonne['frequence'] == 2 ? 'selected' : '' ?>>2 séances</option>
                            <option value="3" <?= $abonne['frequence'] == 3 ? 'selected' : '' ?>>3 séances</option>
                            <option value="4" <?= $abonne['frequence'] == 4 ? 'selected' : '' ?>>4 séances</option>
                            <option value="5" <?= $abonne['frequence'] == 5 ? 'selected' : '' ?>>5 séances</option>
                            <option value="6" <?= $abonne['frequence'] == 6 ? 'selected' : '' ?>>6 séances</option>
                            <option value="7" <?= $abonne['frequence'] == 7 ? 'selected' : '' ?>>7 séances</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Mesures corporelles -->
                <h5 class="mb-3 text-primary">Mesures corporelles (optionnel)</h5>
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Tour de bras (cm)</label>
                        <input type="number" name="tour_de_bras" class="form-control" value="<?= $abonne['tour_de_bras'] ?>" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de hanches (cm)</label>
                        <input type="number" name="tour_de_hanches" class="form-control" value="<?= $abonne['tour_de_hanches'] ?>" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de fessier (cm)</label>
                        <input type="number" name="tour_de_fessier" class="form-control" value="<?= $abonne['tour_de_fessier'] ?>" placeholder="0">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Enregistrer mes objectifs
                    </button>
                    <a href="dashboard.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Section Progression -->
    <?php if ($abonne['poids_actuel'] > 0 && $abonne['poids_cible'] > 0):
        $difference = $abonne['poids_actuel'] - $abonne['poids_cible'];
        $pourcentage = ($abonne['poids_actuel'] > 0) ? (abs($difference) / $abonne['poids_actuel']) * 100 : 0;
    ?>
        <div class="card shadow-sm mt-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">📊 Ma Progression</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-4">
                        <h4><?= number_format($abonne['poids_actuel'], 1) ?> kg</h4>
                        <small class="text-muted">Poids actuel</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="text-success"><?= number_format($abonne['poids_cible'], 1) ?> kg</h4>
                        <small class="text-muted">Objectif</small>
                    </div>
                    <div class="col-md-4">
                        <h4 class="<?= $difference > 0 ? 'text-success' : 'text-warning' ?>">
                            <?= number_format(abs($difference), 1) ?> kg
                        </h4>
                        <small class="text-muted"><?= $difference > 0 ? 'À perdre' : 'À gagner' ?></small>
                    </div>
                </div>
                <div class="progress mt-3" style="height: 25px;">
                    <div class="progress-bar <?= $difference > 0 ? 'bg-success' : 'bg-warning' ?>"
                        style="width: <?= min($pourcentage, 100) ?>%">
                        <?= number_format(min($pourcentage, 100), 1) ?>%
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../includes/footer.php'; ?>