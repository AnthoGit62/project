<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
$stats = getDashboardStats($user['id'], $user['role']);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Weazel News</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0a0a0a;
            color: #fff;
            display: flex;
            min-height: 100vh;
        }
        
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, #16213e 0%, #0f3460 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
        }
        
        .sidebar-header {
            padding: 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: #e94560;
        }
        
        .user-info {
            padding: 1.5rem;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .user-name {
            font-weight: bold;
            margin-bottom: 0.3rem;
        }
        
        .user-role {
            font-size: 0.85rem;
            color: #e94560;
            text-transform: uppercase;
        }
        
        .nav-menu {
            flex: 1;
            padding: 1rem 0;
        }
        
        .nav-item {
            display: block;
            padding: 1rem 1.5rem;
            color: #fff;
            text-decoration: none;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .nav-item:hover, .nav-item.active {
            background: rgba(233, 69, 96, 0.1);
            border-left-color: #e94560;
        }
        
        .nav-item.active {
            background: rgba(233, 69, 96, 0.2);
        }
        
        .logout-btn {
            margin: 1rem 1.5rem;
            padding: 0.8rem;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: block;
            text-align: center;
        }
        
        .logout-btn:hover {
            background: #c93750;
        }
        
        .main-content {
            flex: 1;
            padding: 2rem;
            overflow-y: auto;
        }
        
        .header {
            margin-bottom: 2rem;
        }
        
        .header h1 {
            color: #e94560;
            margin-bottom: 0.5rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #16213e 0%, #0f3460 100%);
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        }
        
        .stat-label {
            color: #ccc;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
            font-weight: bold;
            color: #e94560;
        }
        
        .content-section {
            background: linear-gradient(135deg, #16213e 0%, #0f3460 100%);
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            display: none;
        }
        
        .content-section.active {
            display: block;
        }
        
        .btn-primary {
            padding: 0.8rem 1.5rem;
            background: #e94560;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .btn-primary:hover {
            background: #c93750;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        th {
            background: rgba(0,0,0,0.3);
            padding: 1rem;
            text-align: left;
            color: #e94560;
        }
        
        td {
            padding: 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .badge {
            padding: 0.3rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
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
        
        .tools-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 2rem;
        }
        
        .tool-card {
            background: rgba(0,0,0,0.3);
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .tool-card h3 {
            color: #e94560;
            margin-bottom: 1rem;
        }
        
        .tool-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.8rem 1.5rem;
            background: #e94560;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .tool-link:hover {
            background: #c93750;
            transform: translateY(-2px);
        }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <div class="logo">Weazel News</div>
        </div>
        
        <div class="user-info">
            <div class="user-name"><?= e($user['prenom'] . ' ' . $user['nom']) ?></div>
            <div class="user-role"><?= e($user['role']) ?></div>
        </div>
        
        <nav class="nav-menu">
            <a href="#dashboard" class="nav-item active" onclick="showSection('dashboard')">📊 Tableau de bord</a>
            <a href="#commandes" class="nav-item" onclick="showSection('commandes')">📦 Mes commandes</a>
            
            <?php if ($user['role'] === 'client'): ?>
            <a href="#nouvelle-commande" class="nav-item" onclick="showSection('nouvelle-commande')">➕ Nouvelle commande</a>
            <?php endif; ?>
            
            <?php if (in_array($user['role'], ['personnel', 'direction'])): ?>
            <a href="#toutes-commandes" class="nav-item" onclick="showSection('toutes-commandes')">📋 Toutes les commandes</a>
            <a href="#outils" class="nav-item" onclick="showSection('outils')">🛠️ Outils</a>
            <?php endif; ?>
            
            <?php if ($user['role'] === 'direction'): ?>
            <a href="#gestion-personnel" class="nav-item" onclick="showSection('gestion-personnel')">👥 Gestion Personnel</a>
            <?php endif; ?>
        </nav>
        
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </aside>
    
    <main class="main-content">
        <!-- Section Dashboard -->
        <div id="dashboard" class="content-section active">
            <div class="header">
                <h1>Bienvenue, <?= e($user['prenom']) ?> !</h1>
                <p>Voici un aperçu de votre activité</p>
            </div>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Commandes en attente</div>
                    <div class="stat-value"><?= $stats['en_attente'] ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Commandes terminées</div>
                    <div class="stat-value"><?= $stats['terminees'] ?></div>
                </div>
                <?php if ($user['role'] === 'direction'): ?>
                <div class="stat-card">
                    <div class="stat-label">Membres du personnel</div>
                    <div class="stat-value"><?= $stats['personnel'] ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Section Commandes -->
        <div id="commandes" class="content-section">
            <div class="header">
                <h1>Mes commandes</h1>
            </div>
            <div id="commandes-content">
                <p>Chargement...</p>
            </div>
        </div>
        
        <!-- Section Nouvelle Commande -->
        <?php if ($user['role'] === 'client'): ?>
        <div id="nouvelle-commande" class="content-section">
            <div class="header">
                <h1>Nouvelle commande</h1>
            </div>
            <div id="nouvelle-commande-content">
                <!-- Le contenu sera chargé via AJAX -->
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Toutes les commandes (Personnel/Direction) -->
        <?php if (in_array($user['role'], ['personnel', 'direction'])): ?>
        <div id="toutes-commandes" class="content-section">
            <div class="header">
                <h1>Toutes les commandes</h1>
            </div>
            <div id="toutes-commandes-content">
                <p>Chargement...</p>
            </div>
        </div>
        
        <div id="outils" class="content-section">
            <div class="header">
                <h1>Outils de gestion</h1>
            </div>
            <div class="tools-section">
                <div class="tool-card">
                    <h3>📄 Génération de Factures</h3>
                    <p>Créez des factures professionnelles en PDF</p>
                    <a href="https://generation-facture.weazelnewsinnocent.workers.dev/" target="_blank" class="tool-link">Ouvrir l'outil</a>
                </div>
                <div class="tool-card">
                    <h3>📋 Génération de Devis</h3>
                    <p>Créez des devis détaillés pour vos clients</p>
                    <a href="https://generation-devis.weazelnewsinnocent.workers.dev/" target="_blank" class="tool-link">Ouvrir l'outil</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Section Gestion Personnel (Direction) -->
        <?php if ($user['role'] === 'direction'): ?>
        <div id="gestion-personnel" class="content-section">
            <div class="header">
                <h1>Gestion du personnel</h1>
                <button class="btn-primary" onclick="showAddPersonnelModal()">➕ Ajouter un membre</button>
            </div>
            <div id="gestion-personnel-content">
                <p>Chargement...</p>
            </div>
        </div>
        <?php endif; ?>
    </main>
    
    <script src="js/dashboard.js"></script>
    <script>
        // Passer le rôle de l'utilisateur au JavaScript
        document.body.dataset.userRole = '<?= $user['role'] ?>';
        
        function showSection(sectionId) {
            // Cacher toutes les sections
            document.querySelectorAll('.content-section').forEach(section => {
                section.classList.remove('active');
            });
            
            // Retirer la classe active de tous les liens
            document.querySelectorAll('.nav-item').forEach(item => {
                item.classList.remove('active');
            });
            
            // Afficher la section demandée
            document.getElementById(sectionId).classList.add('active');
            
            // Ajouter la classe active au lien cliqué
            event.target.classList.add('active');
            
            // Charger le contenu si nécessaire
            loadSectionContent(sectionId);
        }
        
        function loadSectionContent(sectionId) {
            switch(sectionId) {
                case 'commandes':
                case 'toutes-commandes':
                    if (commandesData.length === 0) {
                        loadCommandes();
                    } else {
                        updateCommandesDisplay();
                    }
                    break;
                case 'nouvelle-commande':
                    renderNouvelleCommande();
                    break;
                case 'gestion-personnel':
                    if (personnelData.length === 0) {
                        loadPersonnel();
                    } else {
                        updatePersonnelDisplay();
                    }
                    break;
            }
        }
        
        // Charger le contenu initial
        document.addEventListener('DOMContentLoaded', function() {
            loadInitialData();
        });
    </script>
</body>
</html>