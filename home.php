<?php
require 'config.php';

// Controlla l'autenticazione
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

/* ===== Gestione like (toggle) ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'] ?? '';

    if ($formType === 'toggle_like') {
        $postId = isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0;

        if ($postId > 0) {
            // Esiste già un like di questo utente su questo post?
            $stmt = $pdo->prepare("SELECT id FROM likes WHERE user_id = ? AND post_id = ?");
            $stmt->execute([$user_id, $postId]);
            $likeId = $stmt->fetchColumn();

            if ($likeId) {
                // Se esiste, togli il like (DELETE)
                $stmt = $pdo->prepare("DELETE FROM likes WHERE id = ?");
                $stmt->execute([$likeId]);
            } else {
                // Altrimenti metti il like (INSERT)
                $stmt = $pdo->prepare("INSERT INTO likes (user_id, post_id) VALUES (?, ?)");
                $stmt->execute([$user_id, $postId]);
            }
        }

        // Evita resubmit del form
        header("Location: home.php");
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

/* ===== Tutti i post + like status e count ===== */
$stmtPosts = $pdo->prepare(
    "SELECT
        p.id,
        p.content,
        p.created_at,
        u.username,
        u.profile_color,
        u.id AS author_id,
        COUNT(l.id) AS like_count,
        MAX(CASE WHEN l.user_id = :currentUserId THEN 1 ELSE 0 END) AS liked_by_me
     FROM posts p
     JOIN users u ON p.user_id = u.id
     LEFT JOIN likes l ON l.post_id = p.id
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
    <title>Home - MySpace Clone</title>
    <link rel="stylesheet" href="style.css">
</head>


<body class="profile-page home-page">
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
                    <span><?= strtoupper(substr($currentUser['username'], 0, 1)) ?></span>
                </div>
                <div class="profile-heading">
                    <h2>Home</h2>
                    <p class="profile-subtitle">
                        Bacheca globale: qui vedi i post tuoi e delle altre persone.
                    </p>
                </div>
            </div>

            <div class="profile-section posts-section">
                <h3>Tutti i post</h3>
                <?php if (empty($posts)): ?>
                    <p class="no-posts">Non c'è ancora nessun post.</p>
                <?php else: ?>
                    <div class="posts-list">
                        <?php foreach ($posts as $post): ?>
                            <?php
                            $isMine    = ($post['author_id'] == $currentUser['id']);
                            $initial   = strtoupper(substr($post['username'], 0, 1));
                            $likeCount = (int)$post['like_count'];
                            $likedByMe = (int)$post['liked_by_me'] === 1;
                            ?>
                            <article class="post-card">
                                <div class="profile-header-row" style="margin-bottom:0.4rem; gap:0.6rem;">
                                    <div class="avatar-bubble" style="width:40px;height:40px;font-size:1.1rem;border-width:1px;">
                                        <span><?= htmlspecialchars($initial) ?></span>
                                    </div>
                                    <div>
                                        <strong><?= htmlspecialchars($post['username']) ?></strong>
                                        <?php if ($isMine): ?>
                                            <span class="profile-tag" style="margin-left:0.3rem;">Tu</span>
                                        <?php endif; ?>
                                        <p class="post-meta" style="text-align:left;margin-top:0.15rem;">
                                            <?php
                                            $dt = new DateTime($post['created_at']);
                                            echo $dt->format('d/m/Y H:i');
                                            ?>
                                        </p>
                                    </div>
                                </div>

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
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="profile-edit-card">
            <h3>Crea e gestisci i tuoi post</h3>
            <p class="profile-edit-hint">
                Per scrivere un nuovo post o modificare il tuo spazio personale,
                vai nella sezione <strong>Profilo</strong>.
            </p>
            <a href="profile.php" class="profile-save-btn" style="display:inline-block; text-decoration:none; text-align:center;">
                Vai al tuo profilo
            </a>
        </section>
    </main>
</body>

</html>