<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupérer la liste des abonnés
$abonnes = $pdo->query("
    SELECT a.id, u.nom, a.poids_actuel, a.poids_cible 
    FROM abonnes a 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY u.nom
")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $abonne_id = $_POST['abonne_id'];
        $date = $_POST['date'];
        $poids = $_POST['poids'] ?: null;
        $imc = $_POST['imc'] ?: null;
        $commentaire = trim($_POST['commentaire']);

        // Calcul automatique de l'IMC si non fourni mais poids fourni
        if (!$imc && $poids) {
            // Récupérer la taille de l'abonné (à stocker dans la base si disponible)
            // Pour l'instant, on laisse l'IMC manuel ou calculé côté client avec JavaScript
        }

        $stmt = $pdo->prepare("INSERT INTO progressions (abonne_id, date, poids, imc, commentaire) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$abonne_id, $date, $poids, $imc, $commentaire]);

        // Mettre à jour le poids actuel de l'abonné
        if ($poids) {
            $stmt = $pdo->prepare("UPDATE abonnes SET poids_actuel = ? WHERE id = ?");
            $stmt->execute([$poids, $abonne_id]);
        }

        $message = '<div class="alert alert-success">✅ Progression enregistrée avec succès !</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📈 Ajouter une progression</h2>
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
                                    data-poids-cible="<?= $abonne['poids_cible'] ?>">
                                    <?= htmlspecialchars($abonne['nom']) ?>
                                    <?php if ($abonne['poids_actuel'] > 0): ?>
                                        (<?= number_format($abonne['poids_actuel'], 1) ?>kg)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div id="infos_abonne" class="alert alert-info mb-3" style="display: none;">
                    <strong>Objectif de l'abonné :</strong>
                    <span id="poids_actuel">-</span> → <span id="poids_cible">-</span>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" step="0.1" name="poids" id="poids_input" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">IMC</label>
                        <input type="number" step="0.1" name="imc" id="imc_input" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Calculer l'IMC</label>
                        <button type="button" class="btn btn-outline-info w-100" onclick="calculerIMC()">
                            <i class="fas fa-calculator"></i> Calculer
                        </button>
                    </div>
                </div>

                <div class="row mb-3" id="calcul_imc_section" style="display: none;">
                    <div class="col-md-6">
                        <label class="form-label">Taille (m)</label>
                        <input type="number" step="0.01" id="taille_input" class="form-control" placeholder="1.75">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Résultat IMC</label>
                        <input type="text" id="resultat_imc" class="form-control" readonly>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" placeholder="Observations, difficultés, réussites..." rows="3"></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Enregistrer la progression
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
        const infosAbonne = document.getElementById('infos_abonne');
        const poidsActuelSpan = document.getElementById('poids_actuel');
        const poidsCibleSpan = document.getElementById('poids_cible');
        const poidsInput = document.getElementById('poids_input');

        const selectedOption = abonneSelect.options[abonneSelect.selectedIndex];

        if (selectedOption.value) {
            const poidsActuel = selectedOption.getAttribute('data-poids-actuel');
            const poidsCible = selectedOption.getAttribute('data-poids-cible');

            poidsActuelSpan.textContent = poidsActuel > 0 ? poidsActuel + 'kg' : 'Non défini';
            poidsCibleSpan.textContent = poidsCible > 0 ? poidsCible + 'kg' : 'Non défini';

            // Pré-remplir le poids avec le poids actuel
            if (poidsActuel > 0) {
                poidsInput.value = poidsActuel;
            }

            infosAbonne.style.display = 'block';
        } else {
            infosAbonne.style.display = 'none';
        }
    }

    function calculerIMC() {
        document.getElementById('calcul_imc_section').style.display = 'block';
    }

    document.getElementById('taille_input').addEventListener('input', function() {
        const poids = document.getElementById('poids_input').value;
        const taille = this.value;
        const resultatIMC = document.getElementById('resultat_imc');
        const imcInput = document.getElementById('imc_input');

        if (poids && taille) {
            const imc = (poids / (taille * taille)).toFixed(1);
            resultatIMC.value = imc;
            imcInput.value = imc;

            // Colorer le résultat selon la classification IMC
            let classification = '';
            if (imc < 18.5) classification = 'Insuffisance pondérale';
            else if (imc < 25) classification = 'Poids normal';
            else if (imc < 30) classification = 'Surpoids';
            else classification = 'Obésité';

            resultatIMC.value = imc + ' (' + classification + ')';
        }
    });
</script>

<?php include_once '../../../includes/footer.php'; ?>