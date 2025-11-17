# 🚀 Weazel News - Système de Gestion de Commandes

## 📋 Description

Système complet de gestion de commandes avec trois niveaux d'accès :
- **Client** : Passer des commandes, suivre leur statut, discuter avec le personnel
- **Personnel** : Gérer toutes les commandes, répondre aux clients, générer des factures
- **Direction** : Toutes les fonctionnalités du personnel + gestion des comptes personnel

## 🛠️ Technologies utilisées

- **Backend** : PHP 7.4+
- **Base de données** : MySQL/MariaDB
- **Frontend** : HTML5, CSS3, JavaScript (Vanilla)
- **Serveur local** : XAMPP, WAMP, ou LAMP

## 📦 Installation

### 1. Prérequis

- PHP 7.4 ou supérieur
- MySQL 5.7 ou supérieur (ou MariaDB)
- Serveur Apache
- phpMyAdmin (recommandé)

### 2. Installation avec XAMPP

1. **Télécharger et installer XAMPP**
   - Téléchargez depuis https://www.apachefriends.org/
   - Installez avec Apache et MySQL

2. **Copier les fichiers du projet**
   ```
   Copiez tous les fichiers dans : C:\xampp\htdocs\weazel_news\
   ```

3. **Structure des dossiers**
   ```
   weazel_news/
   ├── api/
   │   ├── commandes.php
   │   ├── chat.php
   │   └── personnel.php
   ├── uploads/
   │   ├── factures/
   │   ├── images/
   │   └── fichiers/
   ├── config.php
   ├── index.php
   ├── login.php
   ├── register.php
   ├── dashboard.php
   ├── commande.php
   ├── logout.php
   └── database.sql
   ```

4. **Démarrer les services**
   - Ouvrez le Control Panel XAMPP
   - Démarrez Apache et MySQL

### 3. Configuration de la base de données

1. **Ouvrir phpMyAdmin**
   - Allez sur http://localhost/phpmyadmin

2. **Créer la base de données**
   - Cliquez sur "Nouvelle base de données"
   - Nom : `weazel_news`
   - Interclassement : `utf8mb4_unicode_ci`

3. **Importer la structure**
   - Sélectionnez la base `weazel_news`
   - Onglet "Importer"
   - Choisissez le fichier `database.sql`
   - Cliquez sur "Exécuter"

4. **Créer les comptes par défaut**

Exécutez ces requêtes SQL dans phpMyAdmin :

```sql
-- Compte Direction (email: direction@weazelnews.com, password: Admin123!)
INSERT INTO users (nom, prenom, email, password, role) 
VALUES ('Admin', 'Direction', 'direction@weazelnews.com', 
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'direction');

-- Compte Personnel (email: personnel@weazelnews.com, password: Personnel123!)
INSERT INTO users (nom, prenom, email, password, role) 
VALUES ('Dupont', 'Jean', 'personnel@weazelnews.com', 
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'personnel');
```

**Note** : Ces mots de passe sont des exemples. Pour les générer correctement :
```php
<?php
echo password_hash('Admin123!', PASSWORD_BCRYPT);
echo "\n";
echo password_hash('Personnel123!', PASSWORD_BCRYPT);
?>
```

### 4. Configuration du projet

1. **Modifier config.php si nécessaire**
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'weazel_news');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Mot de passe MySQL (vide par défaut sur XAMPP)
   ```

2. **Vérifier les permissions des dossiers**
   - Le dossier `uploads/` doit être accessible en écriture

### 5. Accéder à l'application

- **URL principale** : http://localhost/weazel_news/
- **phpMyAdmin** : http://localhost/phpmyadmin/

## 👥 Comptes de test

### Direction
- **Email** : direction@weazelnews.com
- **Mot de passe** : Admin123!
- **Accès** : Toutes les fonctionnalités

### Personnel
- **Email** : personnel@weazelnews.com
- **Mot de passe** : Personnel123!
- **Accès** : Gestion des commandes et chat

### Client
- Créez votre compte via la page d'inscription

## 🎯 Fonctionnalités principales

### Pour les Clients
- ✅ Inscription et connexion sécurisées
- ✅ Voir le catalogue de services
- ✅ Créer de nouvelles commandes
- ✅ Suivre le statut des commandes
- ✅ Discuter avec le personnel via chat
- ✅ Envoyer des messages, images et fichiers
- ✅ Télécharger les factures

### Pour le Personnel
- ✅ Voir toutes les commandes
- ✅ Répondre aux clients via chat
- ✅ Uploader des factures
- ✅ Fermer les commandes
- ✅ Accès aux outils de génération de factures et devis

### Pour la Direction
- ✅ Toutes les fonctionnalités du personnel
- ✅ Créer des comptes personnel
- ✅ Modifier les comptes personnel
- ✅ Supprimer des comptes personnel
- ✅ Voir les statistiques globales

## 🔒 Sécurité

- Mots de passe hashés avec bcrypt
- Protection CSRF
- Validation des données côté serveur
- Upload de fichiers sécurisé
- Gestion des permissions par rôle
- Requêtes préparées (protection SQL injection)

## 🛠️ Outils externes intégrés

- **Génération de factures** : https://generation-facture.weazelnewsinnocent.workers.dev/
- **Génération de devis** : https://generation-devis.weazelnewsinnocent.workers.dev/
- **Catalogue** : https://catalogue-weazel-news-innocent.weazelnewsinnocent.workers.dev/

## 🐛 Dépannage

### Erreur de connexion à la base de données
- Vérifiez que MySQL est démarré dans XAMPP
- Vérifiez les identifiants dans `config.php`

### Erreur lors de l'upload de fichiers
- Vérifiez les permissions du dossier `uploads/`
- Sur Windows : Clic droit > Propriétés > Sécurité
- Vérifiez `upload_max_filesize` dans php.ini

### Page blanche
- Activez l'affichage des erreurs dans php.ini :
  ```ini
  display_errors = On
  error_reporting = E_ALL
  ```

### Les messages ne s'affichent pas
- Vérifiez la console JavaScript (F12)
- Vérifiez que le dossier `api/` est accessible

## 📝 Notes importantes

1. **Sécurité en production** :
   - Changez les mots de passe par défaut
   - Utilisez HTTPS
   - Configurez des mots de passe MySQL forts
   - Désactivez `display_errors` en production

2. **Performance** :
   - Le chat utilise du polling (requêtes toutes les 3 secondes)
   - Pour une meilleure performance, considérez WebSocket

3. **Backup** :
   - Sauvegardez régulièrement la base de données
   - Sauvegardez le dossier `uploads/`

## 📞 Support

Pour toute question ou problème, consultez :
- La documentation PHP : https://www.php.net/
- La documentation MySQL : https://dev.mysql.com/doc/

## 📄 Licence

Ce projet est développé pour Weazel News.

---

**Bon développement ! 🚀**