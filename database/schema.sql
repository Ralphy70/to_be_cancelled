-- Base de données pour la gestion de chantiers immobiliers
-- Création de la base de données

CREATE DATABASE IF NOT EXISTS gestion_chantiers CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE gestion_chantiers;

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des chantiers
CREATE TABLE chantiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    adresse VARCHAR(255),
    date_debut DATE,
    date_fin_prevue DATE,
    budget_total DECIMAL(15, 2) DEFAULT 0.00,
    statut ENUM('planification', 'en_cours', 'suspendu', 'termine', 'annule') DEFAULT 'planification',
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_statut (statut),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des postes budgétaires (catégories principales)
CREATE TABLE postes_budgetaires (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chantier_id INT NOT NULL,
    nom VARCHAR(200) NOT NULL,
    description TEXT,
    budget_alloue DECIMAL(15, 2) DEFAULT 0.00,
    budget_consomme DECIMAL(15, 2) DEFAULT 0.00,
    ordre INT DEFAULT 0,
    parent_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES postes_budgetaires(id) ON DELETE CASCADE,
    INDEX idx_chantier (chantier_id),
    INDEX idx_parent (parent_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des financeurs
CREATE TABLE financeurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(200) NOT NULL,
    type ENUM('banque', 'investisseur', 'subvention', 'fonds_propres', 'autre') DEFAULT 'autre',
    contact_nom VARCHAR(100),
    contact_email VARCHAR(100),
    contact_telephone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table de liaison chantiers-financeurs avec suivi des financements
CREATE TABLE chantier_financements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chantier_id INT NOT NULL,
    financeur_id INT NOT NULL,
    montant_prevu DECIMAL(15, 2) NOT NULL,
    montant_verse DECIMAL(15, 2) DEFAULT 0.00,
    pourcentage_participation DECIMAL(5, 2),
    date_accord DATE,
    conditions TEXT,
    statut ENUM('en_negociation', 'accorde', 'verse_partiel', 'verse_total', 'refuse') DEFAULT 'en_negociation',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
    FOREIGN KEY (financeur_id) REFERENCES financeurs(id) ON DELETE CASCADE,
    INDEX idx_chantier (chantier_id),
    INDEX idx_financeur (financeur_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des versements effectués par les financeurs
CREATE TABLE versements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    financement_id INT NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    date_versement DATE NOT NULL,
    reference VARCHAR(100),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (financement_id) REFERENCES chantier_financements(id) ON DELETE CASCADE,
    INDEX idx_financement (financement_id),
    INDEX idx_date (date_versement)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des dépenses par poste budgétaire
CREATE TABLE depenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    poste_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    montant DECIMAL(15, 2) NOT NULL,
    date_depense DATE NOT NULL,
    fournisseur VARCHAR(200),
    numero_facture VARCHAR(100),
    statut ENUM('prevue', 'engagee', 'payee') DEFAULT 'prevue',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (poste_id) REFERENCES postes_budgetaires(id) ON DELETE CASCADE,
    INDEX idx_poste (poste_id),
    INDEX idx_date (date_depense),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des documents attachés (optionnel)
CREATE TABLE documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chantier_id INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(500) NOT NULL,
    type_document VARCHAR(50),
    taille INT,
    uploaded_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (chantier_id) REFERENCES chantiers(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_chantier (chantier_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertion de données de test
-- Utilisateur admin par défaut (mot de passe: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@gestion-chantiers.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('user1', 'user1@gestion-chantiers.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Financeurs exemples
INSERT INTO financeurs (nom, type, contact_nom, contact_email, contact_telephone) VALUES
('Banque Nationale', 'banque', 'Jean Dupont', 'j.dupont@banque.com', '01-23-45-67-89'),
('Investisseur Privé SA', 'investisseur', 'Marie Martin', 'm.martin@invest.com', '01-98-76-54-32'),
('Région Île-de-France', 'subvention', 'Pierre Bernard', 'p.bernard@region.fr', '01-11-22-33-44'),
('Fonds propres', 'fonds_propres', NULL, NULL, NULL);
