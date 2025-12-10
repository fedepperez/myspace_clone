<?php
require 'config.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    if ($username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = "Compila tutti i campi.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Email non valida.";
    }

    if ($password !== $confirm) {
        $errors[] = "Le password non coincidono.";
    }

    if (empty($errors)) {
        // Controlla se l'utente o l'email esistono già
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);

        if ($stmt->fetch()) {
            $errors[] = "Username o email già in uso.";
        } else {
            // Hash della password e inserimento nel database
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                "INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)"
            );
            $stmt->execute([$username, $email, $hash]);

            // Reindirizzamento al login con messaggio di successo
            header("Location: login.php?registered=1");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrati - MySpace Clone</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="auth-page">
    <div class="auth-box">
        <h1>MySpace Clone</h1>
        <h2>Crea il tuo profilo</h2>

        <p style="font-size: 0.85rem; color: #f0d28a; background: rgba(255, 193, 7, 0.08); border: 1px solid rgba(255, 193, 7, 0.4); padding: 0.5rem 0.7rem; border-radius: 6px; margin-top: 0.8rem;">
            Questo è un progetto di test.
            <strong>Non usare mai password che utilizzi su altri siti</strong> e, se vuoi solo provare il sito,
            puoi inserire anche un'email fittizia (ad esempio <code>prova@example.com</code>).
        </p>

        <?php if (!empty($errors)): ?>
            <div class="errors">
                <?php foreach ($errors as $e): ?>
                    <p><?= htmlspecialchars($e) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <label>
                Username
                <input type="text" name="username" required>
            </label>

            <label>
                Email
                <input type="email" name="email" required>
            </label>

            <label>
                Password
                <input type="password" name="password" required>
            </label>

            <label>
                Conferma password
                <input type="password" name="confirm" required>
            </label>

            <button type="submit">Registrati</button>
            <p class="switch-auth">Hai già un account? <a href="login.php">Accedi</a></p>
        </form>
    </div>
</body>

</html>