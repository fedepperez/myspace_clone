<?php
require 'config.php';

// Se l'utente è già loggato, reindirizza alla home
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Inserisci email e password.";
    }

    if (empty($errors)) {
        // Cerca l'utente per email
        $stmt = $pdo->prepare("SELECT id, username, password_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // Login riuscito
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            header("Location: home.php");
            exit;
        } else {
            $errors[] = "Email o password non corretti.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accedi - MySpace Clone</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">
    <div class="auth-box">
        <h1>MySpace Clone</h1>
        <h2>Accedi al tuo profilo</h2>

        <?php if (isset($_GET['registered'])): ?>
            <div class="success">
                <p>Registrazione completata con successo! Ora puoi accedere.</p>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['deleted'])): ?>
            <div class="success">
                <p>Profilo eliminato con successo. Ci dispiace vederti andare via!</p>
            </div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>
                Email
                <input type="email" name="email" required>
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <button type="submit">Accedi</button>
            <p class="switch-auth">Non hai un account? <a href="register.php">Registrati</a></p>
        </form>
    </div>
</body>

</html>