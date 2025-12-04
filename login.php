<?php
require 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = "Inserisci email e password.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']   = $user['id'];
            $_SESSION['username']  = $user['username'];
            header("Location: profile.php");
            exit;
        } else {
            $errors[] = "Credenziali non valide.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Login - MySpace Clone</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-box">
        <h1>MySpace Clone</h1>
        <h2>Accedi</h2>

        <?php if (isset($_GET['registered'])): ?>
            <div class="success">Registrazione completata! Ora puoi accedere.</div>
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

            <button type="submit">Entra</button>
            <p class="switch-auth">Non hai un account? <a href="register.php">Registrati</a></p>
        </form>
    </div>
</body>
</html>
