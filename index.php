<?php
require_once 'config.php';

// Si l'utilisateur est connecté, rediriger vers son dashboard
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weazel News - Accueil</title>
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
        }
        
        .header {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            color: #e94560;
            text-decoration: none;
        }
        
        .nav-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn {
            padding: 0.6rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            text-decoration: none;
            transition: all 0.3s;
            display: inline-block;
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
            background: transparent;
            color: white;
            border: 2px solid #e94560;
        }
        
        .btn-secondary:hover {
            background: #e94560;
            transform: translateY(-2px);
        }
        
        .hero {
            text-align: center;
            padding: 3rem 2rem;
            background: linear-gradient(135deg, #16213e 0%, #0f3460 100%);
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: #e94560;
        }
        
        .hero p {
            font-size: 1.2rem;
            color: #ccc;
            max-width: 800px;
            margin: 0 auto;
        }
        
        .catalogue-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .catalogue-frame {
            width: 100%;
            height: 800px;
            border: none;
            border-radius: 10px;
            background: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 3rem auto;
            padding: 2rem;
        }
        
        .feature-card {
            background: #16213e;
            padding: 2rem;
            border-radius: 10px;
            text-align: center;
            transition: transform 0.3s;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .feature-card h3 {
            color: #e94560;
            margin-bottom: 1rem;
        }
        
        .footer {
            background: #16213e;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">Weazel News</a>
            <div class="nav-buttons">
                <a href="login.php" class="btn btn-secondary">Connexion</a>
                <a href="register.php" class="btn btn-primary">Inscription</a>
            </div>
        </nav>
    </header>

    <section class="hero">
        <h1>Bienvenue chez Weazel News</h1>
        <p>Découvrez notre catalogue de services et passez vos commandes en toute simplicité</p>
    </section>

    <section class="catalogue-container">
        <h2 style="text-align: center; margin-bottom: 2rem; color: #e94560;">Notre Catalogue</h2>
        <iframe 
            src="https://catalogue-weazel-news-innocent.weazelnewsinnocent.workers.dev/" 
            class="catalogue-frame"
            title="Catalogue Weazel News">
        </iframe>
    </section>

    <section class="features">
        <div class="feature-card">
            <h3>🚀 Commandes Rapides</h3>
            <p>Passez vos commandes en quelques clics et suivez leur progression en temps réel</p>
        </div>
        <div class="feature-card">
            <h3>💬 Chat Direct</h3>
            <p>Communiquez directement avec notre équipe pour toutes vos questions</p>
        </div>
        <div class="feature-card">
            <h3>📄 Factures Automatiques</h3>
            <p>Recevez et téléchargez vos factures directement depuis votre espace</p>
        </div>
        <div class="feature-card">
            <h3>🔒 Sécurisé</h3>
            <p>Vos données sont protégées et sécurisées</p>
        </div>
    </section>

    <footer class="footer">
        <p>&copy; 2024 Weazel News - Tous droits réservés</p>
    </footer>
</body>
</html>