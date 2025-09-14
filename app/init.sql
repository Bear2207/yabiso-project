-- Table des utilisateurs (admin, coach, client)
CREATE TABLE utilisateurs (
    utilisateur_id SERIAL PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role VARCHAR(20) CHECK (role IN ('admin', 'coach', 'client')) DEFAULT 'client',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des utilisateurs (20)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role) VALUES
('Dupont', 'Jean', 'jean.dupont@email.com', 'motdepasse1', 'client'),
('Martin', 'Marie', 'marie.martin@email.com', 'motdepasse2', 'client'),
('Bernard', 'Pierre', 'pierre.bernard@email.com', 'motdepasse3', 'client'),
('Dubois', 'Sophie', 'sophie.dubois@email.com', 'motdepasse4', 'client'),
('Moreau', 'Luc', 'luc.moreau@email.com', 'motdepasse5', 'client'),
('Laurent', 'Julie', 'julie.laurent@email.com', 'motdepasse6', 'client'),
('Simon', 'Thomas', 'thomas.simon@email.com', 'motdepasse7', 'client'),
('Michel', 'Camille', 'camille.michel@email.com', 'motdepasse8', 'client'),
('Lefebvre', 'Antoine', 'antoine.lefebvre@email.com', 'motdepasse9', 'client'),
('Leroy', 'Émilie', 'emilie.leroy@email.com', 'motdepasse10', 'client'),
('Roux', 'David', 'david.roux@email.com', 'motdepasse11', 'client'),
('Garcia', 'Laura', 'laura.garcia@email.com', 'motdepasse12', 'client'),
('Durand', 'Nicolas', 'nicolas.durand@email.com', 'motdepasse13', 'client'),
('Lambert', 'Céline', 'celine.lambert@email.com', 'motdepasse14', 'client'),
('Bonnet', 'Stéphane', 'stephane.bonnet@email.com', 'motdepasse15', 'client'),
('François', 'Isabelle', 'isabelle.francois@email.com', 'motdepasse16', 'client'),
('Martinez', 'Philippe', 'philippe.martinez@email.com', 'motdepasse17', 'coach'),
('Petit', 'Catherine', 'catherine.petit@email.com', 'motdepasse18', 'coach'),
('Robert', 'Michel', 'michel.robert@email.com', 'motdepasse19', 'coach'),
('Richard', 'Admin', 'admin.richard@email.com', 'adminpass', 'admin');

-- Table des abonnements
CREATE TABLE abonnements (
    abonnement_id SERIAL PRIMARY KEY,
    nom_abonnement VARCHAR(50) NOT NULL,
    prix NUMERIC(10, 2) NOT NULL,
    duree INT NOT NULL, -- en jours
    description TEXT
);

-- Insertion des abonnements (20)
INSERT INTO abonnements (nom_abonnement, prix, duree, description) VALUES
('Découverte 1 mois', 29.99, 30, 'Abonnement mensuel pour débutants'),
('Forme 3 mois', 79.99, 90, 'Abonnement trimestriel pour rester en forme'),
('Performance 6 mois', 149.99, 180, 'Abonnement semestriel pour progresser'),
('Elite 1 an', 269.99, 365, 'Abonnement annuel pour les plus motivés'),
('Étudiant Mensuel', 24.99, 30, 'Abonnement mensuel spécial étudiants'),
('Couple 3 mois', 139.99, 90, 'Abonnement trimestriel pour deux personnes'),
('Famille 1 an', 449.99, 365, 'Abonnement annuel pour toute la famille'),
('Senior Mensuel', 27.99, 30, 'Abonnement mensuel spécial seniors'),
('Intensif 1 mois', 49.99, 30, 'Programme intensif avec coach inclus'),
('Détox 2 mois', 89.99, 60, 'Programme remise en forme de 2 mois'),
('Week-end', 19.99, 30, 'Accès uniquement le week-end'),
('Early Bird', 22.99, 30, 'Accès uniquement avant 10h'),
('Soirée', 23.99, 30, 'Accès uniquement après 17h'),
('Essai 1 semaine', 9.99, 7, 'Formule découverte d''une semaine'),
('Premium Gold', 399.99, 365, 'Formule premium tous services inclus'),
('Musculation Focus', 34.99, 30, 'Accès illimité zone musculation'),
('Cardio Passion', 31.99, 30, 'Accès illimité zone cardio'),
('Complet 2 mois', 69.99, 60, 'Formule complète 2 mois'),
('Business 3 mois', 99.99, 90, 'Formule adaptée aux professionnels'),
('Summer Body', 59.99, 45, 'Programme spécial été de 45 jours');

