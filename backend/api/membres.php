<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Access-Control-Allow-Headers,Content-Type,Access-Control-Allow-Methods, Authorization, X-Requested-With');

include_once '../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'GET':
            // Récupérer tous les membres avec détails
            $query = "
                SELECT 
                    u.utilisateur_id,
                    u.nom,
                    u.prenom,
                    u.email,
                    u.role,
                    u.date_creation,
                    a.nom_abonnement,
                    a.prix,
                    p.statut as statut_paiement,
                    p.date_paiement,
                    p.mode_paiement
                FROM utilisateurs u
                LEFT JOIN paiements p ON u.utilisateur_id = p.utilisateur_id 
                    AND p.date_paiement = (SELECT MAX(date_paiement) FROM paiements WHERE utilisateur_id = u.utilisateur_id)
                LEFT JOIN abonnements a ON p.abonnement_id = a.abonnement_id
                WHERE u.role IN ('client', 'coach')
                ORDER BY u.date_creation DESC
            ";

            $stmt = $db->prepare($query);
            $stmt->execute();

            $membres = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $membres[] = $row;
            }

            echo json_encode([
                'success' => true,
                'data' => $membres,
                'total' => count($membres)
            ]);
            break;

        case 'POST':
            // Créer un nouveau membre
            $data = json_decode(file_get_contents("php://input"));

            if (!empty($data->nom) && !empty($data->prenom) && !empty($data->email)) {
                $query = "INSERT INTO utilisateurs (nom, prenom, email, role) VALUES (:nom, :prenom, :email, :role)";
                $stmt = $db->prepare($query);

                $stmt->bindParam(':nom', $data->nom);
                $stmt->bindParam(':prenom', $data->prenom);
                $stmt->bindParam(':email', $data->email);
                $role = $data->role ?? 'client';
                $stmt->bindParam(':role', $role);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Membre créé avec succès',
                        'id' => $db->lastInsertId()
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de la création'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Données manquantes'
                ]);
            }
            break;

        case 'PUT':
            // Mettre à jour un membre
            $data = json_decode(file_get_contents("php://input"));
            $id = $_GET['id'] ?? $data->utilisateur_id ?? null;

            if ($id) {
                $query = "UPDATE utilisateurs SET nom = :nom, prenom = :prenom, email = :email, role = :role WHERE utilisateur_id = :id";
                $stmt = $db->prepare($query);

                $stmt->bindParam(':id', $id);
                $stmt->bindParam(':nom', $data->nom);
                $stmt->bindParam(':prenom', $data->prenom);
                $stmt->bindParam(':email', $data->email);
                $stmt->bindParam(':role', $data->role);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Membre mis à jour avec succès'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de la mise à jour'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID manquant'
                ]);
            }
            break;

        case 'DELETE':
            // Supprimer un membre
            $id = $_GET['id'] ?? null;

            if ($id) {
                $query = "DELETE FROM utilisateurs WHERE utilisateur_id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);

                if ($stmt->execute()) {
                    echo json_encode([
                        'success' => true,
                        'message' => 'Membre supprimé avec succès'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Erreur lors de la suppression'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'ID manquant'
                ]);
            }
            break;
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erreur: ' . $e->getMessage()
    ]);
}
