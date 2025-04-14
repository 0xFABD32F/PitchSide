CREATE DATABASE footballdb;
USE footballdb;
-- Table des comptes utilisateurs
CREATE TABLE IF NOT EXISTS comptes (
    id_compte INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    login VARCHAR(15) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(15) NOT NULL,
    prenom VARCHAR(15) NOT NULL,
    type_compte ENUM('user', 'admin_tournoi', 'admin_global') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des équipes
CREATE TABLE IF NOT EXISTS equipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    ville VARCHAR(100) NOT NULL,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des joueurs
CREATE TABLE IF NOT EXISTS joueurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipe_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,    
    position ENUM('gardien', 'defenseur_central', 'defenseur_lateral', 'milieu_defensif', 'milieu_offensif', 'ailier_droit', 'ailier_gauche', 'attaquant') NOT NULL,
    numero_maillot INT NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE DEFAULT NULL,
    nationalite VARCHAR(50) NOT NULL,
    origine VARCHAR(50),
    photo VARCHAR(255),
    CONSTRAINT fk_joueur_equipe FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE
);

-- Table des stades
CREATE TABLE IF NOT EXISTS stades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(255) NOT NULL,
    ville VARCHAR(255) NOT NULL,
    capacite INT NOT NULL
);

-- Table du staff technique
CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipe_id INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    date_naissance DATE NOT NULL,
    poste ENUM('Entraineur_principal', 'Entraineur_adjoint', 'Preparateur_physique', 'Medecin') NOT NULL,
    date_debut DATE NOT NULL,
    date_fin DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE
);

-- Table des arbitres
CREATE TABLE IF NOT EXISTS arbitres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    grade VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des publications
CREATE TABLE IF NOT EXISTS publications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    photo VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des types de tournoi
CREATE TABLE IF NOT EXISTS types_tournois (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) UNIQUE NOT NULL
);

-- Table des tournois
CREATE TABLE IF NOT EXISTS tournois (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_id INT NOT NULL,
    date_debut DATE,
    date_fin DATE,    
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (type_id) REFERENCES types_tournois(id) ON DELETE CASCADE
);

-- Table des matches
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_admin INT NOT NULL,
    tournois_id INT NOT NULL,
    stade_id INT NOT NULL,
    arbitre_id INT NOT NULL,
    equipe_domicile_id INT NOT NULL,
    equipe_exterieur_id INT NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME DEFAULT NULL,
    date_match DATE NOT NULL,
    etat ENUM('prevu', 'en_cours', 'termine', 'annule') NOT NULL DEFAULT 'prevu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    score_domicile_prolongation INT DEFAULT NULL,
    score_exterieur_prolongation INT DEFAULT NULL,
    score_domicile_tirs INT DEFAULT NULL,
    score_exterieur_tirs INT DEFAULT NULL,
    score_domicile INT DEFAULT NULL,
    score_exterieur INT DEFAULT NULL,
    FOREIGN KEY (tournois_id) REFERENCES tournois(id) ON DELETE CASCADE,
    FOREIGN KEY (stade_id) REFERENCES stades(id) ON DELETE CASCADE,
    FOREIGN KEY (arbitre_id) REFERENCES arbitres(id) ON DELETE CASCADE,
    FOREIGN KEY (equipe_domicile_id) REFERENCES equipes(id) ON DELETE CASCADE,
    FOREIGN KEY (equipe_exterieur_id) REFERENCES equipes(id) ON DELETE CASCADE,
    FOREIGN KEY (id_admin) REFERENCES comptes(id_compte) ON DELETE CASCADE
);

-- Table des abonnements
CREATE TABLE IF NOT EXISTS abonnement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    type ENUM('match', 'equipe', 'tournoi') NOT NULL,
    reference_id INT NOT NULL,
    FOREIGN KEY (utilisateur_id) REFERENCES comptes(id_compte) ON DELETE CASCADE
);

-- Table du classement des équipes 
CREATE TABLE IF NOT EXISTS classement_equipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournoi_id INT NOT NULL,
    equipe_id INT NOT NULL,
    points INT DEFAULT 0,
    matchs_joues INT DEFAULT 0,
    victoires INT DEFAULT 0,
    defaites INT DEFAULT 0,
    nuls INT DEFAULT 0,
    buts_marques INT DEFAULT 0,
    buts_encaisse INT DEFAULT 0,
    saison YEAR NOT NULL,
    FOREIGN KEY (tournoi_id) REFERENCES tournois(id) ON DELETE CASCADE,
    FOREIGN KEY (equipe_id) REFERENCES equipes(id) ON DELETE CASCADE
);
-- Table pour gerer la coupe du Throne
CREATE TABLE IF NOT EXISTS knockout_stage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tournois_id INT NOT NULL,
    phase ENUM('16e de finale', '8e de finale', 'quart de finale', 'demi-finale', 'finale') NOT NULL,
    match_id INT NOT NULL,
    equipe_gagnante_id INT DEFAULT NULL,
    equipe_perdante_id INT DEFAULT NULL,
    next_match_id INT DEFAULT NULL,
    etat ENUM('prevu', 'en_cours', 'termine') DEFAULT 'prevu',
    methode_victoire ENUM('temps réglementaire', 'prolongations', 'tirs au but') DEFAULT NULL,
    saison YEAR NOT NULL,
    FOREIGN KEY (tournois_id) REFERENCES tournois(id) ON DELETE CASCADE,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (equipe_gagnante_id) REFERENCES equipes(id) ON DELETE SET NULL,
    FOREIGN KEY (equipe_perdante_id) REFERENCES equipes(id) ON DELETE SET NULL,
    FOREIGN KEY (next_match_id) REFERENCES matches(id) ON DELETE SET NULL
);
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    message TEXT NOT NULL,
    reference_type ENUM('match', 'equipe', 'tournoi') NOT NULL,
    reference_id INT NOT NULL,
    statut ENUM('non_lu', 'lu') DEFAULT 'non_lu',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES comptes(id_compte) ON DELETE CASCADE
);

