-- =============================================
-- Base de données Weazel News
-- Version complète avec données de test
-- =============================================

-- Création de la base de données
DROP DATABASE IF EXISTS weazel_news;
CREATE DATABASE weazel_news CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE weazel_news;

-- =============================================
-- TABLES
-- =============================================

-- Table des utilisateurs
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    telephone VARCHAR(20),
    lieu_habitation VARCHAR(200),
    profession VARCHAR(100),
    role ENUM('client', 'personnel', 'direction') DEFAULT 'client',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif TINYINT(1) DEFAULT 1,
    INDEX idx_email (email),
    INDEX idx_role (role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des commandes
CREATE TABLE commandes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    nom_service VARCHAR(200) NOT NULL,
    quantite INT NOT NULL DEFAULT 1,
    type_commande ENUM('personnel', 'professionnel') NOT NULL,
    statut ENUM('en_attente', 'terminee', 'annulee') DEFAULT 'en_attente',
    facture_url VARCHAR(255),
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_statut (statut)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des messages du chat
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    user_id INT NOT NULL,
    message TEXT,
    type_message ENUM('texte', 'image', 'fichier') DEFAULT 'texte',
    fichier_url VARCHAR(255),
    fichier_nom VARCHAR(255),
    date_envoi TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_commande (commande_id),
    INDEX idx_date (date_envoi)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table des fichiers joints aux commandes
CREATE TABLE fichiers_commande (
    id INT AUTO_INCREMENT PRIMARY KEY,
    commande_id INT NOT NULL,
    nom_fichier VARCHAR(255) NOT NULL,
    chemin_fichier VARCHAR(255) NOT NULL,
    type_fichier VARCHAR(50),
    taille_fichier INT,
    est_facture TINYINT(1) DEFAULT 0,
    date_upload TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    INDEX idx_commande (commande_id),
    INDEX idx_facture (est_facture)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================
-- DONNÉES DE TEST
-- =============================================

-- Utilisateurs de test
-- Tous les mots de passe sont : Password123!
-- Hash généré avec : password_hash('Password123!', PASSWORD_BCRYPT)

-- Direction
INSERT INTO users (nom, prenom, email, password, telephone, lieu_habitation, role) VALUES
('Admin', 'Direction', 'direction@weazelnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0601020304', 'Los Santos', 'direction');

-- Personnel
INSERT INTO users (nom, prenom, email, password, telephone, lieu_habitation, role) VALUES
('Dupont', 'Jean', 'personnel@weazelnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0605060708', 'Los Santos', 'personnel'),
('Martin', 'Sophie', 'sophie.martin@weazelnews.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0609101112', 'Sandy Shores', 'personnel');

-- Clients
INSERT INTO users (nom, prenom, email, password, telephone, lieu_habitation, profession) VALUES
('Durant', 'Pierre', 'pierre.durant@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0612131415', 'Los Santos', 'Entrepreneur'),
('Bernard', 'Marie', 'marie.bernard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0616171819', 'Paleto Bay', 'Avocat'),
('Leroy', 'Lucas', 'lucas.leroy@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0620212223', 'Los Santos', 'Médecin'),
('Moreau', 'Emma', 'emma.moreau@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0624252627', 'Vinewood', 'Designer');

-- Commandes de test

-- Commande en attente 1
INSERT INTO commandes (user_id, nom_service, quantite, type_commande, statut) VALUES
(4, 'Création de logo professionnel', 1, 'professionnel', 'en_attente');

-- Commande en attente 2
INSERT INTO commandes (user_id, nom_service, quantite, type_commande, statut) VALUES
(5, 'Design de site web', 1, 'professionnel', 'en_attente');

-- Commande en attente 3
INSERT INTO commandes (user_id, nom_service, quantite, type_commande, statut) VALUES
(6, 'Carte de visite personnalisée', 100, 'personnel', 'en_attente');

-- Commande terminée 1
INSERT INTO commandes (user_id, nom_service, quantite, type_commande, statut, facture_url) VALUES
(4, 'Flyers publicitaires', 500, 'professionnel', 'terminee', '/uploads/factures/facture_exemple.pdf');

-- Commande terminée 2
INSERT INTO commandes (user_id, nom_service, quantite, type_commande, statut, facture_url) VALUES
(7, 'Brochure entreprise', 1, 'professionnel', 'terminee', '/uploads/factures/facture_exemple2.pdf');

-- Messages de test pour la commande 1
INSERT INTO messages (commande_id, user_id, message, type_message) VALUES
(1, 4, 'Bonjour, j\'aimerais un logo moderne avec des couleurs vives pour ma nouvelle entreprise.', 'texte'),
(1, 2, 'Bonjour Pierre ! Merci pour votre commande. Pourriez-vous me donner plus de détails sur votre secteur d\'activité ?', 'texte'),
(1, 4, 'Nous sommes dans le secteur de la technologie, spécialisés dans les applications mobiles.', 'texte'),
(1, 2, 'Parfait ! Avez-vous des couleurs ou un style particulier en tête ?', 'texte'),
(1, 4, 'J\'aimerais quelque chose avec du bleu et du blanc, style minimaliste et professionnel.', 'texte');

-- Messages de test pour la commande 2
INSERT INTO messages (commande_id, user_id, message, type_message) VALUES
(2, 5, 'Bonjour, je souhaite un site web pour mon cabinet d\'avocat.', 'texte'),
(2, 3, 'Bonjour Marie ! Avec plaisir. Combien de pages envisagez-vous ?', 'texte'),
(2, 5, 'Je pense à 5-6 pages : accueil, services, équipe, contact, mentions légales.', 'texte');

-- Messages de test pour la commande 3
INSERT INTO messages (commande_id, user_id, message, type_message) VALUES
(3, 6, 'Bonjour, j\'ai besoin de cartes de visite pour mon activité médicale.', 'texte'),
(3, 2, 'Bonjour Docteur ! Avez-vous déjà un design ou souhaitez-vous que nous en créions un ?', 'texte');

-- Messages de test pour la commande 4 (terminée)
INSERT INTO messages (commande_id, user_id, message, type_message) VALUES
(4, 4, 'Bonjour, je voudrais des flyers pour promouvoir mes services.', 'texte'),
(4, 2, 'Bonjour ! Nous avons bien reçu votre demande. Voici quelques propositions.', 'texte'),
(4, 4, 'Super ! La proposition 2 me convient parfaitement.', 'texte'),
(4, 2, 'Parfait ! Nous lançons la production. Vous recevrez votre facture sous peu.', 'texte'),
(4, 2, 'Voici votre facture. Merci pour votre confiance !', 'fichier');

-- =============================================
-- VUES UTILES
-- =============================================

-- Vue des statistiques globales
CREATE OR REPLACE VIEW vue_stats_globales AS
SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'client') as total_clients,
    (SELECT COUNT(*) FROM users WHERE role = 'personnel') as total_personnel,
    (SELECT COUNT(*) FROM commandes) as total_commandes,
    (SELECT COUNT(*) FROM commandes WHERE statut = 'en_attente') as commandes_en_attente,
    (SELECT COUNT(*) FROM commandes WHERE statut = 'terminee') as commandes_terminees,
    (SELECT COUNT(*) FROM messages) as total_messages;

-- Vue des commandes avec informations client
CREATE OR REPLACE VIEW vue_commandes_completes AS
SELECT 
    c.id,
    c.nom_service,
    c.quantite,
    c.type_commande,
    c.statut,
    c.facture_url,
    c.date_creation,
    u.id as client_id,
    u.nom as client_nom,
    u.prenom as client_prenom,
    u.email as client_email,
    u.telephone as client_telephone,
    (SELECT COUNT(*) FROM messages WHERE commande_id = c.id) as nb_messages
FROM commandes c
JOIN users u ON c.user_id = u.id
ORDER BY c.date_creation DESC;

-- =============================================
-- PROCÉDURES STOCKÉES
-- =============================================

-- Procédure pour obtenir les statistiques d'un utilisateur
DELIMITER //
CREATE PROCEDURE get_user_stats(IN p_user_id INT, IN p_role VARCHAR(20))
BEGIN
    IF p_role = 'client' THEN
        SELECT 
            COUNT(*) as total_commandes,
            SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
            SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminees
        FROM commandes 
        WHERE user_id = p_user_id;
    ELSE
        SELECT 
            COUNT(DISTINCT m.commande_id) as commandes_traitees,
            COUNT(m.id) as messages_envoyes
        FROM messages m
        WHERE m.user_id = p_user_id;
    END IF;
END //
DELIMITER ;

-- =============================================
-- TRIGGERS
-- =============================================

-- Trigger pour mettre à jour la date de modification d'une commande
DELIMITER //
CREATE TRIGGER update_commande_timestamp 
BEFORE UPDATE ON commandes
FOR EACH ROW
BEGIN
    SET NEW.date_modification = CURRENT_TIMESTAMP;
END //
DELIMITER ;

-- =============================================
-- INDEX ADDITIONNELS POUR PERFORMANCES
-- =============================================

-- Index pour améliorer les recherches
CREATE INDEX idx_commandes_date ON commandes(date_creation DESC);
CREATE INDEX idx_messages_date ON messages(date_envoi DESC);
CREATE INDEX idx_users_actif ON users(actif);

-- =============================================
-- REQUÊTES UTILES (COMMENTÉES)
-- =============================================

-- Voir toutes les commandes avec leurs clients
-- SELECT * FROM vue_commandes_completes;

-- Voir les statistiques globales
-- SELECT * FROM vue_stats_globales;

-- Obtenir les stats d'un client (ID 4)
-- CALL get_user_stats(4, 'client');

-- Obtenir les stats d'un membre du personnel (ID 2)
-- CALL get_user_stats(2, 'personnel');

-- Voir les commandes les plus récentes
-- SELECT * FROM commandes ORDER BY date_creation DESC LIMIT 10;

-- Voir les clients les plus actifs
-- SELECT 
--     u.nom, u.prenom, u.email,
--     COUNT(c.id) as nb_commandes
-- FROM users u
-- LEFT JOIN commandes c ON u.id = c.user_id
-- WHERE u.role = 'client'
-- GROUP BY u.id
-- ORDER BY nb_commandes DESC;

-- Voir l'activité du personnel
-- SELECT 
--     u.nom, u.prenom,
--     COUNT(DISTINCT m.commande_id) as commandes_traitees,
--     COUNT(m.id) as messages_envoyes
-- FROM users u
-- LEFT JOIN messages m ON u.id = m.user_id
-- WHERE u.role IN ('personnel', 'direction')
-- GROUP BY u.id
-- ORDER BY messages_envoyes DESC;

-- =============================================
-- INFORMATIONS DE CONNEXION
-- =============================================

/*
COMPTES DE TEST :

Direction :
- Email: direction@weazelnews.com
- Mot de passe: Password123!

Personnel :
- Email: personnel@weazelnews.com
- Mot de passe: Password123!
- Email: sophie.martin@weazelnews.com
- Mot de passe: Password123!

Clients :
- Email: pierre.durant@email.com
- Mot de passe: Password123!
- Email: marie.bernard@email.com
- Mot de passe: Password123!
- Email: lucas.leroy@email.com
- Mot de passe: Password123!
- Email: emma.moreau@email.com
- Mot de passe: Password123!
*/

-- Fin du script