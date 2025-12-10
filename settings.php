<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$settingsError = '';
$settingsSuccess = '';

/* ===== Gestione form impostazioni ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword     = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Prendo l'hash attuale
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
            $settingsError = "La password attuale non è corretta.";
        } elseif (strlen($newPassword) < 6) {
            $settingsError = "La nuova password deve avere almeno 6 caratteri.";
        } elseif ($newPassword !== $confirmPassword) {
            $settingsError = "La nuova password e la conferma non coincidono.";
        } else {
            // Aggiorna la password
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
            $stmt->execute([$newHash, $user_id]);
            $settingsSuccess = "Password aggiornata con successo.";
        }
    } elseif ($formType === 'delete_account') {
        // Elimina like e post dell'utente (se non hai ON DELETE CASCADE sul DB)
        $stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ?");
        $stmt->execute([$user_id]);

        $stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Elimina utente
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);

        // Logout e redirect al login
        session_destroy();
        header("Location: login.php?deleted=1");
        exit;
    }
}

/* ===== Dati utente corrente ===== */
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$currentUser = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$currentUser) {
    die("Utente non trovato.");
}
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni account - MySpace Clone</title>
    <link rel="stylesheet" href="style.css">
</head>

<body class="profile-page" style="--profile-color: <?= htmlspecialchars($currentUser['profile_color'] ?? '#222222') ?>;">
    <header class="topbar">
        <a href="home.php" class="topbar-logo">MySpace Clone</a>

        <nav>
            <span class="nav-username">
                Ciao, <?= htmlspecialchars($currentUser['username']) ?>
            </span>
            <a href="home.php">Home</a>
            <a href="profile.php">Profilo</a>
            <a href="settings.php">Impostazioni</a>
            <a href="logout.php">Esci</a>
        </nav>
    </header>

    <main class="profile-shell">
        <section class="profile-main-card" style="grid-column: 1 / -1;">
            <h2>Impostazioni account</h2>
            <p class="profile-edit-hint">
                Qui puoi cambiare la password o eliminare definitivamente il tuo profilo.
            </p>

            <?php if ($settingsError): ?>
                <p class="settings-message settings-message-error">
                    <?= htmlspecialchars($settingsError) ?>
                </p>
            <?php elseif ($settingsSuccess): ?>
                <p class="settings-message settings-message-success">
                    <?= htmlspecialchars($settingsSuccess) ?>
                </p>
            <?php endif; ?>

            <div class="settings-section">
                <h3>Cambia password</h3>
                <form method="post" class="settings-form">
                    <input type="hidden" name="form_type" value="change_password">

                    <label>
                        Password attuale
                        <input type="password" name="current_password" required>
                    </label>

                    <label>
                        Nuova password
                        <input type="password" name="new_password" required>
                    </label>

                    <label>
                        Conferma nuova password
                        <input type="password" name="confirm_password" required>
                    </label>

                    <button type="submit" class="settings-save-btn">
                        Cambia password
                    </button>
                </form>
            </div>

            <div class="settings-section">
                <h3>Elimina il profilo</h3>
                <p class="settings-hint">
                    Questa operazione eliminerà il tuo account, i tuoi post e i tuoi like.
                    Non potrà essere annullata.
                </p>

                <form method="post" class="settings-form"
                    onsubmit="return confirm('Sei sicura di voler eliminare definitivamente il tuo profilo? Questa azione non può essere annullata.');">
                    <input type="hidden" name="form_type" value="delete_account">
                    <button type="submit" class="danger-button">
                        Elimina il mio profilo
                    </button>
                </form>
            </div>
        </section>
    </main>
</body>

</html>