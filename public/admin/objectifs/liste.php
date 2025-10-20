<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupération des abonnés avec leurs objectifs
$abonnes = $pdo->query("
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
        a.tour_de_fessier,
        a.date_inscription
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    ORDER BY u.nom
")->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$total_avec_objectif = $pdo->query("SELECT COUNT(*) FROM abonnes WHERE objectif IS NOT NULL AND objectif != ''")->fetchColumn();
$total_sans_objectif = $pdo->query("SELECT COUNT(*) FROM abonnes WHERE objectif IS NULL OR objectif = ''")->fetchColumn();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>🎯 Objectifs des abonnés</h2>
        <a href="ajouter.php" class="btn btn-dark">
            <i class="fas fa-plus"></i> Définir un objectif
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total abonnés</h5>
                    <p class="display-6"><?= count($abonnes) ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Avec objectif</h5>
                    <p class="display-6"><?= $total_avec_objectif ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-warning text-white">
                <div class="card-body">
                    <h5 class="card-title">Sans objectif</h5>
                    <p class="display-6"><?= $total_sans_objectif ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($abonnes): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Abonné</th>
                                <th>Poids</th>
                                <th>Objectif principal</th>
                                <th>Fréquence</th>
                                <th>Durée</th>
                                <th>Progression</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($abonnes as $abonne):
                                $progression = 0;
                                if ($abonne['poids_actuel'] > 0 && $abonne['poids_cible'] > 0) {
                                    $difference = $abonne['poids_cible'] - $abonne['poids_actuel'];
                                    $progression = $difference != 0 ? (($abonne['poids_actuel'] - $abonne['poids_cible']) / abs($difference)) * 100 : 0;
                                }
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($abonne['nom']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            Inscrit le <?= formatDate($abonne['date_inscription']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($abonne['poids_actuel'] > 0): ?>
                                            <div>
                                                <strong><?= number_format($abonne['poids_actuel'], 1) ?> kg</strong>
                                                <?php if ($abonne['poids_cible'] > 0): ?>
                                                    <br>
                                                    <small class="text-muted">
                                                        → <?= number_format($abonne['poids_cible'], 1) ?> kg
                                                    </small>
                                                    <br>
                                                    <small class="<?= $abonne['poids_actuel'] > $abonne['poids_cible'] ? 'text-success' : 'text-warning' ?>">
                                                        <?= $abonne['poids_actuel'] > $abonne['poids_cible'] ? 'Perte' : 'Prise' ?> de poids
                                                    </small>
                                                <?php endif; ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($abonne['objectif']): ?>
                                            <div title="<?= htmlspecialchars($abonne['objectif']) ?>">
                                                <strong><?= strlen($abonne['objectif']) > 50 ?
                                                            htmlspecialchars(substr($abonne['objectif'], 0, 50)) . '...' :
                                                            htmlspecialchars($abonne['objectif']) ?></strong>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted badge bg-warning">À définir</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($abonne['frequence'] > 0): ?>
                                            <span class="badge bg-info">
                                                <?= $abonne['frequence'] ?> séance(s)/semaine
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($abonne['objectif_duree'] > 0): ?>
                                            <span class="badge bg-secondary">
                                                <?= $abonne['objectif_duree'] ?> semaine(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($progression != 0): ?>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar 
                                                    <?= $progression >= 100 ? 'bg-success' : ($progression >= 50 ? 'bg-warning' : 'bg-info') ?>"
                                                    style="width: <?= min(abs($progression), 100) ?>%">
                                                    <?= number_format(min(abs($progression), 100), 0) ?>%
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="modifier.php?id=<?= $abonne['id'] ?>"
                                                class="btn btn-outline-warning"
                                                title="Modifier l'objectif">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($abonne['objectif']): ?>
                                                <a href="supprimer_objectif.php?id=<?= $abonne['id'] ?>"
                                                    class="btn btn-outline-danger"
                                                    title="Supprimer l'objectif"
                                                    onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet objectif?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-bullseye fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun abonné trouvé</h5>
                    <p class="text-muted">Commencez par ajouter des abonnés.</p>
                    <a href="../abonnes/ajouter.php" class="btn btn-dark">Ajouter un abonné</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>