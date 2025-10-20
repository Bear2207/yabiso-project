-- ===================================================
-- Base de données : reforme_center_v2
-- Auteur : Bearing Kalela & ChatGPT (mode coach motivé 🏋️‍♂️)
-- Description : Gestion complète d’un centre de réforme physique
-- ===================================================

CREATE DATABASE IF NOT EXISTS reforme_center_v2 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE reforme_center_v2;

-- ===================================================
-- TABLE : utilisateurs (admin + abonnés)
-- ===================================================
CREATE TABLE utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('admin', 'abonne') NOT NULL DEFAULT 'abonne',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===================================================
-- TABLE : abonnés
-- ===================================================
CREATE TABLE abonnes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    adresse VARCHAR(255),
    poids_actuel DECIMAL(5,2) DEFAULT 0,
    poids_cible DECIMAL(5,2) DEFAULT 0,
    tour_de_bras INT DEFAULT 0, -- en cm
    tour_de_hanches INT DEFAULT 0, -- en cm
    tour_de_fessier INT DEFAULT 0, -- en cm
    frequence INT DEFAULT 3,
    objectif VARCHAR(255),
    objectif_duree INT DEFAULT 4, -- en semaines
    telephone VARCHAR(20),
    date_inscription DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
);

-- ===================================================
-- TABLE : abonnements
-- ===================================================
CREATE TABLE abonnements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    abonne_id INT NOT NULL,
    type ENUM('Mensuel', 'Trimestriel', 'Annuel') DEFAULT 'Mensuel',
    date_debut DATE NOT NULL,
    date_fin DATE NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    statut ENUM('Actif', 'Expiré') DEFAULT 'Actif',
    FOREIGN KEY (abonne_id) REFERENCES abonnes(id) ON DELETE CASCADE
);

-- ===================================================
-- TABLE : paiements
-- ===================================================
CREATE TABLE paiements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    abonne_id INT NOT NULL,
    abonnement_id INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    mode_paiement ENUM('Cash', 'Carte', 'Mobile Money', 'Banque') DEFAULT 'Cash',
    date_paiement DATE DEFAULT CURRENT_DATE,
    FOREIGN KEY (abonne_id) REFERENCES abonnes(id) ON DELETE CASCADE,
    FOREIGN KEY (abonnement_id) REFERENCES abonnements(id) ON DELETE CASCADE
);

-- ===================================================
-- TABLE : progressions
-- ===================================================
CREATE TABLE progressions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    abonne_id INT NOT NULL,
    date DATE NOT NULL,
    poids DECIMAL(5,2),
    imc DECIMAL(4,2),
    commentaire TEXT,
    FOREIGN KEY (abonne_id) REFERENCES abonnes(id) ON DELETE CASCADE
);

-- ===================================================
-- TABLE : notifications
-- ===================================================
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    abonne_id INT NULL,
    type ENUM('Information', 'Alerte', 'Rappel') DEFAULT 'Information',
    message TEXT NOT NULL,
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    lu BOOLEAN DEFAULT FALSE,
    FOREIGN KEY (abonne_id) REFERENCES abonnes(id) ON DELETE CASCADE
);

-- ===================================================
-- TABLE : séances d’entraînement
-- ===================================================
CREATE TABLE seances (
    id INT AUTO_INCREMENT PRIMARY KEY,
    abonne_id INT NOT NULL,
    date_seance DATE NOT NULL,
    activite VARCHAR(100),
    duree INT, -- minutes
    calories_brulees INT,
    FOREIGN KEY (abonne_id) REFERENCES abonnes(id) ON DELETE CASCADE
);

-- ===================================================
-- VUE : Abonnés actifs avec leur progression récente
-- ===================================================
CREATE OR REPLACE VIEW vue_abonnes_actifs AS
SELECT 
    u.nom,
    a.telephone,
    ab.type AS abonnement_type,
    ab.date_fin,
    pr.poids,
    pr.imc,
    pr.commentaire
FROM utilisateurs u
JOIN abonnes a ON u.id = a.utilisateur_id
JOIN abonnements ab ON ab.abonne_id = a.id
LEFT JOIN progressions pr ON pr.abonne_id = a.id
WHERE ab.statut = 'Actif';

-- ===================================================
-- DONNÉES D’EXEMPLE (pour test rapide)
-- ===================================================
INSERT INTO utilisateurs (nom, email, mot_de_passe, role) VALUES
('Admin Principal', 'admin@reforme-center.com', MD5('admin123'), 'admin'),
('Jean Kabila', 'jean@rc.com', MD5('pass123'), 'abonne');

INSERT INTO abonnes (utilisateur_id, poids_actuel, poids_cible, frequence, objectif_duree, telephone, objectif)
VALUES (2, 85.5, 75.0, 3, 8, '+243810000001', 'Perdre du poids et améliorer l’endurance');

INSERT INTO abonnements (abonne_id, type, date_debut, date_fin, montant)
VALUES (1, 'Mensuel', '2025-10-01', '2025-10-31', 30.00);

INSERT INTO paiements (abonne_id, abonnement_id, montant, mode_paiement)
VALUES (1, 1, 30.00, 'Mobile Money');

INSERT INTO progressions (abonne_id, date, poids, imc, commentaire)
VALUES 
(1, '2025-10-10', 84.0, 26.5, 'Bon début !'),
(1, '2025-10-17', 82.8, 26.1, 'Perte stable et régulière.');

INSERT INTO notifications (abonne_id, type, message)
VALUES 
(1, 'Information', 'Bienvenue au Centre de Réforme Physique 💪'),
(1, 'Rappel', 'Votre abonnement arrive à expiration le 31 octobre 2025.'),
(NULL, 'Alerte', 'Un nouveau membre vient de s’inscrire au centre !');
