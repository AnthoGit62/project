<?php
require_once 'config.php';

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $telephone = trim($_POST['telephone'] ?? '');
    $lieu_habitation = trim($_POST['lieu_habitation'] ?? '');
    $profession = trim($_POST['profession'] ?? '');
    
    // Validation
    if (empty($nom) || empty($prenom) || empty($email) || empty($password)) {
        $error = "Tous les champs obligatoires doivent être remplis";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email invalide";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères";
    } elseif ($password !== $password_confirm) {
        $error = "Les mots de passe ne correspondent pas";
    } else {
        try {
            $db = getDB();
            
            // Vérifier si l'email existe déjà
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = "Cet email est déjà utilisé";
            } else {
                // Insérer le nouvel utilisateur
                $stmt = $db->prepare("
                    INSERT INTO users (nom, prenom, email, password, telephone, lieu_habitation, profession, role) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'client')
                ");
                
                $hashedPassword = hashPassword($password);
                $stmt->execute([$nom, $prenom, $email, $hashedPassword, $telephone, $lieu_habitation, $profession]);
                
                $success = "Inscription réussie ! Vous pouvez maintenant vous connecter.";
            }
        } catch (PDOException $e) {
            $error = "Erreur lors de l'inscription : " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Weazel News</title>
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
        
        .register-box {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.5);
            width: 100%;
            max-width: 600px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        
        h2 {
            color: #e94560;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        label {
            display: block;
            color: #fff;
            margin-bottom: 0.5rem;
            font-size: 0.9rem;
        }
        
        .required {
            color: #e94560;
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
            margin-top: 1rem;
        }
        
        .btn:hover {
            background: #c93750;
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
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
        
        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: #ccc;
        }
        
        .login-link a {
            color: #e94560;
            text-decoration: none;
        }
        
        .login-link a:hover {
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
        <div class="register-box">
            <h2>Créer un compte</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-row">
                    <div class="form-group">
                        <label>Nom <span class="required">*</span></label>
                        <input type="text" name="nom" required value="<?= e($_POST['nom'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Prénom <span class="required">*</span></label>
                        <input type="text" name="prenom" required value="<?= e($_POST['prenom'] ?? '') ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Email <span class="required">*</span></label>
                    <input type="email" name="email" required value="<?= e($_POST['email'] ?? '') ?>">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Mot de passe <span class="required">*</span></label>
                        <input type="password" name="password" required minlength="6">
                    </div>
                    <div class="form-group">
                        <label>Confirmer <span class="required">*</span></label>
                        <input type="password" name="password_confirm" required minlength="6">
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Profession</label>
                    <input type="text" name="profession" value="<?= e($_POST['profession'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Lieu d'habitation</label>
                    <input type="text" name="lieu_habitation" value="<?= e($_POST['lieu_habitation'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label>Téléphone</label>
                    <input type="tel" name="telephone" value="<?= e($_POST['telephone'] ?? '') ?>">
                </div>
                
                <button type="submit" class="btn">S'inscrire</button>
            </form>
            
            <div class="login-link">
                Déjà un compte ? <a href="login.php">Se connecter</a>
            </div>
        </div>
    </div>
</body>
</html>