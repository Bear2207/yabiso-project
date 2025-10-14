-- Création de la base de données
CREATE DATABASE IF NOT EXISTS yabiso_db;
USE yabiso_db;

-- Table des utilisateurs (admin, coach, client)
CREATE TABLE utilisateurs (
    utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin','coach','client') DEFAULT 'client',
    telephone VARCHAR(20),
    date_naissance DATE,
    adresse TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    statut ENUM('actif','inactif','suspendu') DEFAULT 'actif'
);

-- Table des abonnements
CREATE TABLE abonnements (
    abonnement_id INT AUTO_INCREMENT PRIMARY KEY,
    nom_abonnement VARCHAR(50) NOT NULL,
    prix DECIMAL(10, 2) NOT NULL,
    duree INT NOT NULL, -- en jours
    description TEXT,
    caracteristiques JSON, -- Stockage des features en JSON
    statut ENUM('actif','inactif') DEFAULT 'actif',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des abonnements actifs des utilisateurs
CREATE TABLE abonnements_utilisateurs (
    abonnement_utilisateur_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    abonnement_id INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    statut ENUM('actif','expire','annule','en_attente') DEFAULT 'en_attente',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    FOREIGN KEY (abonnement_id) REFERENCES abonnements(abonnement_id) ON DELETE CASCADE,
    INDEX idx_utilisateur_statut (utilisateur_id, statut),
    INDEX idx_date_fin (date_fin)
);

-- Table des paiements
CREATE TABLE paiements (
    paiement_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    abonnement_id INT NOT NULL,
    abonnement_utilisateur_id INT, -- Lien vers l'abonnement utilisateur
    montant DECIMAL(10, 2) NOT NULL,
    date_paiement DATETIME DEFAULT CURRENT_TIMESTAMP,
    mode_paiement ENUM('carte','espece','virement','mobile'),
    statut ENUM('paye','en_attente','annule','refuse') DEFAULT 'en_attente',
    reference_paiement VARCHAR(100), -- Référence unique du paiement
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    FOREIGN KEY (abonnement_id) REFERENCES abonnements(abonnement_id) ON DELETE CASCADE,
    FOREIGN KEY (abonnement_utilisateur_id) REFERENCES abonnements_utilisateurs(abonnement_utilisateur_id) ON DELETE SET NULL,
    INDEX idx_date_paiement (date_paiement),
    INDEX idx_statut (statut)
);

-- Table des séances (réservations par les clients)
CREATE TABLE seances (
    seance_id INT AUTO_INCREMENT PRIMARY KEY,
    coach_id INT NOT NULL,
    utilisateur_id INT NOT NULL,
    date_seance DATETIME NOT NULL,
    duree_seance INT DEFAULT 60, -- en minutes
    type_seance VARCHAR(50),
    statut ENUM('reservee','effectuee','annulee','absence') DEFAULT 'reservee',
    notes TEXT,
    FOREIGN KEY (coach_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    INDEX idx_date_seance (date_seance),
    INDEX idx_coach_date (coach_id, date_seance)
);

-- Table du suivi des progrès
CREATE TABLE progres (
    progres_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    date_mesure DATE NOT NULL,
    poids DECIMAL(5, 2),
    taille DECIMAL(5, 2),
    masse_musculaire DECIMAL(5, 2),
    masse_graisseuse DECIMAL(5, 2),
    tour_de_bras DECIMAL(5, 2),
    tour_de_taille DECIMAL(5, 2),
    tour_de_hanches DECIMAL(5, 2),
    notes TEXT,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    INDEX idx_utilisateur_date (utilisateur_id, date_mesure)
);

-- Table des notifications
CREATE TABLE notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type_notification ENUM('info','warning','success','danger') DEFAULT 'info',
    date_notification DATETIME DEFAULT CURRENT_TIMESTAMP,
    date_lecture DATETIME NULL,
    statut ENUM('non_lu','lu') DEFAULT 'non_lu',
    lien_action VARCHAR(255),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    INDEX idx_utilisateur_statut (utilisateur_id, statut),
    INDEX idx_date_notification (date_notification)
);

-- Table des logs d'activité
CREATE TABLE logs_activite (
    log_id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    action VARCHAR(100) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    date_action DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE,
    INDEX idx_utilisateur_date (utilisateur_id, date_action)
);

-- Insertion des utilisateurs
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe, role, telephone) VALUES 
    ('Loulou', 'Admin', 'yabisoelekiyabino@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+243970000001'),
    ('Kalela', 'Bearing', 'bearingkalela@live.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+243970000002'),
    ('Doe', 'John', 'john.doe@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000003'),
    ('Smith', 'Alice', 'alice.smith@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000004'),
    ('Johnson', 'Mike', 'mike.johnson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000005'),
    ('Brown', 'Sarah', 'sarah.brown@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000006'),
    ('Davis', 'Robert', 'robert.davis@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000007'),
    ('Wilson', 'Emma', 'emma.wilson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000008'),
    ('Taylor', 'James', 'james.taylor@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000009'),
    ('Anderson', 'Lisa', 'lisa.anderson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'client', '+243970000010'),
    ('Thomas', 'David', 'david.thomas@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000011'),
    ('Jackson', 'Maria', 'maria.jackson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000012'),
    ('White', 'Paul', 'paul.white@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000013'),
    ('Harris', 'Laura', 'laura.harris@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000014'),
    ('Martin', 'Kevin', 'kevin.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000015'),
    ('Thompson', 'Sophie', 'sophie.thompson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000016'),
    ('Garcia', 'Carlos', 'carlos.garcia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000017'),
    ('Martinez', 'Elena', 'elena.martinez@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000018'),
    ('Robinson', 'Daniel', 'daniel.robinson@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000019'),
    ('Clark', 'Michelle', 'michelle.clark@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'coach', '+243970000020');

-- Insertion des abonnements
INSERT INTO abonnements (nom_abonnement, prix, duree, description, caracteristiques) VALUES 
    ('Découverte 1 mois', 29.99, 30, 'Abonnement mensuel pour débutants', '["Accès illimité", "1 séance coaching", "Évaluation physique"]'),
    ('Forme 3 mois', 79.99, 90, 'Abonnement trimestriel pour rester en forme', '["Accès illimité", "4 séances coaching", "Suivi mensuel"]'),
    ('Performance 6 mois', 149.99, 180, 'Abonnement semestriel pour progresser', '["Accès illimité", "8 séances coaching", "Programme personnalisé"]'),
    ('Elite 1 an', 269.99, 365, 'Abonnement annuel pour les plus motivés', '["Accès illimité", "16 séances coaching", "Nutrition inclus"]'),
    ('Étudiant Mensuel', 24.99, 30, 'Abonnement mensuel spécial étudiants', '["Accès illimité", "Carte étudiante requise"]'),
    ('Couple 3 mois', 139.99, 90, 'Abonnement trimestriel pour deux personnes', '["2 personnes", "Accès illimité", "Séances duo"]'),
    ('Famille 1 an', 449.99, 365, 'Abonnement annuel pour toute la famille', '["4 personnes max", "Accès illimité", "Activités famille"]'),
    ('Senior Mensuel', 27.99, 30, 'Abonnement mensuel spécial seniors', '["Accès illimité", "Programme adapté", "+55 ans"]'),
    ('Intensif 1 mois', 49.99, 30, 'Programme intensif avec coach inclus', '["Accès illimité", "8 séances coaching", "Planning intensif"]'),
    ('Détox 2 mois', 89.99, 60, 'Programme remise en forme de 2 mois', '["Accès illimité", "6 séances coaching", "Programme détox"]'),
    ('Week-end', 19.99, 30, 'Accès uniquement le week-end', '["Samedi-Dimanche", "8h-20h"]'),
    ('Early Bird', 22.99, 30, 'Accès uniquement avant 10h', '["Lun-Ven 6h-10h", "Accès équipements"]'),
    ('Soirée', 23.99, 30, 'Accès uniquement après 17h', '["Lun-Ven 17h-22h", "Accès équipements"]'),
    ('Essai 1 semaine', 9.99, 7, 'Formule découverte d''une semaine', '["7 jours", "1 séance coaching"]'),
    ('Premium Gold', 399.99, 365, 'Formule premium tous services inclus', '["Accès illimité", "Coaching illimité", "Nutrition", "Massages"]'),
    ('Musculation Focus', 34.99, 30, 'Accès illimité zone musculation', '["Zone musculation", "1 séance coaching"]'),
    ('Cardio Passion', 31.99, 30, 'Accès illimité zone cardio', '["Zone cardio", "1 séance coaching"]'),
    ('Complet 2 mois', 69.99, 60, 'Formule complète 2 mois', '["Accès illimité", "4 séances coaching"]'),
    ('Business 3 mois', 99.99, 90, 'Formule adaptée aux professionnels', '["Accès 5h-23h", "3 séances coaching", "Parking inclus"]'),
    ('Summer Body', 59.99, 45, 'Programme spécial été de 45 jours', '["Accès illimité", "6 séances coaching", "Programme été"]');

-- Suite des insertions pour les autres tables...
-- [Le reste de vos insertions adapté pour MySQL]