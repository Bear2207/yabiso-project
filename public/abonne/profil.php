<?php
require_once '../../config/database.php';
require_once '../../config/functions.php';
checkLogin('abonne');

$utilisateur_id = $_SESSION['utilisateur_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $email = trim($_POST['email']);
    $telephone = trim($_POST['telephone']);

    // Mettre à jour l'utilisateur
    $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?");
    $stmt->execute([$nom, $email, $utilisateur_id]);

    // Mettre à jour l'abonné
    $stmt = $pdo->prepare("UPDATE abonnes SET telephone = ? WHERE utilisateur_id = ?");
    $stmt->execute([$telephone, $utilisateur_id]);

    // Mettre à jour la session
    $_SESSION['user_name'] = $nom;

    redirectWithMessage('profil.php', 'success', '✅ Votre profil a été mis à jour avec succès !');
}

// Récupération des informations
$stmt = $pdo->prepare("
    SELECT 
        u.nom,
        u.email,
        u.date_creation,
        a.telephone,
        a.date_inscription,
        ab.type AS abonnement_type,
        ab.date_fin
    FROM utilisateurs u
    JOIN abonnes a ON u.id = a.utilisateur_id
    LEFT JOIN abonnements ab ON a.id = ab.abonne_id AND ab.statut = 'Actif'
    WHERE u.id = ?
");
$stmt->execute([$utilisateur_id]);
$profil = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<?php include '../../includes/header.php'; ?>
<?php include '../../includes/navbar_abonne.php'; ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👤 Mon Profil</h2>
    </div>

    <?php displayFlashMessage(); ?>

    <div class="row">
        <!-- Informations personnelles -->
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Informations personnelles</h5>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Nom complet *</label>
                                <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($profil['nom']); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($profil['email']); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($profil['telephone']); ?>">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Informations compte -->
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Informations du compte</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <strong>Date d'inscription :</strong><br>
                        <span class="text-muted"><?= formatDate($profil['date_inscription']) ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Compte créé le :</strong><br>
                        <span class="text-muted"><?= formatDateTime($profil['date_creation']) ?></span>
                    </div>
                    <div class="mb-3">
                        <strong>Abonnement :</strong><br>
                        <?php if ($profil['abonnement_type']): ?>
                            <span class="badge bg-success"><?= $profil['abonnement_type'] ?></span><br>
                            <small class="text-muted">Valide jusqu'au <?= formatDate($profil['date_fin']) ?></small>
                        <?php else: ?>
                            <span class="badge bg-warning">Aucun abonnement actif</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Actions rapides -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Actions rapides</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="objectifs.php" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-bullseye"></i> Modifier mes objectifs
                        </a>
                        <a href="progression.php" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-chart-line"></i> Voir ma progression
                        </a>
                        <a href="notifications.php" class="btn btn-outline-warning btn-sm">
                            <i class="fas fa-bell"></i> Mes notifications
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../../includes/footer.php'; ?>