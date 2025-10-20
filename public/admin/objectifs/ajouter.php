<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupérer la liste des abonnés sans objectif ou avec objectif à modifier
$abonnes = $pdo->query("
    SELECT a.id, u.nom, a.poids_actuel, a.objectif 
    FROM abonnes a 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY u.nom
")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $abonne_id = $_POST['abonne_id'];
        $poids_cible = $_POST['poids_cible'] ?: 0;
        $frequence = $_POST['frequence'] ?: 3;
        $objectif = trim($_POST['objectif']);
        $objectif_duree = $_POST['objectif_duree'] ?: 4;
        $tour_de_bras = $_POST['tour_de_bras'] ?: 0;
        $tour_de_hanches = $_POST['tour_de_hanches'] ?: 0;
        $tour_de_fessier = $_POST['tour_de_fessier'] ?: 0;

        // Mettre à jour l'abonné avec les objectifs
        $stmt = $pdo->prepare("UPDATE abonnes SET poids_cible = ?, frequence = ?, objectif = ?, objectif_duree = ?, tour_de_bras = ?, tour_de_hanches = ?, tour_de_fessier = ? WHERE id = ?");
        $stmt->execute([$poids_cible, $frequence, $objectif, $objectif_duree, $tour_de_bras, $tour_de_hanches, $tour_de_fessier, $abonne_id]);

        // Envoyer une notification
        sendNotification($pdo, $abonne_id, 'Information', "Votre objectif a été défini : $objectif");

        redirectWithMessage('liste_objectifs.php', 'success', '✅ Objectif défini avec succès !');
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🎯 Définir un objectif</h2>
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
                        <select name="abonne_id" id="abonne_select" class="form-control" required onchange="updateInfosAbonne()">
                            <option value="">Sélectionner un abonné</option>
                            <?php foreach ($abonnes as $abonne): ?>
                                <option value="<?= $abonne['id'] ?>"
                                    data-poids-actuel="<?= $abonne['poids_actuel'] ?>"
                                    data-objectif="<?= htmlspecialchars($abonne['objectif'] ?? '') ?>">
                                    <?= htmlspecialchars($abonne['nom']) ?>
                                    <?php if ($abonne['objectif']): ?>
                                        (Objectif existant)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poids actuel</label>
                        <input type="text" id="poids_actuel_display" class="form-control" readonly style="background-color: #f8f9fa;">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Objectif principal</h5>
                <div class="mb-3">
                    <label class="form-label">Description de l'objectif *</label>
                    <textarea name="objectif" class="form-control" placeholder="Ex: Perdre 10kg, gagner en masse musculaire, améliorer l'endurance..." rows="3" required></textarea>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Poids cible (kg)</label>
                        <input type="number" step="0.1" name="poids_cible" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Durée de l'objectif (semaines)</label>
                        <input type="number" name="objectif_duree" class="form-control" value="4" min="1" max="52">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Plan d'entraînement</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Fréquence d'entraînement/semaine</label>
                        <select name="frequence" class="form-control">
                            <option value="1">1 séance</option>
                            <option value="2">2 séances</option>
                            <option value="3" selected>3 séances</option>
                            <option value="4">4 séances</option>
                            <option value="5">5 séances</option>
                            <option value="6">6 séances</option>
                            <option value="7">7 séances</option>
                        </select>
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Mesures corporelles (optionnel)</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tour de bras (cm)</label>
                        <input type="number" name="tour_de_bras" class="form-control" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de hanches (cm)</label>
                        <input type="number" name="tour_de_hanches" class="form-control" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de fessier (cm)</label>
                        <input type="number" name="tour_de_fessier" class="form-control" placeholder="0">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-bullseye"></i> Définir l'objectif
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateInfosAbonne() {
        const abonneSelect = document.getElementById('abonne_select');
        const poidsActuelDisplay = document.getElementById('poids_actuel_display');
        const objectifTextarea = document.querySelector('textarea[name="objectif"]');

        const selectedOption = abonneSelect.options[abonneSelect.selectedIndex];

        if (selectedOption.value) {
            const poidsActuel = selectedOption.getAttribute('data-poids-actuel');
            const objectifExistant = selectedOption.getAttribute('data-objectif');

            // Afficher le poids actuel
            poidsActuelDisplay.value = poidsActuel > 0 ? poidsActuel + ' kg' : 'Non défini';

            // Pré-remplir l'objectif existant si disponible
            if (objectifExistant) {
                objectifTextarea.value = objectifExistant;
            }
        } else {
            poidsActuelDisplay.value = '';
            objectifTextarea.value = '';
        }
    }
</script>

<?php include_once '../../../includes/footer.php'; ?>