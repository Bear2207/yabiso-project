<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupération des abonnements avec informations des abonnés
$abonnements = $pdo->query("
    SELECT 
        ab.id,
        ab.type,
        ab.date_debut,
        ab.date_fin,
        ab.montant,
        ab.statut,
        u.nom AS abonne_nom,
        a.telephone
    FROM abonnements ab
    JOIN abonnes a ON ab.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    ORDER BY ab.date_debut DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📋 Liste des abonnements</h2>
        <a href="ajouter.php" class="btn btn-dark">
            <i class="fas fa-plus"></i> Nouvel abonnement
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($abonnements): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Abonné</th>
                                <th>Type</th>
                                <th>Période</th>
                                <th>Montant</th>
                                <th>Statut</th>
                                <th>Jours restants</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($abonnements as $abonnement):
                                $jours_restants = (strtotime($abonnement['date_fin']) - time()) / (60 * 60 * 24);
                                $jours_restants = max(0, ceil($jours_restants));
                            ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($abonnement['abonne_nom']) ?></strong>
                                        <?php if ($abonnement['telephone']): ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($abonnement['telephone']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $abonnement['type'] ?></span>
                                    </td>
                                    <td>
                                        <small>
                                            <?= formatDate($abonnement['date_debut']) ?><br>
                                            <strong>→</strong> <?= formatDate($abonnement['date_fin']) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <strong><?= number_format($abonnement['montant'], 2, ',', ' ') ?> $</strong>
                                    </td>
                                    <td>
                                        <span class="badge <?= $abonnement['statut'] === 'Actif' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $abonnement['statut'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($abonnement['statut'] === 'Actif'): ?>
                                            <span class="badge <?= $jours_restants <= 7 ? 'bg-warning' : 'bg-primary' ?>">
                                                <?= $jours_restants ?> jour(s)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="modifier.php?id=<?= $abonnement['id'] ?>"
                                                class="btn btn-outline-warning"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="paiements.php?abonnement_id=<?= $abonnement['id'] ?>"
                                                class="btn btn-outline-success"
                                                title="Paiements">
                                                <i class="fas fa-money-bill"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $abonnement['id'] ?>"
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
                    <small>
                        Total : <?= count($abonnements) ?> abonnement(s) |
                        Actifs : <?= count(array_filter($abonnements, fn($a) => $a['statut'] === 'Actif')) ?>
                    </small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-receipt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun abonnement trouvé</h5>
                    <p class="text-muted">Commencez par créer le premier abonnement.</p>
                    <a href="ajouter.php" class="btn btn-dark">Créer un abonnement</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>