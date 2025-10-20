<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

if (!isset($_GET['id'])) {
    header('Location: liste_objectifs.php');
    exit;
}

$abonne_id = $_GET['id'];

// Récupérer les informations de l'abonné
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        u.nom,
        a.poids_actuel,
        a.poids_cible,
        a.frequence,
        a.objectif,
        a.objectif_duree,
        a.tour_de_bras,
        a.tour_de_hanches,
        a.tour_de_fessier
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$abonne_id]);
$abonne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$abonne) {
    header('Location: liste_objectifs.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $poids_cible = $_POST['poids_cible'] ?: 0;
        $frequence = $_POST['frequence'] ?: 3;
        $objectif = trim($_POST['objectif']);
        $objectif_duree = $_POST['objectif_duree'] ?: 4;
        $tour_de_bras = $_POST['tour_de_bras'] ?: 0;
        $tour_de_hanches = $_POST['tour_de_hanches'] ?: 0;
        $tour_de_fessier = $_POST['tour_de_fessier'] ?: 0;

        // Mettre à jour l'abonné
        $stmt = $pdo->prepare("UPDATE abonnes SET poids_cible = ?, frequence = ?, objectif = ?, objectif_duree = ?, tour_de_bras = ?, tour_de_hanches = ?, tour_de_fessier = ? WHERE id = ?");
        $stmt->execute([$poids_cible, $frequence, $objectif, $objectif_duree, $tour_de_bras, $tour_de_hanches, $tour_de_fessier, $abonne_id]);

        // Envoyer une notification
        sendNotification($pdo, $abonne_id, 'Information', "Votre objectif a été mis à jour : $objectif");

        redirectWithMessage('liste_objectifs.php', 'success', '✅ Objectif modifié avec succès !');
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Modifier l'objectif</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Abonné :</strong> <?= htmlspecialchars($abonne['nom']) ?>
                </div>
                <div class="col-md-6">
                    <strong>Poids actuel :</strong>
                    <?= $abonne['poids_actuel'] > 0 ? number_format($abonne['poids_actuel'], 1) . ' kg' : 'Non défini' ?>
                </div>
            </div>

            <form method="POST">
                <h5 class="mb-3 text-primary">Objectif principal</h5>
                <div class="mb-3">
                    <label class="form-label">Description de l'objectif *</label>
                    <textarea name="objectif" class="form-control" rows="3" required><?= htmlspecialchars($abonne['objectif']) ?></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Poids cible (kg)</label>
                        <input type="number" step="0.1" name="poids_cible" class="form-control" value="<?= $abonne['poids_cible'] ?>" placeholder="0.0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Durée de l'objectif (semaines)</label>
                        <input type="number" name="objectif_duree" class="form-control" value="<?= $abonne['objectif_duree'] ?: 4 ?>" min="1" max="52">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Plan d'entraînement</h5>
                <div class="row mb-3">
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

                <h5 class="mb-3 text-primary">Mesures corporelles</h5>
                <div class="row mb-3">
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
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Mettre à jour l'objectif
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>