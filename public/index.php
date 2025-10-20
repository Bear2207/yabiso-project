<?php
require_once '../config/database.php';
session_start();

// Redirection si déjà connecté
if (isset($_SESSION['utilisateur_id']) && isset($_SESSION['role'])) {
    if ($_SESSION['role'] === 'admin') {
        header('Location: admin/dashboard.php');
        exit;
    } elseif ($_SESSION['role'] === 'abonne') {
        header('Location: abonne/dashboard.php');
        exit;
    }
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Vérification utilisateur
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($utilisateur) {
        // Vérification du mot de passe (MD5 pour les données d'exemple)
        if (md5($password) === $utilisateur['mot_de_passe'] || password_verify($password, $utilisateur['mot_de_passe'])) {
            $_SESSION['utilisateur_id'] = $utilisateur['id'];
            $_SESSION['role'] = $utilisateur['role'];

            // Redirection selon le rôle
            if ($utilisateur['role'] === 'admin') {
                header('Location: admin/dashboard.php');
            } else {
                header('Location: abonne/dashboard.php');
            }
            exit;
        }
    }

    $message = "❌ Email ou mot de passe incorrect.";
}
?>

<?php include_once '../includes/header.php'; ?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg">
                <div class="card-header text-center bg-dark text-white">
                    <h3>Connexion - Reforme Center</h3>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-danger"><?= $message ?></div>
                    <?php endif; ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label>Email :</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label>Mot de passe :</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button class="btn btn-dark w-100" type="submit">Se connecter</button>
                    </form>

                    <!-- Informations de test -->
                    <div class="mt-3 p-3 bg-light rounded">
                        <small class="text-muted">
                            <strong>Comptes de test :</strong><br>
                            Admin: admin@reforme-center.com / admin123<br>
                            Abonné: jean@rc.com / pass123
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once '../includes/footer.php'; ?>