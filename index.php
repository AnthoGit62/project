<?php
require_once 'config.php';

// Si l'utilisateur est connecté, rediriger vers son dashboard
if (isLoggedIn()) {
    // Vérifier si on vient du panier
    if (isset($_POST['panier_data'])) {
        // Traiter la commande
        header('Location: traiter_commande.php');
        exit;
    }
    header('Location: dashboard.php');
    exit;
}

$showLoginModal = isset($_GET['commande']) && $_GET['commande'] == 'required';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weazel News - Catalogue & Commandes</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a1a 0%, #2a2a2a 100%);
            color: #e0e0e0;
            min-height: 100vh;
            padding-top: 60px;
        }

        /* Header fixe */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.95);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
            z-index: 1000;
            backdrop-filter: blur(10px);
        }
        
        .nav-container {
            max-width: 1400px;
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
            align-items: center;
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

        /* Modal de connexion */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.9);
            z-index: 2000;
            align-items: center;
            justify-content: center;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            background: #16213e;
            padding: 2rem;
            border-radius: 15px;
            max-width: 500px;
            width: 90%;
            position: relative;
        }

        .modal-close {
            position: absolute;
            top: 1rem;
            right: 1rem;
            background: none;
            border: none;
            color: #fff;
            font-size: 1.5rem;
            cursor: pointer;
        }

        .modal h2 {
            color: #e94560;
            margin-bottom: 1.5rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #ccc;
        }

        .form-group input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 5px;
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 1rem;
        }

        .form-group input:focus {
            outline: none;
            border-color: #e94560;
        }

        /* Catalogue intégré */
        .catalogue-container {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        iframe {
            width: 100%;
            min-height: 90vh;
            border: none;
            border-radius: 10px;
            background: white;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">📺 WEAZEL NEWS</a>
            <div class="nav-buttons">
                <a href="login.php" class="btn btn-secondary">Connexion</a>
                <a href="register.php" class="btn btn-primary">Inscription</a>
            </div>
        </nav>
    </header>

    <div class="catalogue-container">
        <iframe src="catalogue.html" id="catalogueFrame"></iframe>
    </div>

    <!-- Modal de connexion -->
    <div class="modal <?= $showLoginModal ? 'active' : '' ?>" id="loginModal">
        <div class="modal-content">
            <button class="modal-close" onclick="closeLoginModal()">×</button>
            <h2>Connexion requise</h2>
            <p style="margin-bottom: 1.5rem; color: #ccc;">
                Vous devez être connecté pour passer commande.
            </p>
            <div style="display: flex; gap: 1rem;">
                <a href="login.php" class="btn btn-primary" style="flex: 1; text-align: center;">
                    Se connecter
                </a>
                <a href="register.php" class="btn btn-secondary" style="flex: 1; text-align: center;">
                    S'inscrire
                </a>
            </div>
        </div>
    </div>

    <script>
        // Écouter les messages du catalogue
        window.addEventListener('message', function(event) {
            // Vérifier l'origine du message
            if (event.data.action === 'commander') {
                // L'utilisateur veut passer commande
                const panier = event.data.panier;
                
                // Stocker le panier dans localStorage
                localStorage.setItem('weazel_panier', JSON.stringify(panier));
                
                // Rediriger vers la page de traitement
                window.location.href = 'traiter_commande.php';
            }
        });

        function closeLoginModal() {
            document.getElementById('loginModal').classList.remove('active');
        }

        // Fermer la modal si on clique en dehors
        document.getElementById('loginModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeLoginModal();
            }
        });
    </script>
</body>
</html>
