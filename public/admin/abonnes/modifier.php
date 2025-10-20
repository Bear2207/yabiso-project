<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

if (!isset($_GET['id'])) {
    header('Location: liste.php');
    exit;
}

$abonne_id = $_GET['id'];

// Récupérer les informations de l'abonné
$stmt = $pdo->prepare("
    SELECT 
        a.id,
        u.nom,
        u.email,
        a.telephone,
        a.poids_actuel,
        a.poids_cible,
        a.tour_de_bras,
        a.tour_de_hanches,
        a.tour_de_fessier,
        a.frequence,
        a.objectif,
        a.objectif_duree
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE a.id = ?
");
$stmt->execute([$abonne_id]);
$abonne = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$abonne) {
    header('Location: liste.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Données utilisateur
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $telephone = trim($_POST['telephone']);

        // Données abonné
        $poids_actuel = $_POST['poids_actuel'] ?: 0;
        $poids_cible = $_POST['poids_cible'] ?: 0;
        $tour_de_bras = $_POST['tour_de_bras'] ?: 0;
        $tour_de_hanches = $_POST['tour_de_hanches'] ?: 0;
        $tour_de_fessier = $_POST['tour_de_fessier'] ?: 0;
        $frequence = $_POST['frequence'] ?: 3;
        $objectif = trim($_POST['objectif']);
        $objectif_duree = $_POST['objectif_duree'] ?: 4;

        // Vérifier si l'email existe déjà pour un autre utilisateur
        $stmt = $pdo->prepare("SELECT utilisateur_id FROM abonnes WHERE id = ?");
        $stmt->execute([$abonne_id]);
        $current_abonne = $stmt->fetch(PDO::FETCH_ASSOC);
        $current_user_id = $current_abonne['utilisateur_id'];

        if (emailExists($pdo, $email, $current_user_id)) {
            $message = '<div class="alert alert-danger">❌ Cet email est déjà utilisé par un autre utilisateur.</div>';
        } else {
            // Mettre à jour l'utilisateur
            $stmt = $pdo->prepare("UPDATE utilisateurs SET nom = ?, email = ? WHERE id = ?");
            $stmt->execute([$nom, $email, $current_user_id]);

            // Mettre à jour l'abonné
            $stmt = $pdo->prepare("UPDATE abonnes SET telephone = ?, poids_actuel = ?, poids_cible = ?, tour_de_bras = ?, tour_de_hanches = ?, tour_de_fessier = ?, frequence = ?, objectif = ?, objectif_duree = ? WHERE id = ?");
            $stmt->execute([$telephone, $poids_actuel, $poids_cible, $tour_de_bras, $tour_de_hanches, $tour_de_fessier, $frequence, $objectif, $objectif_duree, $abonne_id]);

            // Gestion du mot de passe si fourni
            if (!empty($_POST['mot_de_passe'])) {
                $mot_de_passe = md5($_POST['mot_de_passe']); // MD5 pour compatibilité
                $stmt = $pdo->prepare("UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?");
                $stmt->execute([$mot_de_passe, $current_user_id]);
            }

            $message = '<div class="alert alert-success">✅ Abonné modifié avec succès !</div>';

            // Mettre à jour les données affichées
            $abonne = array_merge($abonne, [
                'nom' => $nom,
                'email' => $email,
                'telephone' => $telephone,
                'poids_actuel' => $poids_actuel,
                'poids_cible' => $poids_cible,
                'tour_de_bras' => $tour_de_bras,
                'tour_de_hanches' => $tour_de_hanches,
                'tour_de_fessier' => $tour_de_fessier,
                'frequence' => $frequence,
                'objectif' => $objectif,
                'objectif_duree' => $objectif_duree
            ]);
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur lors de la modification : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Modifier l'abonné</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <h5 class="mb-3 text-primary">Informations personnelles</h5>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nom complet *</label>
                        <input type="text" name="nom" class="form-control" value="<?= htmlspecialchars($abonne['nom']) ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($abonne['email']) ?>" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Nouveau mot de passe</label>
                        <input type="password" name="mot_de_passe" class="form-control" placeholder="Laisser vide pour ne pas changer">
                        <small class="text-muted">Laisser vide pour conserver le mot de passe actuel</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="<?= htmlspecialchars($abonne['telephone']) ?>" placeholder="+243...">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Informations physiques</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Poids actuel (kg)</label>
                        <input type="number" step="0.1" name="poids_actuel" class="form-control" value="<?= $abonne['poids_actuel'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Poids cible (kg)</label>
                        <input type="number" step="0.1" name="poids_cible" class="form-control" value="<?= $abonne['poids_cible'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fréquence/semaine</label>
                        <select name="frequence" class="form-control">
                            <option value="1" <?= $abonne['frequence'] == 1 ? 'selected' : '' ?>>1 fois</option>
                            <option value="2" <?= $abonne['frequence'] == 2 ? 'selected' : '' ?>>2 fois</option>
                            <option value="3" <?= $abonne['frequence'] == 3 ? 'selected' : '' ?>>3 fois</option>
                            <option value="4" <?= $abonne['frequence'] == 4 ? 'selected' : '' ?>>4 fois</option>
                            <option value="5" <?= $abonne['frequence'] == 5 ? 'selected' : '' ?>>5 fois</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tour de bras (cm)</label>
                        <input type="number" name="tour_de_bras" class="form-control" value="<?= $abonne['tour_de_bras'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de hanches (cm)</label>
                        <input type="number" name="tour_de_hanches" class="form-control" value="<?= $abonne['tour_de_hanches'] ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de fessier (cm)</label>
                        <input type="number" name="tour_de_fessier" class="form-control" value="<?= $abonne['tour_de_fessier'] ?>">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Objectifs</h5>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Objectif principal</label>
                        <textarea name="objectif" class="form-control" rows="2"><?= htmlspecialchars($abonne['objectif']) ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée objectif (semaines)</label>
                        <input type="number" name="objectif_duree" class="form-control" value="<?= $abonne['objectif_duree'] ?>" min="1">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>