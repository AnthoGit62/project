<?php
require_once '../config.php';
requireLogin();

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    $db = getDB();
    $user = getCurrentUser();
    
    switch ($action) {
        case 'create':
            if ($method === 'POST' && $user['role'] === 'client') {
                $data = json_decode(file_get_contents('php://input'), true);
                
                $nom_service = trim($data['nom_service'] ?? '');
                $quantite = intval($data['quantite'] ?? 1);
                $type_commande = $data['type_commande'] ?? '';
                
                if (empty($nom_service) || $quantite < 1 || !in_array($type_commande, ['personnel', 'professionnel'])) {
                    throw new Exception('Données invalides');
                }
                
                $stmt = $db->prepare("
                    INSERT INTO commandes (user_id, nom_service, quantite, type_commande, statut) 
                    VALUES (?, ?, ?, ?, 'en_attente')
                ");
                $stmt->execute([$user['id'], $nom_service, $quantite, $type_commande]);
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Commande créée avec succès',
                    'commande_id' => $db->lastInsertId()
                ]);
            }
            break;
            
        case 'list':
            if ($user['role'] === 'client') {
                $stmt = $db->prepare("
                    SELECT c.*, 
                           (SELECT COUNT(*) FROM messages WHERE commande_id = c.id) as nb_messages
                    FROM commandes c 
                    WHERE c.user_id = ? 
                    ORDER BY c.date_creation DESC
                ");
                $stmt->execute([$user['id']]);
            } else {
                $stmt = $db->query("
                    SELECT c.*, 
                           u.nom, u.prenom, u.email,
                           (SELECT COUNT(*) FROM messages WHERE commande_id = c.id) as nb_messages
                    FROM commandes c 
                    JOIN users u ON c.user_id = u.id 
                    ORDER BY c.date_creation DESC
                ");
            }
            
            $commandes = $stmt->fetchAll();
            echo json_encode(['success' => true, 'commandes' => $commandes]);
            break;
            
        case 'get':
            $commande_id = intval($_GET['id'] ?? 0);
            
            if ($user['role'] === 'client') {
                $stmt = $db->prepare("SELECT * FROM commandes WHERE id = ? AND user_id = ?");
                $stmt->execute([$commande_id, $user['id']]);
            } else {
                $stmt = $db->prepare("
                    SELECT c.*, u.nom, u.prenom, u.email, u.telephone 
                    FROM commandes c 
                    JOIN users u ON c.user_id = u.id 
                    WHERE c.id = ?
                ");
                $stmt->execute([$commande_id]);
            }
            
            $commande = $stmt->fetch();
            
            if (!$commande) {
                throw new Exception('Commande non trouvée');
            }
            
            echo json_encode(['success' => true, 'commande' => $commande]);
            break;
            
        case 'close':
            if (!in_array($user['role'], ['personnel', 'direction'])) {
                throw new Exception('Permission refusée');
            }
            
            $commande_id = intval($_POST['commande_id'] ?? 0);
            
            // Vérifier qu'une facture a été uploadée
            $stmt = $db->prepare("
                SELECT COUNT(*) as nb 
                FROM fichiers_commande 
                WHERE commande_id = ? AND est_facture = 1
            ");
            $stmt->execute([$commande_id]);
            $result = $stmt->fetch();
            
            if ($result['nb'] == 0) {
                throw new Exception('Une facture doit être uploadée avant de fermer la commande');
            }
            
            // Fermer la commande
            $stmt = $db->prepare("UPDATE commandes SET statut = 'terminee' WHERE id = ?");
            $stmt->execute([$commande_id]);
            
            echo json_encode(['success' => true, 'message' => 'Commande fermée avec succès']);
            break;
            
        case 'delete':
            if ($user['role'] !== 'direction') {
                throw new Exception('Permission refusée');
            }
            
            $commande_id = intval($_POST['commande_id'] ?? 0);
            $stmt = $db->prepare("DELETE FROM commandes WHERE id = ?");
            $stmt->execute([$commande_id]);
            
            echo json_encode(['success' => true, 'message' => 'Commande supprimée']);
            break;
            
        case 'stats':
            if ($user['role'] === 'client') {
                $stmt = $db->prepare("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminees
                    FROM commandes 
                    WHERE user_id = ?
                ");
                $stmt->execute([$user['id']]);
            } else {
                $stmt = $db->query("
                    SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END) as en_attente,
                        SUM(CASE WHEN statut = 'terminee' THEN 1 ELSE 0 END) as terminees
                    FROM commandes
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