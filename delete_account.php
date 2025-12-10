<?php
require 'config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Elimina like dell'utente
$stmt = $pdo->prepare("DELETE FROM likes WHERE user_id = ?");
$stmt->execute([$user_id]);

// Elimina post dell'utente
$stmt = $pdo->prepare("DELETE FROM posts WHERE user_id = ?");
$stmt->execute([$user_id]);

// Elimina l'utente
$stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
$stmt->execute([$user_id]);

// Cancella la sessione
session_unset();
session_destroy();

// Rimanda al login
header("Location: login.php?deleted=1");
exit;
