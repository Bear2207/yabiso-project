<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupération des paiements avec informations des abonnés et abonnements
$paiements = $pdo->query("
    SELECT 
        p.id,
        p.montant,
        p.mode_paiement,
        p.date_paiement,
        u.nom AS abonne_nom,
        ab.type AS abonnement_type,
        ab.statut AS abonnement_statut
    FROM paiements p
    JOIN abonnes a ON p.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    JOIN abonnements ab ON p.abonnement_id = ab.id
    ORDER BY p.date_paiement DESC
")->fetchAll(PDO::FETCH_ASSOC);

// Statistiques
$total_paiements = $pdo->query("SELECT SUM(montant) FROM paiements")->fetchColumn();
$total_paiements_mois = $pdo->query("SELECT SUM(montant) FROM paiements WHERE MONTH(date_paiement) = MONTH(CURRENT_DATE()) AND YEAR(date_paiement) = YEAR(CURRENT_DATE())")->fetchColumn();
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💰 Historique des paiements</h2>
        <a href="ajouter.php" class="btn btn-dark">
            <i class="fas fa-plus"></i> Nouveau paiement
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <!-- Statistiques -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card text-center bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total des paiements</h5>
                    <p class="display-6"><?= number_format($total_paiements, 2, ',', ' ') ?> $</p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card text-center bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Ce mois-ci</h5>
                    <p class="display-6"><?= number_format($total_paiements_mois, 2, ',', ' ') ?> $</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($paiements): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Abonné</th>
                                <th>Type d'abonnement</th>
                                <th>Montant</th>
                                <th>Mode de paiement</th>
                                <th>Date</th>
                                <th>Statut abonnement</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($paiements as $paiement): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($paiement['abonne_nom']) ?></strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info"><?= $paiement['abonnement_type'] ?></span>
                                    </td>
                                    <td>
                                        <strong class="text-success"><?= number_format($paiement['montant'], 2, ',', ' ') ?> $</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?= $paiement['mode_paiement'] ?></span>
                                    </td>
                                    <td>
                                        <?= formatDateTime($paiement['date_paiement']) ?>
                                    </td>
                                    <td>
                                        <span class="badge <?= $paiement['abonnement_statut'] === 'Actif' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $paiement['abonnement_statut'] ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="modifier.php?id=<?= $paiement['id'] ?>"
                                                class="btn btn-outline-warning"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $paiement['id'] ?>"
                                                class="btn btn-outline-danger"
                                                title="Supprimer"
                                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')">
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
                    <small>Total : <?= count($paiements) ?> paiement(s)</small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-money-bill-wave fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun paiement enregistré</h5>
                    <p class="text-muted">Commencez par enregistrer le premier paiement.</p>
                    <a href="ajouter.php" class="btn btn-dark">Enregistrer un paiement</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>