-- Table des paiements
CREATE TABLE paiements (
    paiement_id SERIAL PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    abonnement_id INT NOT NULL,
    montant NUMERIC(10, 2) NOT NULL,
    date_paiement DATE DEFAULT CURRENT_DATE,
    mode_paiement VARCHAR(20) CHECK (mode_paiement IN ('carte', 'espece', 'virement')),
    statut VARCHAR(20) CHECK (statut IN ('payé', 'en attente', 'annulé')) DEFAULT 'en attente',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    FOREIGN KEY (abonnement_id) REFERENCES abonnements(abonnement_id) ON DELETE CASCADE
);

-- Insertion des paiements (20)
INSERT INTO paiements (utilisateur_id, abonnement_id, montant, date_paiement, mode_paiement, statut) VALUES
(1, 1, 29.99, '2024-01-15', 'carte', 'payé'),
(2, 2, 79.99, '2024-01-16', 'virement', 'payé'),
(3, 3, 149.99, '2024-01-17', 'carte', 'payé'),
(4, 4, 269.99, '2024-01-18', 'espece', 'payé'),
(5, 5, 24.99, '2024-01-19', 'carte', 'payé'),
(6, 6, 139.99, '2024-01-20', 'virement', 'en attente'),
(7, 7, 449.99, '2024-01-21', 'carte', 'payé'),
(8, 8, 27.99, '2024-01-22', 'espece', 'annulé'),
(9, 9, 49.99, '2024-01-23', 'carte', 'payé'),
(10, 10, 89.99, '2024-01-24', 'virement', 'payé'),
(11, 11, 19.99, '2024-01-25', 'carte', 'payé'),
(12, 12, 22.99, '2024-01-26', 'carte', 'payé'),
(13, 13, 23.99, '2024-01-27', 'espece', 'payé'),
(14, 14, 9.99, '2024-01-28', 'carte', 'payé'),
(15, 15, 399.99, '2024-01-29', 'virement', 'en attente'),
(16, 16, 34.99, '2024-01-30', 'carte', 'payé'),
(17, 17, 31.99, '2024-01-31', 'carte', 'payé'),
(18, 18, 69.99, '2024-02-01', 'virement', 'payé'),
(19, 19, 99.99, '2024-02-02', 'carte', 'annulé'),
(1, 20, 59.99, '2024-02-03', 'carte', 'payé');

