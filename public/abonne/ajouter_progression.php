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

// Récupération des informations de l'abonné pour les objectifs
$stmt_abonne = $pdo->prepare("
    SELECT 
        a.poids_actuel,
        a.poids_cible,
        a.objectif,
        u.nom
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE a.id = ?
");
$stmt_abonne->execute([$abonne_id]);
$abonne = $stmt_abonne->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $date = $_POST['date'];
        $poids = $_POST['poids'] ?: null;
        $imc = $_POST['imc'] ?: null;
        $tour_de_bras = $_POST['tour_de_bras'] ?: null;
        $tour_de_hanches = $_POST['tour_de_hanches'] ?: null;
        $tour_de_fessier = $_POST['tour_de_fessier'] ?: null;
        $commentaire = trim($_POST['commentaire']);

        // Calcul automatique de l'IMC si non fourni
        if (!$imc && $poids && isset($_POST['taille']) && $_POST['taille'] > 0) {
            $taille = $_POST['taille'];
            $imc = round($poids / ($taille * $taille), 1);
        }

        // Insérer la progression
        $stmt = $pdo->prepare("
            INSERT INTO progressions (abonne_id, date, poids, imc, commentaire) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$abonne_id, $date, $poids, $imc, $commentaire]);

        // Mettre à jour le poids actuel de l'abonné
        if ($poids) {
            $stmt = $pdo->prepare("UPDATE abonnes SET poids_actuel = ? WHERE id = ?");
            $stmt->execute([$poids, $abonne_id]);
        }

        // Mettre à jour les mesures corporelles si fournies
        if ($tour_de_bras || $tour_de_hanches || $tour_de_fessier) {
            $update_fields = [];
            $update_values = [];

            if ($tour_de_bras) {
                $update_fields[] = "tour_de_bras = ?";
                $update_values[] = $tour_de_bras;
            }
            if ($tour_de_hanches) {
                $update_fields[] = "tour_de_hanches = ?";
                $update_values[] = $tour_de_hanches;
            }
            if ($tour_de_fessier) {
                $update_fields[] = "tour_de_fessier = ?";
                $update_values[] = $tour_de_fessier;
            }

            if (!empty($update_fields)) {
                $update_values[] = $abonne_id;
                $stmt = $pdo->prepare("UPDATE abonnes SET " . implode(", ", $update_fields) . " WHERE id = ?");
                $stmt->execute($update_values);
            }
        }

        redirectWithMessage('progression.php', 'success', '✅ Votre progression a été enregistrée avec succès !');
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Ajouter une mesure de progression</h2>
        <a href="progression.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour au suivi
        </a>
    </div>

    <?php displayFlashMessage(); ?>
    <?php if (isset($message)) echo $message; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-weight-scale"></i> Nouvelle mesure</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="progressionForm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Date de la mesure *</label>
                                <input type="date" name="date" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Poids (kg)</label>
                                <input type="number" step="0.1" name="poids" id="poids_input" class="form-control"
                                    value="<?= $abonne['poids_actuel'] ?>"
                                    placeholder="Ex: 75.5" min="30" max="300">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">IMC</label>
                                <input type="number" step="0.1" name="imc" id="imc_input" class="form-control"
                                    placeholder="Calculé automatiquement" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Taille (m) pour calcul IMC</label>
                                <input type="number" step="0.01" id="taille_input" class="form-control"
                                    placeholder="Ex: 1.75" min="1.00" max="2.50">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <button type="button" class="btn btn-outline-info w-100" onclick="calculerIMC()">
                                    <i class="fas fa-calculator"></i> Calculer l'IMC
                                </button>
                            </div>
                            <div class="col-md-8">
                                <div id="classification_imc" class="alert" style="display: none;">
                                    <strong>Classification :</strong> <span id="imc_text"></span>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <h6 class="text-primary mb-3"><i class="fas fa-ruler-combined"></i> Mesures corporelles (optionnel)</h6>
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Tour de bras (cm)</label>
                                <input type="number" name="tour_de_bras" class="form-control" placeholder="0" min="10" max="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tour de hanches (cm)</label>
                                <input type="number" name="tour_de_hanches" class="form-control" placeholder="0" min="50" max="200">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tour de fessier (cm)</label>
                                <input type="number" name="tour_de_fessier" class="form-control" placeholder="0" min="50" max="200">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Commentaires & observations</label>
                            <textarea name="commentaire" class="form-control" rows="4"
                                placeholder="Comment vous sentez-vous ? Difficultés particulières ? Succès ?..."></textarea>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save"></i> Enregistrer la mesure
                            </button>
                            <a href="progression.php" class="btn btn-outline-secondary">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!-- Informations objectif -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-bullseye"></i> Mon Objectif</h5>
                </div>
                <div class="card-body">
                    <?php if ($abonne['objectif']): ?>
                        <p class="card-text"><?= htmlspecialchars($abonne['objectif']) ?></p>
                    <?php else: ?>
                        <p class="text-muted">Aucun objectif défini</p>
                        <a href="objectifs.php" class="btn btn-sm btn-outline-success">Définir un objectif</a>
                    <?php endif; ?>

                    <?php if ($abonne['poids_actuel'] > 0 && $abonne['poids_cible'] > 0):
                        $difference = $abonne['poids_actuel'] - $abonne['poids_cible'];
                        $pourcentage = ($abonne['poids_actuel'] > 0) ? (abs($difference) / $abonne['poids_actuel']) * 100 : 0;
                    ?>
                        <hr>
                        <div class="text-center">
                            <div class="mb-2">
                                <strong>Actuel :</strong> <?= number_format($abonne['poids_actuel'], 1) ?> kg<br>
                                <strong>Objectif :</strong> <?= number_format($abonne['poids_cible'], 1) ?> kg
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar <?= $difference > 0 ? 'bg-success' : 'bg-warning' ?>"
                                    style="width: <?= min($pourcentage, 100) ?>%">
                                    <?= number_format(abs($difference), 1) ?> kg
                                </div>
                            </div>
                            <small class="text-muted mt-1">
                                <?= $difference > 0 ? 'À perdre' : 'À gagner' ?>
                            </small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Conseils -->
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-lightbulb"></i> Conseils</h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled small">
                        <li class="mb-2">📅 <strong>Régularité :</strong> Mesurez-vous toujours le même jour de la semaine</li>
                        <li class="mb-2">⏰ <strong>Moment :</strong> De préférence le matin à jeun</li>
                        <li class="mb-2">⚖️ <strong>Précision :</strong> Utilisez la même balance</li>
                        <li class="mb-2">💧 <strong>Hydratation :</strong> Buvez suffisamment d'eau</li>
                        <li class="mb-2">📝 <strong>Notes :</strong> Notez vos observations pour mieux comprendre votre progression</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function calculerIMC() {
        const poids = parseFloat(document.getElementById('poids_input').value);
        const taille = parseFloat(document.getElementById('taille_input').value);
        const imcInput = document.getElementById('imc_input');
        const classificationDiv = document.getElementById('classification_imc');
        const imcText = document.getElementById('imc_text');

        if (poids && taille && taille > 0) {
            const imc = (poids / (taille * taille)).toFixed(1);
            imcInput.value = imc;

            // Classification IMC
            let classification = '';
            let bgColor = '';

            if (imc < 18.5) {
                classification = 'Insuffisance pondérale';
                bgColor = 'alert-info';
            } else if (imc < 25) {
                classification = 'Poids normal';
                bgColor = 'alert-success';
            } else if (imc < 30) {
                classification = 'Surpoids';
                bgColor = 'alert-warning';
            } else {
                classification = 'Obésité';
                bgColor = 'alert-danger';
            }

            imcText.textContent = classification + ' (IMC: ' + imc + ')';
            classificationDiv.className = 'alert ' + bgColor;
            classificationDiv.style.display = 'block';
        } else {
            alert('Veuillez renseigner le poids et la taille pour calculer l\'IMC.');
        }
    }

    // Calcul automatique de l'IMC quand les champs sont remplis
    document.getElementById('poids_input').addEventListener('input', calculerIMCAuto);
    document.getElementById('taille_input').addEventListener('input', calculerIMCAuto);

    function calculerIMCAuto() {
        const poids = parseFloat(document.getElementById('poids_input').value);
        const taille = parseFloat(document.getElementById('taille_input').value);
        const imcInput = document.getElementById('imc_input');

        if (poids && taille && taille > 0) {
            const imc = (poids / (taille * taille)).toFixed(1);
            imcInput.value = imc;
        }
    }

    // Validation du formulaire
    document.getElementById('progressionForm').addEventListener('submit', function(e) {
        const poids = document.getElementById('poids_input').value;
        const date = document.querySelector('input[name="date"]').value;

        if (!date) {
            e.preventDefault();
            alert('Veuillez renseigner la date de la mesure.');
            return;
        }

        // Vérifier que la date n'est pas dans le futur
        const today = new Date().toISOString().split('T')[0];
        if (date > today) {
            e.preventDefault();
            alert('La date ne peut pas être dans le futur.');
            return;
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>