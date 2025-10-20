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

// Récupération des séances (sans la colonne created_at qui n'existe pas)
$stmt = $pdo->prepare("
    SELECT 
        id,
        date_seance,
        activite,
        duree,
        calories_brulees
    FROM seances 
    WHERE abonne_id = ? 
    ORDER BY date_seance DESC, id DESC
");
$stmt->execute([$abonne_id]);
$seances = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des statistiques
$stats = $pdo->prepare("
    SELECT 
        COUNT(*) as total_seances,
        SUM(duree) as total_duree,
        SUM(calories_brulees) as total_calories,
        AVG(duree) as moyenne_duree
    FROM seances 
    WHERE abonne_id = ?
");
$stats->execute([$abonne_id]);
$statistiques = $stats->fetch(PDO::FETCH_ASSOC);

// Traitement de l'ajout de séance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajouter_seance'])) {
    try {
        $date_seance = $_POST['date_seance'];
        $activite = trim($_POST['activite']);
        $duree = $_POST['duree'] ?: 0;
        $calories_brulees = $_POST['calories_brulees'] ?: 0;
        $notes = trim($_POST['notes']);

        $stmt = $pdo->prepare("
            INSERT INTO seances (abonne_id, date_seance, activite, duree, calories_brulees) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$abonne_id, $date_seance, $activite, $duree, $calories_brulees]);

        redirectWithMessage('seances.php', 'success', '✅ Séance enregistrée avec succès !');
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}

// Traitement de la suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['supprimer_seance'])) {
    try {
        $seance_id = $_POST['seance_id'];

        $stmt = $pdo->prepare("DELETE FROM seances WHERE id = ? AND abonne_id = ?");
        $stmt->execute([$seance_id, $abonne_id]);

        redirectWithMessage('seances.php', 'success', '✅ Séance supprimée avec succès !');
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💪 Mes Séances d'Entraînement</h2>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterSeanceModal">
            <i class="fas fa-plus"></i> Nouvelle séance
        </button>
    </div>

    <?php displayFlashMessage(); ?>
    <?php if (isset($message)) echo $message; ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total séances</h5>
                    <p class="display-6"><?= $statistiques['total_seances'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Temps total</h5>
                    <p class="display-6">
                        <?php
                        if ($statistiques['total_duree']) {
                            $heures = floor($statistiques['total_duree'] / 60);
                            $minutes = $statistiques['total_duree'] % 60;
                            echo $heures . 'h' . ($minutes < 10 ? '0' : '') . $minutes;
                        } else {
                            echo '0h00';
                        }
                        ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Calories brûlées</h5>
                    <p class="display-6"><?= $statistiques['total_calories'] ?? 0 ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Durée moyenne</h5>
                    <p class="display-6"><?= $statistiques['moyenne_duree'] ? round($statistiques['moyenne_duree']) . 'min' : '0min' ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Liste des séances -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white">
            <h5 class="mb-0">Historique des séances</h5>
        </div>
        <div class="card-body">
            <?php if ($seances): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Activité</th>
                                <th>Durée</th>
                                <th>Calories</th>
                                <th>Intensité</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($seances as $seance):
                                $intensite = 'Faible';
                                $bg_color = 'bg-success';

                                if ($seance['calories_brulees'] > 0 && $seance['duree'] > 0) {
                                    $calories_par_minute = $seance['calories_brulees'] / $seance['duree'];
                                    if ($calories_par_minute > 10) {
                                        $intensite = 'Élevée';
                                        $bg_color = 'bg-danger';
                                    } elseif ($calories_par_minute > 6) {
                                        $intensite = 'Moyenne';
                                        $bg_color = 'bg-warning';
                                    }
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= formatDate($seance['date_seance']) ?></strong>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($seance['activite']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-primary"><?= $seance['duree'] ?> min</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-warning"><?= $seance['calories_brulees'] ?> kcal</span>
                                    </td>
                                    <td>
                                        <span class="badge <?= $bg_color ?>"><?= $intensite ?></span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#detailSeanceModal"
                                                onclick="afficherDetailsSeance(<?= htmlspecialchars(json_encode($seance)) ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <form method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette séance ?')">
                                                <input type="hidden" name="seance_id" value="<?= $seance['id'] ?>">
                                                <input type="hidden" name="supprimer_seance" value="1">
                                                <button type="submit" class="btn btn-outline-danger">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Résumé -->
                <div class="mt-3 text-muted">
                    <small>
                        Total : <?= count($seances) ?> séance(s) |
                        Dernière séance : <?= $seances[0]['date_seance'] ? formatDate($seances[0]['date_seance']) : 'Aucune' ?>
                    </small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-dumbbell fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune séance enregistrée</h5>
                    <p class="text-muted">Commencez votre parcours fitness en ajoutant votre première séance !</p>
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterSeanceModal">
                        <i class="fas fa-plus"></i> Ajouter ma première séance
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Ajouter Séance -->
<div class="modal fade" id="ajouterSeanceModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-plus"></i> Nouvelle séance d'entraînement</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Date de la séance *</label>
                                <input type="date" name="date_seance" class="form-control" value="<?= date('Y-m-d') ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Type d'activité *</label>
                                <select name="activite" class="form-control" required>
                                    <option value="">Choisir une activité</option>
                                    <option value="Musculation">🏋️ Musculation</option>
                                    <option value="Cardio">🏃 Cardio</option>
                                    <option value="Yoga">🧘 Yoga</option>
                                    <option value="CrossFit">🔥 CrossFit</option>
                                    <option value="Natation">🏊 Natation</option>
                                    <option value="Cyclisme">🚴 Cyclisme</option>
                                    <option value="Course à pied">👟 Course à pied</option>
                                    <option value="Marche">🚶 Marche</option>
                                    <option value="Pilates">💪 Pilates</option>
                                    <option value="Autre">❓ Autre</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Durée (minutes) *</label>
                                <input type="number" name="duree" class="form-control" min="5" max="300" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Calories brûlées (estimées)</label>
                                <input type="number" name="calories_brulees" class="form-control" min="0" max="5000">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Calculateur calories</label>
                                <button type="button" class="btn btn-outline-info w-100" onclick="calculerCalories()">
                                    <i class="fas fa-calculator"></i> Calculer
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Calculateur calories (caché par défaut) -->
                    <div class="card mb-3" id="calculateurCalories" style="display: none;">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">Calculateur de calories</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="form-label">Poids (kg)</label>
                                    <input type="number" id="poids_calcul" class="form-control" step="0.1">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Intensité</label>
                                    <select id="intensite_calcul" class="form-control">
                                        <option value="3">Légère</option>
                                        <option value="5" selected>Modérée</option>
                                        <option value="8">Élevée</option>
                                        <option value="10">Très élevée</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Résultat</label>
                                    <input type="text" id="resultat_calories" class="form-control" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" name="ajouter_seance" value="1">
                    <button type="submit" class="btn btn-primary">Enregistrer la séance</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Détails Séance -->
<div class="modal fade" id="detailSeanceModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">Détails de la séance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="detailSeanceContent">
                    <!-- Contenu chargé par JavaScript -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Calculateur de calories
    function calculerCalories() {
        document.getElementById('calculateurCalories').style.display = 'block';
    }

    document.getElementById('poids_calcul').addEventListener('input', calculerCaloriesAuto);
    document.getElementById('intensite_calcul').addEventListener('change', calculerCaloriesAuto);

    function calculerCaloriesAuto() {
        const poids = parseFloat(document.getElementById('poids_calcul').value);
        const intensite = parseFloat(document.getElementById('intensite_calcul').value);
        const duree = parseFloat(document.querySelector('input[name="duree"]').value);
        const resultat = document.getElementById('resultat_calories');
        const inputCalories = document.querySelector('input[name="calories_brulees"]');

        if (poids && duree) {
            const calories = Math.round(poids * intensite * duree / 60);
            resultat.value = calories + ' kcal estimées';
            inputCalories.value = calories;
        }
    }

    // Affichage des détails d'une séance
    function afficherDetailsSeance(seance) {
        const content = `
        <div class="row">
            <div class="col-12">
                <p><strong>Date :</strong> ${seance.date_seance}</p>
                <p><strong>Activité :</strong> ${seance.activite}</p>
                <p><strong>Durée :</strong> ${seance.duree} minutes</p>
                <p><strong>Calories brûlées :</strong> ${seance.calories_brulees} kcal</p>
            </div>
        </div>
    `;
        document.getElementById('detailSeanceContent').innerHTML = content;
    }

    // Validation de la date (ne pas permettre les dates futures)
    document.querySelector('input[name="date_seance"]').addEventListener('change', function() {
        const today = new Date().toISOString().split('T')[0];
        if (this.value > today) {
            alert('La date ne peut pas être dans le futur.');
            this.value = today;
        }
    });
</script>

<?php include '../../includes/footer.php'; ?>