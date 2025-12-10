<?php
// Configurazione Database
$host = 'localhost';
$dbname = 'my_fedeptalentform';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Errore connessione DB: " . $e->getMessage());
}

// Avvia la sessione solo se non è già attiva
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}