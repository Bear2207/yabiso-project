<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

if (!isset($_GET['id'])) {
    header('Location: liste_progressions.php');
    exit;
}

$progression_id = $_GET['id'];

// Récupérer la progression
$stmt = $pdo->prepare("
    SELECT 
        p.*,
        u.nom AS abonne_nom,
        a.poids_actuel,
        a.poids_cible
    FROM progressions p
    JOIN abonnes a ON p.abonne_id = a.id
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    WHERE p.id = ?
");
$stmt->execute([$progression_id]);
$progression = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$progression) {
    header('Location: liste_progressions.php');
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $date = $_POST['date'];
        $poids = $_POST['poids'] ?: null;
        $imc = $_POST['imc'] ?: null;
        $commentaire = trim($_POST['commentaire']);

        // Mettre à jour la progression
        $stmt = $pdo->prepare("UPDATE progressions SET date = ?, poids = ?, imc = ?, commentaire = ? WHERE id = ?");
        $stmt->execute([$date, $poids, $imc, $commentaire, $progression_id]);

        // Mettre à jour le poids actuel de l'abonné si c'est la progression la plus récente
        if ($poids) {
            $stmt = $pdo->prepare("
                UPDATE abonnes SET poids_actuel = ? 
                WHERE id = ? AND (
                    SELECT MAX(date) FROM progressions WHERE abonne_id = ?
                ) = ?
            ");
            $stmt->execute([$poids, $progression['abonne_id'], $progression['abonne_id'], $date]);
        }

        $message = '<div class="alert alert-success">✅ Progression modifiée avec succès !</div>';

        // Mettre à jour les données affichées
        $progression = array_merge($progression, [
            'date' => $date,
            'poids' => $poids,
            'imc' => $imc,
            'commentaire' => $commentaire
        ]);
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>✏️ Modifier la progression</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-6">
                    <strong>Abonné :</strong> <?= htmlspecialchars($progression['abonne_nom']) ?>
                </div>
                <div class="col-md-6">
                    <strong>Objectif :</strong>
                    <?= $progression['poids_actuel'] > 0 ? number_format($progression['poids_actuel'], 1) . 'kg' : 'Non défini' ?>
                    →
                    <?= $progression['poids_cible'] > 0 ? number_format($progression['poids_cible'], 1) . 'kg' : 'Non défini' ?>
                </div>
            </div>

            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Date *</label>
                        <input type="date" name="date" class="form-control" value="<?= $progression['date'] ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Poids (kg)</label>
                        <input type="number" step="0.1" name="poids" class="form-control" value="<?= $progression['poids'] ?>" placeholder="0.0">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">IMC</label>
                        <input type="number" step="0.1" name="imc" class="form-control" value="<?= $progression['imc'] ?>" placeholder="0.0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Classification IMC</label>
                        <input type="text" class="form-control"
                            value="<?php
                                    if ($progression['imc']) {
                                        if ($progression['imc'] < 18.5) echo 'Insuffisance pondérale';
                                        elseif ($progression['imc'] < 25) echo 'Poids normal';
                                        elseif ($progression['imc'] < 30) echo 'Surpoids';
                                        else echo 'Obésité';
                                    }
                                    ?>"
                            readonly
                            style="background-color: <?php
                                                        if ($progression['imc']) {
                                                            if ($progression['imc'] < 18.5) echo '#17a2b8';
                                                            elseif ($progression['imc'] < 25) echo '#28a745';
                                                            elseif ($progression['imc'] < 30) echo '#ffc107';
                                                            else echo '#dc3545';
                                                        } else {
                                                            echo '#e9ecef';
                                                        }
                                                        ?>; color: white;">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Commentaire</label>
                    <textarea name="commentaire" class="form-control" rows="3"><?= htmlspecialchars($progression['commentaire']) ?></textarea>
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