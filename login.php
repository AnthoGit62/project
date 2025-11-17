<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = "Tous les champs sont obligatoires";
    } else {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND actif = 1");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
            
            if ($user && verifyPassword($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['user_nom'] = $user['nom'];
                $_SESSION['user_prenom'] = $user['prenom'];
                
                header('Location: dashboard.php');
                exit;
            } else {
                $error = "Email ou mot de passe incorrect";
            }
        } catch (PDOException $e) {
            $error = "Erreur de connexion : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Weazel News</title>
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
            display: flex;
            flex-direction: column;
        }
        
        .header {
            background: rgba(22, 33, 62, 0.8);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.5);
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
        
        .container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }
        
        .login-box {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 400px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        h2 {
            color: #e94560;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        label {
            display: block;
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        input {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 5px;
            background: rgba(255,255,255,0.05);
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s;
        }
        
        input:focus {
            outline: none;
            border-color: #e94560;
            background: rgba(255,255,255,0.1);
        }
        
        .btn {
            width: 100%;
            padding: 1rem;
            border: none;
            border-radius: 5px;
            background: #e94560;
            color: white;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #c93750;
            transform: translateY(-2px);
        }
        
        .alert-error {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
            background: rgba(255, 0, 0, 0.2);
            border: 1px solid #ff0000;
            color: #ffcccc;
        }
        
        .register-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #ccc;
        }
        
        .register-link a {
            color: #e94560;
            text-decoration: none;
        }
        
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <a href="index.php" class="logo">Weazel News</a>
        </nav>
    </header>

    <div class="container">
        <div class="login-box">
            <h2>Connexion</h2>
            
            <?php if ($error): ?>
                <div class="alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Se connecter</button>
            </form>
            
            <div class="register-link">
                Pas encore de compte ? <a href="register.php">S'inscrire</a>
            </div>
        </div>
    </div>
</body>
</html>