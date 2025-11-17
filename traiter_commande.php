<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$error = '';
$success = false;

// Traiter la commande si POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $panier = json_decode($_POST['panier_data'], true);
        
        if (empty($panier)) {
            throw new Exception('Le panier est vide');
        }
        
        $db = getDB();
        $db->beginTransaction();
        
        // Créer une commande pour chaque service
        $stmt = $db->prepare("
            INSERT INTO commandes (user_id, nom_service, prix, quantite, type_commande, statut) 
            VALUES (?, ?, ?, 1, 'catalogue', 'en_attente')
        ");
        
        $commandes_ids = [];
        
        foreach ($panier as $item) {
            $stmt->execute([
                $user['id'],
                $item['name'],
                $item['price']
            ]);
            $commandes_ids[] = $db->lastInsertId();
        }
        
        $db->commit();
        $success = true;
        
    } catch (Exception $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Commander - Weazel News</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            min-height: 100vh;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .container {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border-radius: 15px;
            max-width: 800px;
            width: 100%;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
        }
        
        h1 {
            color: #e94560;
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .panier-recap {
            background: rgba(0,0,0,0.3);
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .panier-item {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .panier-item:last-child {
            border-bottom: none;
        }
        
        .item-name {
            font-weight: bold;
            color: #e94560;
        }
        
        .item-price {
            color: #4caf50;
            font-weight: bold;
        }
        
        .total {
            background: #e94560;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            margin: 2rem 0;
        }
        
        .total-label {
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .total-amount {
            font-size: 2.5rem;
            font-weight: bold;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ccc;
        }
        
        select {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 5px;
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 1rem;
        }
        
        .btn-group {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        .btn {
            flex: 1;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #e94560;
            color: white;
        }
        
        .btn-primary:hover {
            background: #c93750;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #666;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #555;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            text-align: center;
        }
        
        .alert-error {
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff0000;
            color: #ffcccc;
        }
        
        .alert-success {
            background: rgba(0, 255, 0, 0.2);
            border: 1px solid #00ff00;
            color: #ccffcc;
        }
        
        .success-container {
            text-align: center;
        }
        
        .success-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }
        
        .success-message {
            font-size: 1.5rem;
            margin-bottom: 2rem;
            color: #4caf50;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if ($success): ?>
            <div class="success-container">
                <div class="success-icon">✅</div>
                <h1>Commande validée !</h1>
                <p class="success-message">Votre commande a été créée avec succès.</p>
                <p style="color: #ccc; margin-bottom: 2rem;">
                    Vous pouvez suivre son évolution dans votre dashboard et discuter avec notre équipe.
                </p>
                <a href="dashboard.php" class="btn btn-primary" style="display: inline-block; text-decoration: none;">
                    Voir mes commandes
                </a>
            </div>
        <?php else: ?>
            <h1>Finaliser ma commande</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <div class="panier-recap">
                <h2 style="color: #e94560; margin-bottom: 1rem;">📋 Récapitulatif</h2>
                <div id="panierItems"></div>
            </div>
            
            <div class="total">
                <div class="total-label">MONTANT TOTAL</div>
                <div class="total-amount" id="totalAmount">0$</div>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="panier_data" id="panierData">
                
                <div class="form-group">
                    <label>Type de commande</label>
                    <select name="type_commande" required>
                        <option value="">-- Sélectionnez --</option>
                        <option value="personnel">Personnel</option>
                        <option value="professionnel">Professionnel</option>
                    </select>
                </div>
                
                <div class="btn-group">
                    <a href="index.php" class="btn btn-secondary">← Retour au catalogue</a>
                    <button type="submit" class="btn btn-primary">Valider la commande ✓</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
    
    <script>
        // Récupérer le panier du localStorage
        const panier = JSON.parse(localStorage.getItem('weazel_panier') || '{}');
        
        if (Object.keys(panier).length === 0) {
            window.location.href = 'index.php';
        } else {
            // Afficher les items
            let html = '';
            let total = 0;
            
            for (const [key, item] of Object.entries(panier)) {
                html += `
                    <div class="panier-item">
                        <div class="item-name">${escapeHtml(item.name)}</div>
                        <div class="item-price">${item.price.toLocaleString()}$</div>
                    </div>
                `;
                total += item.price;
            }
            
            document.getElementById('panierItems').innerHTML = html;
            document.getElementById('totalAmount').textContent = total.toLocaleString() + '$';
            document.getElementById('panierData').value = JSON.stringify(panier);
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
        
        // Nettoyer le localStorage après validation
        <?php if ($success): ?>
        localStorage.removeItem('weazel_panier');
        <?php endif; ?>
    </script>
</body>
</html>
