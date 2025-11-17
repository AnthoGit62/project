<?php
session_start();

// Afficher les erreurs en développement (à désactiver en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'weazel_news');
define('DB_USER', 'root');
define('DB_PASS', '');

// Chemins des dossiers
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('FACTURES_DIR', UPLOAD_DIR . 'factures/');
define('IMAGES_DIR', UPLOAD_DIR . 'images/');
define('FICHIERS_DIR', UPLOAD_DIR . 'fichiers/');

// Créer les dossiers s'ils n'existent pas
if (!file_exists(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
if (!file_exists(FACTURES_DIR)) mkdir(FACTURES_DIR, 0755, true);
if (!file_exists(IMAGES_DIR)) mkdir(IMAGES_DIR, 0755, true);
if (!file_exists(FICHIERS_DIR)) mkdir(FICHIERS_DIR, 0755, true);

// Connexion à la base de données
function getDB() {
    try {
        $pdo = new PDO(
            "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
            DB_USER,
            DB_PASS,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}

// Vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Vérifier le rôle de l'utilisateur
function hasRole($roles) {
    if (!isLoggedIn()) return false;
    if (!is_array($roles)) $roles = [$roles];
    return in_array($_SESSION['user_role'], $roles);
}

// Rediriger si non connecté
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

// Rediriger si rôle insuffisant
function requireRole($roles) {
    requireLogin();
    if (!hasRole($roles)) {
        header('Location: dashboard.php');
        exit;
    }
}

// Récupérer l'utilisateur connecté
function getCurrentUser() {
    if (!isLoggedIn()) return null;
    
    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Hasher un mot de passe
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT);
}

// Vérifier un mot de passe
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// Sécuriser l'affichage
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Upload de fichier sécurisé
function uploadFile($file, $type = 'fichier') {
    $allowed_types = [
        'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
        'fichier' => ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 
                      'application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'facture' => ['application/pdf']
    ];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'error' => 'Erreur lors de l\'upload'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mime, $allowed_types[$type])) {
        return ['success' => false, 'error' => 'Type de fichier non autorisé'];
    }
    
    if ($file['size'] > 10 * 1024 * 1024) { // 10 MB max
        return ['success' => false, 'error' => 'Fichier trop volumineux (max 10 MB)'];
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '_' . time() . '.' . $extension;
    
    $dir = $type === 'image' ? IMAGES_DIR : ($type === 'facture' ? FACTURES_DIR : FICHIERS_DIR);
    $filepath = $dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $filepath)) {
        // Générer l'URL relative
        $relative_path = '/uploads/' . ($type === 'image' ? 'images/' : ($type === 'facture' ? 'factures/' : 'fichiers/')) . $filename;
        
        return [
            'success' => true,
            'filename' => $filename,
            'filepath' => $filepath,
            'url' => $relative_path
        ];
    }
    
    return ['success' => false, 'error' => 'Erreur lors de la sauvegarde'];
}

// Formater une date
function formatDate($date) {
    if (empty($date)) return '-';
    return date('d/m/Y à H:i', strtotime($date));
}

// Récupérer les statistiques pour le dashboard
function getDashboardStats($userId, $role) {
    $db = getDB();
    $stats = [];
    
    if ($role === 'client') {
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM commandes WHERE user_id = ? AND statut = 'en_attente'");
        $stmt->execute([$userId]);
        $stats['en_attente'] = $stmt->fetch()['total'];
        
        $stmt = $db->prepare("SELECT COUNT(*) as total FROM commandes WHERE user_id = ? AND statut = 'terminee'");
        $stmt->execute([$userId]);
        $stats['terminees'] = $stmt->fetch()['total'];
    } else {
        $stmt = $db->query("SELECT COUNT(*) as total FROM commandes WHERE statut = 'en_attente'");
        $stats['en_attente'] = $stmt->fetch()['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM commandes WHERE statut = 'terminee'");
        $stats['terminees'] = $stmt->fetch()['total'];
        
        if ($role === 'direction') {
            $stmt = $db->query("SELECT COUNT(*) as total FROM users WHERE role = 'personnel'");
            $stats['personnel'] = $stmt->fetch()['total'];
        }
    }
    
    return $stats;
}
?>