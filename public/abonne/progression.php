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

// Récupération des progressions
$stmt = $pdo->prepare("
    SELECT date, poids, imc, commentaire 
    FROM progressions 
    WHERE abonne_id = ? 
    ORDER BY date ASC
");
$stmt->execute([$abonne_id]);
$progressions = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Récupération des informations de l'abonné pour l'objectif
$stmt_abonne = $pdo->prepare("
    SELECT a.poids_actuel, a.poids_cible, a.objectif
    FROM abonnes a
    WHERE a.id = ?
");
$stmt_abonne->execute([$abonne_id]);
$abonne = $stmt_abonne->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📈 Mon Suivi de Progression</h2>
        <a href="ajouter_progression.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Ajouter une mesure
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Graphique -->
    <?php if (count($progressions) > 1): ?>
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Évolution du poids</h5>
            </div>
            <div class="card-body">
                <canvas id="progressChart" height="100"></canvas>
            </div>
        </div>
    <?php endif; ?>

    <!-- Tableau des progressions -->
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Historique des mesures</h5>
        </div>
        <div class="card-body">
            <?php if ($progressions): ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Poids (kg)</th>
                                <th>IMC</th>
                                <th>Commentaire</th>
                                <th>Évolution</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $poids_precedent = null;
                            foreach ($progressions as $index => $progression):
                                $evolution = '';
                                if ($poids_precedent !== null && $progression['poids']) {
                                    $difference = $progression['poids'] - $poids_precedent;
                                    if ($difference != 0) {
                                        $evolution = $difference < 0 ?
                                            '<span class="text-success">↓ ' . number_format(abs($difference), 1) . 'kg</span>' :
                                            '<span class="text-danger">↑ ' . number_format(abs($difference), 1) . 'kg</span>';
                                    }
                                }
                                $poids_precedent = $progression['poids'];
                            ?>
                                <tr>
                                    <td><strong><?= formatDate($progression['date']) ?></strong></td>
                                    <td>
                                        <?php if ($progression['poids']): ?>
                                            <span class="fs-5"><?= number_format($progression['poids'], 1) ?></span> kg
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($progression['imc']): ?>
                                            <span class="badge 
                                                <?= $progression['imc'] < 18.5 ? 'bg-info' : ($progression['imc'] < 25 ? 'bg-success' : ($progression['imc'] < 30 ? 'bg-warning' : 'bg-danger')) ?>">
                                                <?= number_format($progression['imc'], 1) ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($progression['commentaire']): ?>
                                            <small title="<?= htmlspecialchars($progression['commentaire']) ?>">
                                                <?= strlen($progression['commentaire']) > 50 ?
                                                    htmlspecialchars(substr($progression['commentaire'], 0, 50)) . '...' :
                                                    htmlspecialchars($progression['commentaire']) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= $evolution ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune progression enregistrée</h5>
                    <p class="text-muted">Commencez à suivre votre évolution en ajoutant votre première mesure.</p>
                    <a href="ajouter_progression.php" class="btn btn-primary">Ajouter une mesure</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if (count($progressions) > 1): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx = document.getElementById('progressChart');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_map(function ($p) {
                            return formatDate($p['date']);
                        }, $progressions)); ?>,
                datasets: [{
                    label: 'Poids (kg)',
                    data: <?= json_encode(array_column($progressions, 'poids')); ?>,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    title: {
                        display: true,
                        text: 'Évolution de votre poids'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: false,
                        title: {
                            display: true,
                            text: 'Poids (kg)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Date'
                        }
                    }
                }
            }
        });
    </script>
<?php endif; ?>

<?php include '../../includes/footer.php'; ?>