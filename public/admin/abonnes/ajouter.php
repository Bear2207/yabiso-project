<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Données utilisateur
        $nom = trim($_POST['nom']);
        $email = trim($_POST['email']);
        $mot_de_passe = $_POST['mot_de_passe'];
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

        // Vérifier si l'email existe déjà
        if (emailExists($pdo, $email)) {
            $message = '<div class="alert alert-danger">❌ Cet email est déjà utilisé.</div>';
        } else {
            // Créer l'utilisateur
            $stmt = $pdo->prepare("INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES (?, ?, ?, 'abonne')");
            $stmt->execute([$nom, $email, md5($mot_de_passe)]); // MD5 pour compatibilité avec vos données existantes

            $utilisateur_id = $pdo->lastInsertId();

            // Créer l'abonné
            $stmt = $pdo->prepare("INSERT INTO abonnes (utilisateur_id, telephone, poids_actuel, poids_cible, tour_de_bras, tour_de_hanches, tour_de_fessier, frequence, objectif, objectif_duree) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$utilisateur_id, $telephone, $poids_actuel, $poids_cible, $tour_de_bras, $tour_de_hanches, $tour_de_fessier, $frequence, $objectif, $objectif_duree]);

            $abonne_id = $pdo->lastInsertId();

            // Créer un abonnement par défaut
            $date_debut = date('Y-m-d');
            $date_fin = date('Y-m-d', strtotime('+1 month'));

            $stmt = $pdo->prepare("INSERT INTO abonnements (abonne_id, type, date_debut, date_fin, montant, statut) VALUES (?, 'Mensuel', ?, ?, 30.00, 'Actif')");
            $stmt->execute([$abonne_id, $date_debut, $date_fin]);

            // Envoyer une notification de bienvenue
            sendNotification($pdo, $abonne_id, 'Information', 'Bienvenue au Reforme Center ! Votre compte a été créé avec succès.');

            $pdo->commit();

            $message = '<div class="alert alert-success">✅ Abonné ajouté avec succès !</div>';
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = '<div class="alert alert-danger">❌ Erreur lors de l\'ajout : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>👤 Ajouter un abonné</h2>
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
                        <input type="text" name="nom" class="form-control" placeholder="Nom complet" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Email *</label>
                        <input type="email" name="email" class="form-control" placeholder="email@exemple.com" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Mot de passe *</label>
                        <input type="password" name="mot_de_passe" class="form-control" placeholder="Mot de passe" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" placeholder="+243...">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Informations physiques</h5>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Poids actuel (kg)</label>
                        <input type="number" step="0.1" name="poids_actuel" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Poids cible (kg)</label>
                        <input type="number" step="0.1" name="poids_cible" class="form-control" placeholder="0.0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fréquence/semaine</label>
                        <select name="frequence" class="form-control">
                            <option value="1">1 fois</option>
                            <option value="2">2 fois</option>
                            <option value="3" selected>3 fois</option>
                            <option value="4">4 fois</option>
                            <option value="5">5 fois</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Tour de bras (cm)</label>
                        <input type="number" name="tour_de_bras" class="form-control" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de hanches (cm)</label>
                        <input type="number" name="tour_de_hanches" class="form-control" placeholder="0">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Tour de fessier (cm)</label>
                        <input type="number" name="tour_de_fessier" class="form-control" placeholder="0">
                    </div>
                </div>

                <hr class="my-4">

                <h5 class="mb-3 text-primary">Objectifs</h5>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Objectif principal</label>
                        <textarea name="objectif" class="form-control" placeholder="Ex: Perdre 10kg, gagner en masse musculaire..." rows="2"></textarea>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Durée objectif (semaines)</label>
                        <input type="number" name="objectif_duree" class="form-control" value="4" min="1">
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Enregistrer l'abonné
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once '../../../includes/footer.php'; ?>