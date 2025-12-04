<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// elimina l'utente
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// cancella la sessione
session_unset();
session_destroy();

// rimanda al login
header("Location: login.php?deleted=1");
exit;
