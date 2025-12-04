<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bio   = $_POST['bio'] ?? '';
    $color = $_POST['profile_color'] ?? '#222222';

    $stmt = $pdo->prepare("UPDATE users SET bio = ?, profile_color = ? WHERE id = ?");
    $stmt->execute([$bio, $color, $user_id]);
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utente non trovato.");
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>Profilo di <?= htmlspecialchars($user['username']) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="profile-page" style="--profile-color: <?= htmlspecialchars($user['profile_color']) ?>;">
    <header class="topbar"> 
    <h1>MySpace Clone</h1>
    <nav>
        <span>Ciao, <?= htmlspecialchars($user['username']) ?></span>
        <a href="logout.php">Logout</a>
        <a href="delete_account.php" 
           onclick="return confirm('Vuoi davvero eliminare definitivamente il tuo profilo?');"
           style="color: #ff6b6b;">
           Elimina account
        </a>
    </nav>
</header>


    <main class="profile-container">
        <section class="profile-card">
            <h2><?= htmlspecialchars($user['username']) ?></h2>
            <p class="bio">
                <?= nl2br(htmlspecialchars($user['bio'] ?? 'Scrivi qualcosa su di te...')) ?>
            </p>
        </section>

        <section class="profile-edit">
            <h3>Personalizza il tuo spazio</h3>
            <form method="post">
                <label>
                    Bio
                    <textarea name="bio" rows="5"><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                </label>

                <label>
                    Colore del tema
                    <input type="color" name="profile_color" value="<?= htmlspecialchars($user['profile_color']) ?>">
                </label>

                <button type="submit">Salva</button>
            </form>
        </section>
    </main>
</body>
</html>
