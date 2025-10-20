<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupération des progressions avec informations des abonnés
$progressions = $pdo->query("
    SELECT 
        p.id,
        p.date,
        p.poids,
        p.imc,
        p.commentaire,
        u.nom AS abonne_nom,
        a.poids_actuel,
        a.poids_cible
    FROM progressions p
    JOIN abonnes a ON p.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    ORDER BY p.date DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📊 Suivi des progressions</h2>
        <a href="ajouter.php" class="btn btn-dark">
            <i class="fas fa-plus"></i> Nouvelle progression
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($progressions): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Abonné</th>
                                <th>Date</th>
                                <th>Poids (kg)</th>
                                <th>IMC</th>
                                <th>Objectif</th>
                                <th>Commentaire</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($progressions as $progression):
                                $poids_actuel = $progression['poids_actuel'];
                                $poids_cible = $progression['poids_cible'];
                                $progression_poids = $progression['poids'];

                                // Calcul de la progression
                                $difference = $poids_actuel > 0 ? $progression_poids - $poids_actuel : 0;
                                $pourcentage_objectif = $poids_cible > 0 ? abs(($progression_poids - $poids_actuel) / ($poids_cible - $poids_actuel) * 100) : 0;
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($progression['abonne_nom']) ?></strong>
                                        <?php if ($difference != 0): ?>
                                            <br>
                                            <small class="<?= $difference < 0 ? 'text-success' : 'text-danger' ?>">
                                                <?= $difference < 0 ? '↓' : '↑' ?> <?= number_format(abs($difference), 1) ?>kg
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?= formatDate($progression['date']) ?>
                                    </td>
                                    <td>
                                        <strong><?= number_format($progression['poids'], 1) ?> kg</strong>
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
                                        <?php if ($poids_cible > 0): ?>
                                            <div class="progress" style="height: 20px;" title="<?= number_format($pourcentage_objectif, 1) ?>% vers l'objectif">
                                                <div class="progress-bar 
                                                    <?= $pourcentage_objectif >= 100 ? 'bg-success' : ($pourcentage_objectif >= 50 ? 'bg-warning' : 'bg-info') ?>"
                                                    style="width: <?= min($pourcentage_objectif, 100) ?>%">
                                                    <?= number_format(min($pourcentage_objectif, 100), 0) ?>%
                                                </div>
                                            </div>
                                            <small class="text-muted">
                                                <?= number_format($poids_actuel, 1) ?>kg → <?= number_format($poids_cible, 1) ?>kg
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
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
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="modifier.php?id=<?= $progression['id'] ?>"
                                                class="btn btn-outline-warning"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $progression['id'] ?>"
                                                class="btn btn-outline-danger"
                                                title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette progression ?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 text-muted">
                    <small>Total : <?= count($progressions) ?> enregistrement(s) de progression</small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-chart-line fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucune progression enregistrée</h5>
                    <p class="text-muted">Commencez par ajouter le premier suivi de progression.</p>
                    <a href="ajouter.php" class="btn btn-dark">Ajouter une progression</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>