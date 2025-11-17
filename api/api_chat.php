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
        case 'send':
            if ($method === 'POST') {
                $commande_id = intval($_POST['commande_id'] ?? 0);
                $message = trim($_POST['message'] ?? '');
                $type_message = 'texte';
                $fichier_url = null;
                $fichier_nom = null;
                
                // Vérifier que l'utilisateur a accès à cette commande
                if ($user['role'] === 'client') {
                    $stmt = $db->prepare("SELECT id FROM commandes WHERE id = ? AND user_id = ?");
                    $stmt->execute([$commande_id, $user['id']]);
                } else {
                    $stmt = $db->prepare("SELECT id FROM commandes WHERE id = ?");
                    $stmt->execute([$commande_id]);
                }
                
                if (!$stmt->fetch()) {
                    throw new Exception('Commande non trouvée ou accès refusé');
                }
                
                // Gérer l'upload de fichier
                if (isset($_FILES['fichier']) && $_FILES['fichier']['error'] === UPLOAD_ERR_OK) {
                    $finfo = finfo_open(FILEINFO_MIME_TYPE);
                    $mime = finfo_file($finfo, $_FILES['fichier']['tmp_name']);
                    finfo_close($finfo);
                    
                    if (strpos($mime, 'image/') === 0) {
                        $type_message = 'image';
                        $result = uploadFile($_FILES['fichier'], 'image');
                    } else {
                        $type_message = 'fichier';
                        $result = uploadFile($_FILES['fichier'], 'fichier');
                    }
                    
                    if (!$result['success']) {
                        throw new Exception($result['error']);
                    }
                    
                    $fichier_url = $result['url'];
                    $fichier_nom = $_FILES['fichier']['name'];
                }
                
                // Insérer le message
                $stmt = $db->prepare("
                    INSERT INTO messages (commande_id, user_id, message, type_message, fichier_url, fichier_nom) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                $stmt->execute([$commande_id, $user['id'], $message, $type_message, $fichier_url, $fichier_nom]);
                
                echo json_encode([
                    'success' => true,
                    'message_id' => $db->lastInsertId()
                ]);
            }
            break;
            
        case 'get':
            $commande_id = intval($_GET['commande_id'] ?? 0);
            $since = intval($_GET['since'] ?? 0); // Pour le polling
            
            // Vérifier l'accès
            if ($user['role'] === 'client') {
                $stmt = $db->prepare("SELECT id FROM commandes WHERE id = ? AND user_id = ?");
                $stmt->execute([$commande_id, $user['id']]);
            } else {
                $stmt = $db->prepare("SELECT id FROM commandes WHERE id = ?");
                $stmt->execute([$commande_id]);
            }
            
            if (!$stmt->fetch()) {
                throw new Exception('Accès refusé');
            }
            
            // Récupérer les messages
            if ($since > 0) {
                $stmt = $db->prepare("
                    SELECT m.*, u.nom, u.prenom, u.role 
                    FROM messages m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.commande_id = ? AND m.id > ? 
                    ORDER BY m.date_envoi ASC
                ");
                $stmt->execute([$commande_id, $since]);
            } else {
                $stmt = $db->prepare("
                    SELECT m.*, u.nom, u.prenom, u.role 
                    FROM messages m 
                    JOIN users u ON m.user_id = u.id 
                    WHERE m.commande_id = ? 
                    ORDER BY m.date_envoi ASC
                ");
                $stmt->execute([$commande_id]);
            }
            
            $messages = $stmt->fetchAll();
            echo json_encode(['success' => true, 'messages' => $messages]);
            break;
            
        case 'upload_facture':
            if (!in_array($user['role'], ['personnel', 'direction'])) {
                throw new Exception('Permission refusée');
            }
            
            if (!isset($_FILES['facture'])) {
                throw new Exception('Aucun fichier fourni');
            }
            
            $commande_id = intval($_POST['commande_id'] ?? 0);
            
            // Vérifier que la commande existe
            $stmt = $db->prepare("SELECT id FROM commandes WHERE id = ?");
            $stmt->execute([$commande_id]);
            if (!$stmt->fetch()) {
                throw new Exception('Commande non trouvée');
            }
            
            // Upload de la facture
            $result = uploadFile($_FILES['facture'], 'facture');
            
            if (!$result['success']) {
                throw new Exception($result['error']);
            }
            
            // Enregistrer la facture
            $stmt = $db->prepare("
                INSERT INTO fichiers_commande 
                (commande_id, nom_fichier, chemin_fichier, type_fichier, taille_fichier, est_facture) 
                VALUES (?, ?, ?, 'application/pdf', ?, 1)
            ");
            $stmt->execute([
                $commande_id, 
                $_FILES['facture']['name'], 
                $result['filepath'],
                $_FILES['facture']['size']
            ]);
            
            // Mettre à jour l'URL de la facture dans la commande
            $stmt = $db->prepare("UPDATE commandes SET facture_url = ? WHERE id = ?");
            $stmt->execute([$result['url'], $commande_id]);
            
            // Envoyer un message automatique
            $stmt = $db->prepare("
                INSERT INTO messages (commande_id, user_id, message, type_message, fichier_url, fichier_nom) 
                VALUES (?, ?, ?, 'fichier', ?, ?)
            ");
            $stmt->execute([
                $commande_id, 
                $user['id'], 
                'Facture uploadée',
                $result['url'],
                $_FILES['facture']['name']
            ]);
            
            echo json_encode([
                'success' => true,
                'message' => 'Facture uploadée avec succès',
                'url' => $result['url']
            ]);
            break;
            
        case 'get_facture':
            $commande_id = intval($_GET['commande_id'] ?? 0);
            
            // Vérifier l'accès
            if ($user['role'] === 'client') {
                $stmt = $db->prepare("SELECT facture_url FROM commandes WHERE id = ? AND user_id = ?");
                $stmt->execute([$commande_id, $user['id']]);
            } else {
                $stmt = $db->prepare("SELECT facture_url FROM commandes WHERE id = ?");
                $stmt->execute([$commande_id]);
            }
            
            $commande = $stmt->fetch();
            
            if (!$commande) {
                throw new Exception('Accès refusé');
            }
            
            if (!$commande['facture_url']) {
                throw new Exception('Aucune facture disponible');
            }
            
            echo json_encode([
                'success' => true,
                'facture_url' => $commande['facture_url']
            ]);
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