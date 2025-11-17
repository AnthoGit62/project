<?php
require_once 'config.php';
requireLogin();

$commande_id = intval($_GET['id'] ?? 0);
$user = getCurrentUser();
$db = getDB();

// Récupérer la commande
if ($user['role'] === 'client') {
    $stmt = $db->prepare("
        SELECT c.* 
        FROM commandes c 
        WHERE c.id = ? AND c.user_id = ?
    ");
    $stmt->execute([$commande_id, $user['id']]);
} else {
    $stmt = $db->prepare("
        SELECT c.*, u.nom, u.prenom, u.email, u.telephone, u.lieu_habitation 
        FROM commandes c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$commande_id]);
}

$commande = $stmt->fetch();

if (!$commande) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commande #<?= $commande_id ?> - Weazel News</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            min-height: 100vh;
        }
        
        .header {
            background: rgba(0,0,0,0.3);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
        }
        
        .back-btn {
            color: #e94560;
            text-decoration: none;
            font-size: 1.1rem;
        }
        
        .container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 2rem;
        }
        
        .card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .card h2 {
            color: #e94560;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .info-group {
            margin-bottom: 1rem;
        }
        
        .info-label {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 0.3rem;
        }
        
        .info-value {
            font-size: 1.1rem;
            font-weight: bold;
        }
        
        .badge {
            display: inline-block;
            padding: 0.4rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: bold;
        }
        
        .badge-pending {
            background: rgba(255, 193, 7, 0.2);
            color: #ffc107;
        }
        
        .badge-completed {
            background: rgba(76, 175, 80, 0.2);
            color: #4caf50;
        }
        
        .chat-container {
            display: flex;
            flex-direction: column;
            height: 600px;
        }
        
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .message {
            margin-bottom: 1rem;
            padding: 1rem;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .message-own {
            background: rgba(233, 69, 96, 0.2);
            margin-left: auto;
            border: 1px solid #e94560;
        }
        
        .message-other {
            background: rgba(255,255,255,0.05);
            margin-right: auto;
        }
        
        .message-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }
        
        .message-author {
            font-weight: bold;
            color: #e94560;
        }
        
        .message-time {
            color: #999;
        }
        
        .message-content {
            word-wrap: break-word;
        }
        
        .message-image {
            max-width: 100%;
            border-radius: 5px;
            margin-top: 0.5rem;
        }
        
        .message-file {
            display: inline-block;
            margin-top: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            color: #e94560;
            text-decoration: none;
        }
        
        .message-file:hover {
            background: rgba(255,255,255,0.2);
        }
        
        .chat-input-area {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .input-row {
            display: flex;
            gap: 0.5rem;
        }
        
        textarea {
            flex: 1;
            padding: 0.8rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 5px;
            background: rgba(255,255,255,0.05);
            color: #fff;
            resize: vertical;
            min-height: 60px;
            font-family: inherit;
        }
        
        textarea:focus {
            outline: none;
            border-color: #e94560;
        }
        
        .file-input-label {
            padding: 0.8rem 1rem;
            background: rgba(255,255,255,0.1);
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            transition: all 0.3s;
        }
        
        .file-input-label:hover {
            background: rgba(255,255,255,0.2);
        }
        
        input[type="file"] {
            display: none;
        }
        
        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 1rem;
            font-weight: bold;
        }
        
        .btn-primary {
            background: #e94560;
            color: white;
        }
        
        .btn-primary:hover {
            background: #c93750;
        }
        
        .btn-success {
            background: #4caf50;
            color: white;
        }
        
        .btn-success:hover {
            background: #45a049;
        }
        
        .actions {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
        
        .selected-file {
            font-size: 0.9rem;
            color: #ccc;
            padding: 0.5rem;
            background: rgba(0,0,0,0.2);
            border-radius: 5px;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: rgba(76, 175, 80, 0.2);
            border: 1px solid #4caf50;
            color: #4caf50;
        }
        
        .alert-error {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff0000;
            color: #ffcccc;
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="dashboard.php" class="back-btn">← Retour au dashboard</a>
        <h1>Commande #<?= $commande_id ?></h1>
    </header>
    
    <div class="container">
        <aside class="card">
            <h2>Détails de la commande</h2>
            
            <div class="info-group">
                <div class="info-label">Statut</div>
                <div class="info-value">
                    <span class="badge badge-<?= $commande['statut'] === 'terminee' ? 'completed' : 'pending' ?>">
                        <?= $commande['statut'] === 'terminee' ? 'Terminée' : 'En attente' ?>
                    </span>
                </div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Service</div>
                <div class="info-value"><?= e($commande['nom_service']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Quantité</div>
                <div class="info-value"><?= $commande['quantite'] ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Type</div>
                <div class="info-value"><?= ucfirst($commande['type_commande']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Date de création</div>
                <div class="info-value"><?= formatDate($commande['date_creation']) ?></div>
            </div>
            
            <?php if ($user['role'] !== 'client'): ?>
            <hr style="margin: 1rem 0; border-color: rgba(255,255,255,0.1);">
            
            <h3 style="color: #e94560; margin-bottom: 1rem;">Informations client</h3>
            
            <div class="info-group">
                <div class="info-label">Nom</div>
                <div class="info-value"><?= e($commande['prenom'] . ' ' . $commande['nom']) ?></div>
            </div>
            
            <div class="info-group">
                <div class="info-label">Email</div>
                <div class="info-value"><?= e($commande['email']) ?></div>
            </div>
            
            <?php if (!empty($commande['telephone'])): ?>
            <div class="info-group">
                <div class="info-label">Téléphone</div>
                <div class="info-value"><?= e($commande['telephone']) ?></div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
            
            <?php if (in_array($user['role'], ['personnel', 'direction']) && $commande['statut'] === 'en_attente'): ?>
            <div class="actions">
                <h3 style="color: #e94560; margin-bottom: 1rem;">Actions</h3>
                
                <form id="uploadFactureForm" style="margin-bottom: 1rem;">
                    <label class="file-input-label">
                        📄 Uploader une facture (obligatoire pour fermer)
                        <input type="file" id="factureInput" accept=".pdf" required>
                    </label>
                    <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 0.5rem;">
                        Uploader la facture
                    </button>
                </form>
                
                <button id="closeCommandeBtn" class="btn btn-success" style="width: 100%;" disabled>
                    ✓ Fermer la commande
                </button>
                
                <div id="actionAlert" style="margin-top: 1rem;"></div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($commande['facture_url'])): ?>
            <div class="actions">
                <h3 style="color: #e94560; margin-bottom: 1rem;">Facture</h3>
                <a href="<?= e($commande['facture_url']) ?>" target="_blank" class="btn btn-primary" style="width: 100%; text-align: center; display: block; text-decoration: none;">
                    📥 Télécharger la facture
                </a>
            </div>
            <?php endif; ?>
        </aside>
        
        <main class="card">
            <h2>💬 Discussion</h2>
            
            <div class="chat-container">
                <div class="messages-area" id="messagesArea"></div>
                
                <?php if ($commande['statut'] === 'en_attente'): ?>
                <div class="chat-input-area">
                    <div id="chatAlert"></div>
                    <div id="selectedFileInfo" class="selected-file" style="display: none;"></div>
                    <div class="input-row">
                        <textarea id="messageInput" placeholder="Tapez votre message..."></textarea>
                        <label class="file-input-label">
                            📎
                            <input type="file" id="fileInput" accept="image/*,.pdf,.doc,.docx">
                        </label>
                        <button id="sendBtn" class="btn btn-primary">Envoyer</button>
                    </div>
                </div>
                <?php else: ?>
                <div class="alert alert-success">
                    Cette commande est terminée. La discussion est fermée.
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
    
    <script>
        const commandeId = <?= $commande_id ?>;
        const userId = <?= $user['id'] ?>;
        let lastMessageId = 0;
        let selectedFile = null;
        let factureUploaded = <?= !empty($commande['facture_url']) ? 'true' : 'false' ?>;
        
        // Gestion de la sélection de fichier pour le chat
        const fileInput = document.getElementById('fileInput');
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                selectedFile = e.target.files[0];
                const fileInfo = document.getElementById('selectedFileInfo');
                if (selectedFile) {
                    fileInfo.textContent = `📎 Fichier sélectionné: ${selectedFile.name}`;
                    fileInfo.style.display = 'block';
                } else {
                    fileInfo.style.display = 'none';
                }
            });
        }
        
        // Envoyer un message
        const sendBtn = document.getElementById('sendBtn');
        if (sendBtn) {
            sendBtn.addEventListener('click', sendMessage);
        }
        
        const messageInput = document.getElementById('messageInput');
        if (messageInput) {
            messageInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && e.ctrlKey) {
                    sendMessage();
                }
            });
        }
        
        async function sendMessage() {
            const messageInput = document.getElementById('messageInput');
            const message = messageInput.value.trim();
            
            if (!message && !selectedFile) {
                return;
            }
            
            const formData = new FormData();
            formData.append('commande_id', commandeId);
            formData.append('message', message);
            
            if (selectedFile) {
                formData.append('fichier', selectedFile);
            }
            
            try {
                const response = await fetch('api/chat.php?action=send', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    messageInput.value = '';
                    selectedFile = null;
                    const fileInputElem = document.getElementById('fileInput');
                    if (fileInputElem) fileInputElem.value = '';
                    document.getElementById('selectedFileInfo').style.display = 'none';
                    loadMessages();
                } else {
                    showAlert('chatAlert', data.error, 'error');
                }
            } catch (error) {
                showAlert('chatAlert', 'Erreur lors de l\'envoi du message', 'error');
            }
        }
        
        // Charger les messages
        async function loadMessages() {
            try {
                const response = await fetch(`api/chat.php?action=get&commande_id=${commandeId}&since=${lastMessageId}`);
                const data = await response.json();
                
                if (data.success && data.messages.length > 0) {
                    const messagesArea = document.getElementById('messagesArea');
                    
                    data.messages.forEach(msg => {
                        const messageDiv = document.createElement('div');
                        messageDiv.className = `message ${msg.user_id == userId ? 'message-own' : 'message-other'}`;
                        
                        let content = `
                            <div class="message-header">
                                <span class="message-author">${escapeHtml(msg.prenom)} ${escapeHtml(msg.nom)} (${escapeHtml(msg.role)})</span>
                                <span class="message-time">${new Date(msg.date_envoi).toLocaleString('fr-FR')}</span>
                            </div>
                            <div class="message-content">${escapeHtml(msg.message || '')}</div>
                        `;
                        
                        if (msg.type_message === 'image' && msg.fichier_url) {
                            content += `<img src="${escapeHtml(msg.fichier_url)}" class="message-image" alt="Image">`;
                        } else if (msg.fichier_url) {
                            content += `<a href="${escapeHtml(msg.fichier_url)}" class="message-file" target="_blank">📎 ${escapeHtml(msg.fichier_nom)}</a>`;
                        }
                        
                        messageDiv.innerHTML = content;
                        messagesArea.appendChild(messageDiv);
                        
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    });
                    
                    messagesArea.scrollTop = messagesArea.scrollHeight;
                }
            } catch (error) {
                console.error('Erreur lors du chargement des messages:', error);
            }
        }
        
        // Upload de facture
        const uploadFactureForm = document.getElementById('uploadFactureForm');
        if (uploadFactureForm) {
            uploadFactureForm.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                const factureInput = document.getElementById('factureInput');
                const file = factureInput.files[0];
                
                if (!file) {
                    showAlert('actionAlert', 'Veuillez sélectionner une facture', 'error');
                    return;
                }
                
                const formData = new FormData();
                formData.append('commande_id', commandeId);
                formData.append('facture', file);
                
                try {
                    const response = await fetch('api/chat.php?action=upload_facture', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showAlert('actionAlert', 'Facture uploadée avec succès !', 'success');
                        factureUploaded = true;
                        document.getElementById('closeCommandeBtn').disabled = false;
                        loadMessages();
                    } else {
                        showAlert('actionAlert', data.error, 'error');
                    }
                } catch (error) {
                    showAlert('actionAlert', 'Erreur lors de l\'upload', 'error');
                }
            });
        }
        
        // Fermer la commande
        const closeCommandeBtn = document.getElementById('closeCommandeBtn');
        if (closeCommandeBtn) {
            closeCommandeBtn.addEventListener('click', async function() {
                if (!factureUploaded) {
                    showAlert('actionAlert', 'Veuillez d\'abord uploader une facture', 'error');
                    return;
                }
                
                if (!confirm('Êtes-vous sûr de vouloir fermer cette commande ?')) {
                    return;
                }
                
                try {
                    const formData = new FormData();
                    formData.append('commande_id', commandeId);
                    
                    const response = await fetch('api/commandes.php?action=close', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        showAlert('actionAlert', 'Commande fermée avec succès !', 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showAlert('actionAlert', data.error, 'error');
                    }
                } catch (error) {
                    showAlert('actionAlert', 'Erreur lors de la fermeture', 'error');
                }
            });
        }
        
        function showAlert(elementId, message, type) {
            const alertDiv = document.getElementById(elementId);
            if (!alertDiv) return;
            
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            alertDiv.style.display = 'block';
            
            setTimeout(() => {
                alertDiv.style.display = 'none';
            }, 5000);
        }
        
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text || '').replace(/[&<>"']/g, m => map[m]);
        }
        
        // Charger les messages au démarrage
        loadMessages();
        
        // Polling pour les nouveaux messages (toutes les 3 secondes)
        <?php if ($commande['statut'] === 'en_attente'): ?>
        setInterval(loadMessages, 3000);
        <?php endif; ?>
        
        // Activer le bouton de fermeture si une facture existe déjà
        if (factureUploaded && document.getElementById('closeCommandeBtn')) {
            document.getElementById('closeCommandeBtn').disabled = false;
        }
    </script>
</body>
</html>