-- Table des séances (réservations par les clients)
CREATE TABLE seances (
    seance_id SERIAL PRIMARY KEY,
    coach_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_seance TIMESTAMP NOT NULL,
    type_seance VARCHAR(50),
    statut VARCHAR(20) CHECK (statut IN ('réservée', 'effectuée', 'annulée')) DEFAULT 'réservée',
    FOREIGN KEY (coach_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
);

INSERT INTO seances (coach_id, utilisateur_id, date_seance, type_seance, statut) VALUES
(17, 1, '2024-02-05 09:00:00', 'Musculation', 'réservée'),
(18, 2, '2024-02-05 10:30:00', 'Cardio', 'effectuée'),
(19, 3, '2024-02-05 14:00:00', 'Yoga', 'réservée'),
(17, 4, '2024-02-06 11:00:00', 'CrossFit', 'annulée'),
(18, 5, '2024-02-06 16:00:00', 'Pilates', 'effectuée'),
(19, 6, '2024-02-07 09:30:00', 'Stretching', 'réservée'),
(17, 7, '2024-02-07 15:00:00', 'Boxe', 'effectuée'),
(18, 8, '2024-02-08 10:00:00', 'Musculation', 'réservée'),
(19, 9, '2024-02-08 17:00:00', 'Cardio', 'annulée'),
(17, 10, '2024-02-09 08:30:00', 'Yoga', 'réservée'),
(18, 11, '2024-02-09 12:00:00', 'CrossFit', 'effectuée'),
(19, 12, '2024-02-10 14:30:00', 'Pilates', 'réservée'),
(17, 13, '2024-02-10 16:00:00', 'Stretching', 'effectuée'),
(18, 14, '2024-02-12 09:00:00', 'Boxe', 'réservée'),
(19, 15, '2024-02-12 11:30:00', 'Musculation', 'annulée'),
(17, 16, '2024-02-13 13:00:00', 'Cardio', 'réservée'),
(18, 1, '2024-02-13 15:30:00', 'Yoga', 'effectuée'),
(19, 2, '2024-02-14 10:00:00', 'CrossFit', 'réservée'),
(17, 3, '2024-02-14 17:00:00', 'Pilates', 'effectuée'),
(18, 4, '2024-02-15 09:30:00', 'Stretching', 'réservée');

-- Table du suivi des progrès
CREATE TABLE progres (
    progres_id SERIAL PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_mesure DATE NOT NULL,
    poids NUMERIC(5, 2),
    taille NUMERIC(5, 2),
    masse_musculaire NUMERIC(5, 2),
    masse_graisseuse NUMERIC(5, 2),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
);

-- Insertion des progrès (20)
INSERT INTO progres (utilisateur_id, date_mesure, poids, taille, masse_musculaire, masse_graisseuse) VALUES
(1, '2024-01-15', 75.5, 1.80, 35.2, 18.5),
(1, '2024-02-15', 74.2, 1.80, 36.1, 17.8),
(2, '2024-01-16', 62.3, 1.68, 28.7, 22.1),
(3, '2024-01-17', 85.0, 1.85, 40.3, 20.5),
(4, '2024-01-18', 58.7, 1.65, 25.8, 24.3),
(5, '2024-01-19', 70.1, 1.75, 32.4, 19.7),
(6, '2024-01-20', 66.8, 1.72, 30.1, 21.4),
(7, '2024-01-21', 90.5, 1.88, 42.6, 23.8),
(8, '2024-01-22', 63.5, 1.70, 29.3, 22.6),
(9, '2024-01-23', 77.2, 1.82, 36.8, 19.2),
(10, '2024-01-24', 69.4, 1.74, 31.9, 20.8),
(11, '2024-01-25', 72.6, 1.78, 33.7, 20.1),
(12, '2024-01-26', 61.9, 1.67, 28.4, 23.2),
(13, '2024-01-27', 83.7, 1.84, 39.5, 21.7),
(14, '2024-01-28', 67.3, 1.73, 30.8, 21.0),
(15, '2024-01-29', 76.8, 1.81, 36.3, 19.5),
(16, '2024-01-30', 64.2, 1.69, 29.7, 22.3),
(17, '2024-01-31', 88.4, 1.86, 41.2, 22.9),
(18, '2024-02-01', 71.5, 1.76, 33.1, 20.4),
(19, '2024-02-02', 65.9, 1.71, 30.3, 21.8);

-- Table des notifications (ex: abonnement expiré)
CREATE TABLE notifications (
    notification_id SERIAL PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    message TEXT NOT NULL,
    date_notification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(20) CHECK (statut IN ('non lu', 'lu')) DEFAULT 'non lu',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
);

-- Insertion des notifications (20)
INSERT INTO notifications (utilisateur_id, message, date_notification, statut) VALUES
(1, 'Votre abonnement expire dans 7 jours', '2024-02-08 09:00:00', 'non lu'),
(2, 'Séance avec Coach Martinez confirmée', '2024-02-04 14:30:00', 'lu'),
(3, 'Paiement reçu avec succès', '2024-01-17 16:45:00', 'lu'),
(4, 'Votre séance de demain a été annulée', '2024-02-05 11:20:00', 'non lu'),
(5, 'Nouveau programme disponible', '2024-02-01 10:15:00', 'lu'),
(6, 'Paiement en attente de validation', '2024-01-21 09:30:00', 'non lu'),
(7, 'Félicitations pour votre progression !', '2024-02-15 08:00:00', 'non lu'),
(8, 'Votre abonnement a été annulé', '2024-01-23 15:40:00', 'lu'),
(9, 'Rappel: Séance aujourd''hui à 17h', '2024-02-08 12:00:00', 'non lu'),
(10, 'Promotion spéciale sur les abonnements', '2024-02-03 10:30:00', 'lu'),
(11, 'Votre coach a ajouté un commentaire', '2024-02-10 17:25:00', 'non lu'),
(12, 'Maintenance programmée samedi', '2024-02-07 14:00:00', 'lu'),
(13, 'Questionnaire de satisfaction', '2024-02-14 16:20:00', 'non lu'),
(14, 'Votre carte va expirer', '2024-02-06 11:45:00', 'non lu'),
(15, 'Paiement refusé, merci de mettre à jour', '2024-01-30 13:15:00', 'lu'),
(16, 'Nouveau coach disponible', '2024-02-02 09:50:00', 'lu'),
(17, 'Vos disponibilités ont été mises à jour', '2024-01-25 15:30:00', 'lu'),
(18, 'Alert: Charge maximale atteinte', '2024-02-11 10:05:00', 'non lu'),
(19, 'Bienvenue dans notre salle de sport !', '2024-01-20 08:45:00', 'lu'),
(1, 'Rappel: Séance demain à 9h', '2024-02-04 18:00:00', 'lu');
