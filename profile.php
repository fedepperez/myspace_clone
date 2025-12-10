<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===== Gestione POST: profilo / nuovo post / cancella post / like ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'profile') {
        // Aggiorna bio e colore
        $bio   = trim($_POST['bio'] ?? '');
        $color = $_POST['profile_color'] ?? '#222222';

        $stmt = $pdo->prepare(
            "UPDATE users SET bio = ?, profile_color = ? WHERE id = ?"
        );
        $stmt->execute([$bio, $color, $user_id]);
    } elseif ($formType === 'new_post') {
        // Crea un nuovo post
        $content = trim($_POST['content'] ?? '');
        if ($content !== '') {
            $stmt = $pdo->prepare(
                "INSERT INTO posts (user_id, content) VALUES (?, ?)"
            );
            $stmt->execute([$user_id, $content]);
        }
    } elseif ($formType === 'delete_post') {
        // Elimina un tuo post (controlla che sia effettivamente dell'utente corrente)
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

        if ($postId > 0) {
            $stmt = $pdo->prepare(
                "DELETE FROM posts WHERE id = ? AND user_id = ?"
            );
            $stmt->execute([$postId, $user_id]);
        }
    } elseif ($formType === 'toggle_like') {
        // Metti/togli like su un tuo post
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

        if ($postId > 0) {
            $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $postId]);
            $likeId = $stmt->fetchColumn();

            if ($likeId) {
                $stmt = $pdo->prepare("DELETE FROM likes WHERE id = ?");
                $stmt->execute([$likeId]);
            } else {
                $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $postId]);
            }
        }

        // Reindirizzamento per evitare resubmit
        header("Location: profile.php");
        exit;
    }

    // Se non è un toggle_like, ricarica la pagina per mostrare i cambiamenti (es. bio, nuovo post, delete post)
    if ($formType !== 'toggle_like') {
        header("Location: profile.php");
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

// Iniziale avatar
$initial = strtoupper(substr($currentUser['username'] ?? '?', 0, 1));

// Data iscrizione
$joinedText = '';
if (!empty($currentUser['created_at'])) {
    $joined = new DateTime($currentUser['created_at']);
    $joinedText = $joined->format('d/m/Y');
}

/* ===== Recupera SOLO i tuoi post + like status e count ===== */
$stmtPosts = $pdo->prepare(
    "SELECT
        p.id,
        p.content,
        p.created_at,
        COUNT(l.id) AS like_count,
        MAX(CASE WHEN l.user_id = :currentUserId THEN 1 ELSE 0 END) AS liked_by_me
     FROM posts p
     LEFT JOIN likes l ON l.post_id = p.id
     WHERE p.user_id = :currentUserId
     GROUP BY p.id
     ORDER BY p.created_at DESC"
);
$stmtPosts->execute(['currentUserId' => $user_id]);
$posts = $stmtPosts->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profilo di <?= htmlspecialchars($currentUser['username']) ?> - MySpace Clone</title>
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
        <section class="profile-main-card">
            <div class="profile-header-row">
                <div class="avatar-bubble">
                    <span><?= htmlspecialchars($initial) ?></span>
                </div>
                <div class="profile-heading">
                    <h2><?= htmlspecialchars($currentUser['username']) ?></h2>
                    <p class="profile-subtitle">
                        Il tuo spazio personale su MySpace Clone.
                    </p>
                    <div class="profile-meta">
                        <?php if ($joinedText): ?>
                            <span class="profile-tag">Iscritta dal <?= htmlspecialchars($joinedText) ?></span>
                        <?php endif; ?>
                        <span class="profile-tag profile-color-tag">
                            Colore tema
                            <span class="color-dot"
                                style="background-color: <?= htmlspecialchars($currentUser['profile_color'] ?? '#222222') ?>;">
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="profile-section">
                <h3>Bio</h3>
                <p class="bio">
                    <?php
                    $bio = trim($currentUser['bio'] ?? '');
                    if ($bio === '') {
                        echo 'Non hai ancora scritto nulla su di te.';
                    } else {
                        echo nl2br(htmlspecialchars($bio));
                    }
                    ?>
                </p>
            </div>

            <div class="profile-section">
                <h3>Nuovo post</h3>
                <form method="post" class="post-form">
                    <input type="hidden" name="form_type" value="new_post">

                    <label class="post-label">Cosa vuoi condividere?</label>

                    <textarea name="content"
                        rows="3"
                        class="post-textarea"
                        placeholder="Scrivi qualcosa..."></textarea>

                    <button type="submit" class="post-submit-btn">Pubblica</button>
                </form>
            </div>

            <div class="profile-section posts-section">
                <h3>I tuoi post</h3>
                <?php if (empty($posts)): ?>
                    <p class="no-posts">Non hai ancora pubblicato niente.</p>
                <?php else: ?>
                    <div class="posts-list">
                        <?php foreach ($posts as $post): ?>
                            <?php
                            $likeCount = (int)$post['like_count'];
                            $likedByMe = (int)$post['liked_by_me'] === 1;
                            ?>
                            <article class="post-card">
                                <form method="post" class="post-delete-form"
                                    onsubmit="return confirm('Eliminare questo post?');">
                                    <input type="hidden" name="form_type" value="delete_post">
                                    <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                    <button type="submit" class="post-delete-btn" title="Elimina post">
                                        <svg class="trash-icon" viewBox="0 0 24 24">
                                            <path d="M3 6h18M9 6V4h6v2m-7 4v8m4-8v8m4-8v8M5 6h14l-1 14H6L5 6z"
                                                stroke="currentColor" stroke-width="2" fill="none"
                                                stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </button>
                                </form>

                                <p class="post-content">
                                    <?= nl2br(htmlspecialchars($post['content'])) ?>
                                </p>

                                <div class="post-footer-row">
                                    <form method="post" class="like-form">
                                        <input type="hidden" name="form_type" value="toggle_like">
                                        <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
                                        <button type="submit"
                                            class="like-button <?= $likedByMe ? 'liked' : '' ?>"
                                            title="Metti/togli like">
                                            ❤
                                        </button>
                                        <span class="like-count">
                                            <?= $likeCount ?>
                                        </span>
                                    </form>
                                    <p class="post-meta">
                                        <?php
                                        $dt = new DateTime($post['created_at']);
                                        echo 'Pubblicato il ' . $dt->format('d/m/Y H:i');
                                        ?>
                                    </p>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="profile-edit-card">
            <h3>Modifica il profilo</h3>
            <p class="profile-edit-hint">
                Aggiorna la tua bio e scegli il colore principale del tuo spazio.
            </p>
            <form method="post" class="profile-form">
                <input type="hidden" name="form_type" value="profile">

                <label>
                    Bio
                    <textarea name="bio" rows="5"><?= htmlspecialchars($currentUser['bio'] ?? '') ?></textarea>
                </label>

                <label class="color-picker-row">
                    Colore del tema
                    <div class="color-picker-inline">
                        <input type="color" name="profile_color"
                            value="<?= htmlspecialchars($currentUser['profile_color'] ?? '#222222') ?>">
                        <span class="color-hex">
                            <?= htmlspecialchars($currentUser['profile_color'] ?? '#222222') ?>
                        </span>
                    </div>
                </label>

                <button type="submit" class="profile-save-btn">Salva modifiche</button>
            </form>
        </section>
    </main>
</body>

</html>