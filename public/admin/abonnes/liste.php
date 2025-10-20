<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupération des abonnés avec leurs informations utilisateur
$abonnes = $pdo->query("
    SELECT 
        a.id,
        u.nom,
        u.email,
        a.telephone,
        a.poids_actuel,
        a.poids_cible,
        a.objectif,
        a.date_inscription,
        ab.statut AS statut_abonnement
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    LEFT JOIN abonnements ab ON a.id = ab.abonne_id AND ab.statut = 'Actif'
    ORDER BY a.date_inscription DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>📋 Liste des abonnés</h2>
        <a href="ajouter.php" class="btn btn-dark">
            <i class="fas fa-plus"></i> Nouvel abonné
        </a>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <?php if ($abonnes): ?>
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Poids</th>
                                <th>Objectif</th>
                                <th>Inscription</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($abonnes as $abonne): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($abonne['nom']) ?></strong>
                                    </td>
                                    <td><?= htmlspecialchars($abonne['email']) ?></td>
                                    <td>
                                        <?= $abonne['telephone'] ? htmlspecialchars($abonne['telephone']) : '<span class="text-muted">N/A</span>' ?>
                                    </td>
                                    <td>
                                        <?php if ($abonne['poids_actuel'] > 0): ?>
                                            <small>
                                                <strong><?= $abonne['poids_actuel'] ?>kg</strong>
                                                <?php if ($abonne['poids_cible'] > 0): ?>
                                                    <br>
                                                    <span class="text-muted">→ <?= $abonne['poids_cible'] ?>kg</span>
                                                <?php endif; ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($abonne['objectif']): ?>
                                            <small title="<?= htmlspecialchars($abonne['objectif']) ?>">
                                                <?= strlen($abonne['objectif']) > 30 ?
                                                    htmlspecialchars(substr($abonne['objectif'], 0, 30)) . '...' :
                                                    htmlspecialchars($abonne['objectif']) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted">Non défini</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <small><?= formatDate($abonne['date_inscription']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $abonne['statut_abonnement'] === 'Actif' ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $abonne['statut_abonnement'] ? $abonne['statut_abonnement'] : 'Inactif' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="modifier.php?id=<?= $abonne['id'] ?>"
                                                class="btn btn-outline-warning"
                                                title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="progression.php?id=<?= $abonne['id'] ?>"
                                                class="btn btn-outline-info"
                                                title="Voir progression">
                                                <i class="fas fa-chart-line"></i>
                                            </a>
                                            <a href="seances.php?id=<?= $abonne['id'] ?>"
                                                class="btn btn-outline-primary"
                                                title="Séances">
                                                <i class="fas fa-dumbbell"></i>
                                            </a>
                                            <a href="supprimer.php?id=<?= $abonne['id'] ?>"
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
                    <small>Total : <?= count($abonnes) ?> abonné(s)</small>
                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-users fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">Aucun abonné trouvé</h5>
                    <p class="text-muted">Commencez par ajouter votre premier abonné.</p>
                    <a href="ajouter.php" class="btn btn-dark">Ajouter un abonné</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>