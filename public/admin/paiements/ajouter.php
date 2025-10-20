<?php
require_once '../../../config/database.php';
require_once '../../../config/functions.php';
checkLogin('admin');
include_once '../../../includes/header.php';
include_once '../../../includes/navbar_admin.php';

// Récupérer la liste des abonnés avec leurs abonnements actifs
$abonnes_actifs = $pdo->query("
    SELECT 
        a.id AS abonne_id,
        u.nom AS abonne_nom,
        ab.id AS abonnement_id,
        ab.type AS abonnement_type,
        ab.montant AS abonnement_montant
    FROM abonnes a
    JOIN utilisateurs u ON a.utilisateur_id = u.id
    JOIN abonnements ab ON a.id = ab.abonne_id
    WHERE ab.statut = 'Actif'
    ORDER BY u.nom
")->fetchAll(PDO::FETCH_ASSOC);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $abonne_id = $_POST['abonne_id'];
        $abonnement_id = $_POST['abonnement_id'];
        $montant = $_POST['montant'];
        $mode_paiement = $_POST['mode_paiement'];
        $date_paiement = $_POST['date_paiement'];

        // Vérifier si l'abonnement appartient bien à l'abonné
        $stmt = $pdo->prepare("SELECT id FROM abonnements WHERE id = ? AND abonne_id = ?");
        $stmt->execute([$abonnement_id, $abonne_id]);

        if (!$stmt->fetch()) {
            $message = '<div class="alert alert-danger">❌ Erreur : Cet abonnement n\'appartient pas à l\'abonné sélectionné.</div>';
        } else {
            // Enregistrer le paiement
            $stmt = $pdo->prepare("INSERT INTO paiements (abonne_id, abonnement_id, montant, mode_paiement, date_paiement) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$abonne_id, $abonnement_id, $montant, $mode_paiement, $date_paiement]);

            $message = '<div class="alert alert-success">✅ Paiement enregistré avec succès !</div>';
        }
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">❌ Erreur : ' . $e->getMessage() . '</div>';
    }
}
?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>💰 Enregistrer un paiement</h2>
        <a href="liste.php" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Retour à la liste
        </a>
    </div>

    <?= $message ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form method="POST">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Abonné *</label>
                        <select name="abonne_id" id="abonne_select" class="form-control" required onchange="updateAbonnements()">
                            <option value="">Sélectionner un abonné</option>
                            <?php
                            $abonnes_grouped = [];
                            foreach ($abonnes_actifs as $abonne) {
                                $abonnes_grouped[$abonne['abonne_id']]['nom'] = $abonne['abonne_nom'];
                                $abonnes_grouped[$abonne['abonne_id']]['abonnements'][] = [
                                    'id' => $abonne['abonnement_id'],
                                    'type' => $abonne['abonnement_type'],
                                    'montant' => $abonne['abonnement_montant']
                                ];
                            }

                            foreach ($abonnes_grouped as $abonne_id => $data): ?>
                                <option value="<?= $abonne_id ?>" data-abonnements='<?= json_encode($data['abonnements']) ?>'>
                                    <?= htmlspecialchars($data['nom']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Abonnement *</label>
                        <select name="abonnement_id" id="abonnement_select" class="form-control" required>
                            <option value="">Sélectionner d'abord un abonné</option>
                        </select>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Montant ($) *</label>
                        <input type="number" step="0.01" name="montant" id="montant_input" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mode de paiement *</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="Cash">Cash</option>
                            <option value="Carte">Carte</option>
                            <option value="Mobile Money">Mobile Money</option>
                            <option value="Banque">Banque</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Date de paiement *</label>
                        <input type="date" name="date_paiement" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-dark btn-lg">
                        <i class="fas fa-save"></i> Enregistrer le paiement
                    </button>
                    <a href="liste.php" class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateAbonnements() {
        const abonneSelect = document.getElementById('abonne_select');
        const abonnementSelect = document.getElementById('abonnement_select');
        const montantInput = document.getElementById('montant_input');

        // Vider la liste des abonnements
        abonnementSelect.innerHTML = '<option value="">Sélectionner un abonnement</option>';
        montantInput.value = '';

        const selectedOption = abonneSelect.options[abonneSelect.selectedIndex];
        if (selectedOption.value) {
            const abonnements = JSON.parse(selectedOption.getAttribute('data-abonnements'));

            abonnements.forEach(abonnement => {
                const option = document.createElement('option');
                option.value = abonnement.id;
                option.textContent = `${abonnement.type} - ${abonnement.montant}$`;
                option.setAttribute('data-montant', abonnement.montant);
                abonnementSelect.appendChild(option);
            });
        }
    }

    // Mettre à jour le montant quand un abonnement est sélectionné
    document.getElementById('abonnement_select').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            document.getElementById('montant_input').value = selectedOption.getAttribute('data-montant');
        }
    });
</script>

<?php include_once '../../../includes/footer.php'; ?>