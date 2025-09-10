-- Creation de la base de données
create database yabiso;

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

-- Table des abonnements
CREATE TABLE abonnements (
    abonnement_id SERIAL PRIMARY KEY,
    nom_abonnement VARCHAR(50) NOT NULL,
    prix NUMERIC(10, 2) NOT NULL,
    duree INT NOT NULL, -- en jours
    description TEXT
);

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

-- Table des notifications (ex: abonnement expiré)
CREATE TABLE notifications (
    notification_id SERIAL PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    message TEXT NOT NULL,
    date_notification TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    statut VARCHAR(20) CHECK (statut IN ('non lu', 'lu')) DEFAULT 'non lu',
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(utilisateur_id) ON DELETE CASCADE
);
