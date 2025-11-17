<?php
require_once '../config.php';
requireRole('direction');

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = getDB();
    
    switch ($action) {
        case 'list':
            $stmt = $db->query("
                SELECT id, nom, prenom, email, telephone, lieu_habitation, profession, date_creation, actif 
                FROM users 
                WHERE role = 'personnel' 
                ORDER BY nom, prenom
            ");
            $personnel = $stmt->fetchAll();
            echo json_encode(['success' => true, 'personnel' => $personnel]);
            break;
            
        case 'create':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $nom = trim($data['nom'] ?? '');
                $prenom = trim($data['prenom'] ?? '');
                $email = trim($data['email'] ?? '');
                $password = $data['password'] ?? '';
                $telephone = trim($data['telephone'] ?? '');
                $lieu_habitation = trim($data['lieu_habitation'] ?? '');
                $profession = trim($data['profession'] ?? '');
                
                if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
                    throw new Exception('Tous les champs obligatoires doivent être remplis');
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email invalide');
                }
                
                if (strlen($password) < 6) {
                    throw new Exception('Le mot de passe doit contenir au moins 6 caractères');
                }
                
                // Vérifier si l'email existe déjà
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
                $stmt->execute([$email]);
                
                if ($stmt->fetch()) {
                    throw new Exception('Cet email est déjà utilisé');
                }
                
                // Créer le compte personnel
                $stmt = $db->prepare("
                    INSERT INTO users (nom, prenom, email, password, telephone, lieu_habitation, profession, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'personnel')
                ");
                
                $hashedPassword = hashPassword($password);
                $stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone, $lieu_habitation, $profession]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Compte personnel créé avec succès',
                    'user_id' => $db->lastInsertId()
                ]);
            }
            break;
            
        case 'get':
            $user_id = intval($_GET['id'] ?? 0);
            
            $stmt = $db->prepare("
                SELECT id, nom, prenom, email, telephone, lieu_habitation, profession, date_creation, actif 
                FROM users 
                WHERE id = ? AND role = 'personnel'
            ");
            $stmt->execute([$user_id]);
            
            $user = $stmt->fetch();
            
            if (!$user) {
                throw new Exception('Utilisateur non trouvé');
            }
            
            echo json_encode(['success' => true, 'user' => $user]);
            break;
            
        case 'update':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $user_id = intval($data['id'] ?? 0);
                $nom = trim($data['nom'] ?? '');
                $prenom = trim($data['prenom'] ?? '');
                $email = trim($data['email'] ?? '');
                $telephone = trim($data['telephone'] ?? '');
                $lieu_habitation = trim($data['lieu_habitation'] ?? '');
                $profession = trim($data['profession'] ?? '');
                
                if (empty($nom) || empty($prenom) || empty($email)) {
                    throw new Exception('Tous les champs obligatoires doivent être remplis');
                }
                
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    throw new Exception('Email invalide');
                }
                
                // Vérifier si l'email est déjà utilisé par un autre utilisateur
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $user_id]);
                
                if ($stmt->fetch()) {
                    throw new Exception('Cet email est déjà utilisé');
                }
                
                // Mettre à jour l'utilisateur
                $stmt = $db->prepare("
                    UPDATE users 
                    SET nom = ?, prenom = ?, email = ?, telephone = ?, lieu_habitation = ?, profession = ? 
                    WHERE id = ? AND role = 'personnel'
                ");
                $stmt->execute([$nom, $prenom, $email, $telephone, $lieu_habitation, $profession, $user_id]);
                
                // Si un nouveau mot de passe est fourni
                if (!empty($data['password'])) {
                    if (strlen($data['password']) < 6) {
                        throw new Exception('Le mot de passe doit contenir au moins 6 caractères');
                    }
                    
                    $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $stmt->execute([hashPassword($data['password']), $user_id]);
                }
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Compte mis à jour avec succès'
                ]);
            }
            break;
            
        case 'delete':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $user_id = intval($data['id'] ?? 0);
                
                // Vérifier que l'utilisateur existe et est un membre du personnel
                $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 'personnel'");
                $stmt->execute([$user_id]);
                
                if (!$stmt->fetch()) {
                    throw new Exception('Utilisateur non trouvé');
                }
                
                // Supprimer l'utilisateur
                $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 'personnel'");
                $stmt->execute([$user_id]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Compte supprimé avec succès'
                ]);
            }
            break;
            
        case 'toggle_active':
            if ($method === 'POST') {
                $data = json_decode(file_get_contents('php://input'), true);
                $user_id = intval($data['id'] ?? 0);
                
                $stmt = $db->prepare("
                    UPDATE users 
                    SET actif = NOT actif 
                    WHERE id = ? AND role = 'personnel'
                ");
                $stmt->execute([$user_id]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Statut mis à jour'
                ]);
            }
            break;
            
        case 'stats':
            $user_id = intval($_GET['id'] ?? 0);
            
            if ($user_id > 0) {
                // Stats pour un membre spécifique
                $stmt = $db->prepare("
                    SELECT 
                        COUNT(DISTINCT m.commande_id) as commandes_traitees,
                        COUNT(m.id) as messages_envoyes
                    FROM messages m
                    WHERE m.user_id = ?
                ");
                $stmt->execute([$user_id]);
            } else {
                // Stats globales
                $stmt = $db->query("
                    SELECT 
                        COUNT(*) as total_personnel,
                        SUM(actif) as personnel_actif
                    FROM users 
                    WHERE role = 'personnel'
                ");
            }
            
            $stats = $stmt->fetch();
            echo json_encode(['success' => true, 'stats' => $stats]);
            break;
            
        default:
            throw new Exception('Action non reconnue');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}