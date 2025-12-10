<?php
// Credenziali di connessione al database
$host = 'localhost';             // Hostname/server
$dbname = 'my_fedeptalentform';  // Database
$user = 'fedeptalentform';       // Username
$pass = '';                      // Password Facoltativa (campo vuoto)

try {
    // Connessione al database tramite PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Errore connessione DB: " . $e->getMessage());
}

session_start();
