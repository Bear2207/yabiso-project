<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkLogin('abonne');

$utilisateur_id = $_SESSION['utilisateur_id'];

// Récupération des infos principales de l'abonné
$stmt = $pdo->prepare("
    SELECT 
        u.nom,
        a.poids_actuel,
        a.poids_cible,
        a.objectif,
        a.frequence,
        ab.type AS abonnement_type,
        ab.date_fin
    FROM utilisateurs u
    JOIN abonnes a ON u.id = a.utilisateur_id
    LEFT JOIN abonnements ab ON a.id = ab.abonne_id AND ab.statut = 'Actif'
    WHERE u.id = ?
");
$stmt->execute([$utilisateur_id]);
$abonne = $stmt->fetch(PDO::FETCH_ASSOC);

// Récupération de l'ID de l'abonné pour les autres requêtes
$stmt_abonne_id = $pdo->prepare("SELECT id FROM abonnes WHERE utilisateur_id = ?");
$stmt_abonne_id->execute([$utilisateur_id]);
$abonne_data = $stmt_abonne_id->fetch(PDO::FETCH_ASSOC);
$abonne_id = $abonne_data['id'];

// Dernières progressions
$progressions = $pdo->prepare("
    SELECT date, poids, imc, commentaire 
    FROM progressions 
    WHERE abonne_id = ? 
    ORDER BY date DESC 
    LIMIT 5
");
$progressions->execute([$abonne_id]);
$progressions = $progressions->fetchAll(PDO::FETCH_ASSOC);

// Notifications récentes
$notifications = $pdo->prepare("
    SELECT type, message, date_envoi, lu
    FROM notifications 
    WHERE abonne_id = ? 
    ORDER BY date_envoi DESC 
    LIMIT 5
");
$notifications->execute([$abonne_id]);
$notifications = $notifications->fetchAll(PDO::FETCH_ASSOC);

// Marquer les notifications comme lues
$pdo->prepare("UPDATE notifications SET lu = TRUE WHERE abonne_id = ? AND lu = FALSE")->execute([$abonne_id]);

// Séances récentes
$seances = $pdo->prepare("
    SELECT date_seance, activite, duree, calories_brulees
    FROM seances 
    WHERE abonne_id = ? 
    ORDER BY date_seance DESC 
    LIMIT 3
");
$seances->execute([$abonne_id]);
$seances = $seances->fetchAll(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👋 Bienvenue, <?= htmlspecialchars($abonne['nom']); ?> !</h2>
        <div class="badge bg-primary fs-6">
            <?= $abonne['abonnement_type'] ?? 'Aucun abonnement' ?>
        </div>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="row g-4">
        <!-- Carte Objectif -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🎯 Mon Objectif</h5>
                </div>
                <div class="card-body">
                    <?php if ($abonne['objectif']): ?>
                        <p class="card-text"><?= htmlspecialchars($abonne['objectif']) ?></p>
                    <?php else: ?>
                        <p class="card-text text-muted">Aucun objectif défini</p>
                    <?php endif; ?>

                    <div class="mt-3">
                        <strong>Poids actuel :</strong>
                        <span class="fs-5"><?= $abonne['poids_actuel'] ? number_format($abonne['poids_actuel'], 1) . ' kg' : 'Non renseigné' ?></span>
                    </div>

                    <?php if ($abonne['poids_cible'] > 0): ?>
                        <div class="mt-2">
                            <strong>Objectif :</strong>
                            <span class="fs-5 text-success"><?= number_format($abonne['poids_cible'], 1) ?> kg</span>
                        </div>
                        <?php if ($abonne['poids_actuel'] > 0):
                            $difference = $abonne['poids_actuel'] - $abonne['poids_cible'];
                            $pourcentage = $abonne['poids_actuel'] > 0 ? (abs($difference) / $abonne['poids_actuel']) * 100 : 0;
                        ?>
                            <div class="progress mt-2" style="height: 20px;">
                                <div class="progress-bar <?= $difference > 0 ? 'bg-success' : 'bg-warning' ?>"
                                    style="width: <?= min($pourcentage, 100) ?>%">
                                    <?= number_format(abs($difference), 1) ?>kg <?= $difference > 0 ? 'à perdre' : 'à gagner' ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php if ($abonne['frequence'] > 0): ?>
                        <div class="mt-2">
                            <strong>Fréquence :</strong>
                            <span class="badge bg-info"><?= $abonne['frequence'] ?> séances/semaine</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Carte Abonnement -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">📅 Mon Abonnement</h5>
                </div>
                <div class="card-body">
                    <?php if ($abonne['abonnement_type']): ?>
                        <div class="text-center">
                            <h4 class="text-success"><?= $abonne['abonnement_type'] ?></h4>
                            <p class="mb-2">Valide jusqu'au</p>
                            <h5 class="text-dark"><?= formatDate($abonne['date_fin']) ?></h5>

                            <?php
                            $jours_restants = (strtotime($abonne['date_fin']) - time()) / (60 * 60 * 24);
                            $jours_restants = max(0, ceil($jours_restants));
                            ?>
                            <div class="mt-3">
                                <span class="badge <?= $jours_restants <= 7 ? 'bg-warning' : 'bg-success' ?> fs-6">
                                    <?= $jours_restants ?> jour(s) restant(s)
                                </span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center text-muted">
                            <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                            <p>Aucun abonnement actif</p>
                            <a href="#" class="btn btn-outline-primary btn-sm">Souscrire</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Dernières Progressions -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">📈 Dernières Progressions</h5>
                </div>
                <div class="card-body">
                    <?php if ($progressions): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($progressions as $progression): ?>
                                <div class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong><?= formatDate($progression['date']) ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <?= $progression['poids'] ? number_format($progression['poids'], 1) . ' kg' : 'Poids non renseigné' ?>
                                            <?php if ($progression['imc']): ?>
                                                • IMC: <?= number_format($progression['imc'], 1) ?>
                                            <?php endif; ?>
                                        </small>
                                        <?php if ($progression['commentaire']): ?>
                                            <br>
                                            <small class="text-muted"><?= htmlspecialchars($progression['commentaire']) ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="progression.php" class="btn btn-outline-info btn-sm">Voir tout</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucune progression enregistrée</p>
                        <div class="text-center">
                            <a href="progression.php" class="btn btn-outline-info btn-sm">Commencer le suivi</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Séances Récentes -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">💪 Dernières Séances</h5>
                </div>
                <div class="card-body">
                    <?php if ($seances): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($seances as $seance): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($seance['activite']) ?></strong>
                                            <br>
                                            <small class="text-muted">
                                                <?= formatDate($seance['date_seance']) ?> •
                                                <?= $seance['duree'] ?> min •
                                                <?= $seance['calories_brulees'] ?> kcal
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="seances.php" class="btn btn-outline-warning btn-sm">Voir toutes les séances</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucune séance enregistrée</p>
                        <div class="text-center">
                            <a href="seances.php" class="btn btn-outline-warning btn-sm">Ajouter une séance</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0">📢 Dernières Notifications</h5>
                </div>
                <div class="card-body">
                    <?php if ($notifications): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($notifications as $notification): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge 
                                                <?= $notification['type'] === 'Alerte' ? 'bg-danger' : ($notification['type'] === 'Rappel' ? 'bg-warning' : 'bg-info') ?> me-2">
                                                <?= $notification['type'] ?>
                                            </span>
                                            <?= htmlspecialchars($notification['message']) ?>
                                        </div>
                                        <small class="text-muted"><?= formatDateTime($notification['date_envoi']) ?></small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="text-center mt-3">
                            <a href="notifications.php" class="btn btn-outline-dark btn-sm">Voir toutes les notifications</a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center">Aucune notification</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>