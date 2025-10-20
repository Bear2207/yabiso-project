<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkLogin('admin');
include_once '../../includes/header.php';
include_once '../../includes/navbar_admin.php';

// Récupération des stats
$total_abonnes = $pdo->query("SELECT COUNT(*) FROM abonnes")->fetchColumn();
$total_actifs = $pdo->query("SELECT COUNT(*) FROM abonnements WHERE statut='Actif'")->fetchColumn();
$total_paiements = $pdo->query("SELECT SUM(montant) FROM paiements")->fetchColumn();
$total_abonnements = $pdo->query("SELECT COUNT(*) FROM abonnements")->fetchColumn();

// Récupération des abonnés récents
$abonnes_recents = $pdo->query("
    SELECT u.nom, a.date_inscription 
    FROM abonnes a 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY a.date_inscription DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Récupération des paiements récents
$paiements_recents = $pdo->query("
    SELECT u.nom, p.montant, p.date_paiement, p.mode_paiement 
    FROM paiements p 
    JOIN abonnes a ON p.abonne_id = a.id 
    JOIN utilisateurs u ON a.utilisateur_id = u.id 
    ORDER BY p.date_paiement DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2 class="mb-4">Tableau de bord Administrateur</h2>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-primary">Abonnés</h5>
                    <p class="display-6"><?= $total_abonnes ?></p>
                    <small class="text-muted">Total des inscrits</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-success">Actifs</h5>
                    <p class="display-6"><?= $total_actifs ?></p>
                    <small class="text-muted">Abonnements actifs</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-warning">Revenus</h5>
                    <p class="display-6"><?= number_format($total_paiements, 2, ',', ' ') ?> $</p>
                    <small class="text-muted">Total des paiements</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-center shadow-sm">
                <div class="card-body">
                    <h5 class="card-title text-info">Abonnements</h5>
                    <p class="display-6"><?= $total_abonnements ?></p>
                    <small class="text-muted">Total souscrits</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">📋 Abonnés récents</h5>
                </div>
                <div class="card-body">
                    <?php if ($abonnes_recents): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($abonnes_recents as $abonne): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span><?= htmlspecialchars($abonne['nom']) ?></span>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($abonne['date_inscription'])) ?></small>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucun abonné récent</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">💰 Paiements récents</h5>
                </div>
                <div class="card-body">
                    <?php if ($paiements_recents): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($paiements_recents as $paiement): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= htmlspecialchars($paiement['nom']) ?></strong>
                                        <br>
                                        <small class="text-muted"><?= $paiement['mode_paiement'] ?></small>
                                    </div>
                                    <div class="text-end">
                                        <strong><?= number_format($paiement['montant'], 2, ',', ' ') ?> $</strong>
                                        <br>
                                        <small class="text-muted"><?= date('d/m/Y', strtotime($paiement['date_paiement'])) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucun paiement récent</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">📅 Notifications récentes</h5>
                </div>
                <div class="card-body">
                    <?php
                    $notifications = $pdo->query("
                        SELECT n.message, n.date_envoi, n.type, u.nom 
                        FROM notifications n 
                        LEFT JOIN abonnes a ON n.abonne_id = a.id 
                        LEFT JOIN utilisateurs u ON a.utilisateur_id = u.id 
                        ORDER BY n.date_envoi DESC 
                        LIMIT 5
                    ")->fetchAll(PDO::FETCH_ASSOC);

                    if ($notifications): ?>
                        <ul class="list-group list-group-flush">
                            <?php foreach ($notifications as $n): ?>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-<?=
                                                                    $n['type'] === 'Alerte' ? 'danger' : ($n['type'] === 'Rappel' ? 'warning' : 'info')
                                                                    ?> me-2">
                                                <?= $n['type'] ?>
                                            </span>
                                            <?= htmlspecialchars($n['message']) ?>
                                            <?php if ($n['nom']): ?>
                                                <small class="text-muted">(pour <?= $n['nom'] ?>)</small>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted"><?= date('d/m/Y H:i', strtotime($n['date_envoi'])) ?></small>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p class="text-muted">Aucune notification récente</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../../includes/footer.php'; ?>