<?php
require 'config.php';

// Se l’utente è loggato, vai direttamente al profilo
if (isset($_SESSION['user_id'])) {
    header("Location: profile.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <title>MySpace Clone - Home</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            margin: 0;
            font-family: system-ui, sans-serif;
            background: linear-gradient(135deg, #1a1a1a, #000);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: #f0f0f0;
        }

        .home-container {
            text-align: center;
            padding: 2.5rem;
            background: rgba(0, 0, 0, 0.75);
            border-radius: 14px;
            border: 1px solid #3aa4ff;
            width: 90%;
            max-width: 480px;
        }

        h1 {
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
            color: #3aa4ff;
        }

        p {
            color: #ccc;
            margin-bottom: 2rem;
            font-size: 1rem;
        }

        .button-group {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .home-button {
            padding: 0.8rem;
            border-radius: 10px;
            border: none;
            background-color: #3aa4ff;
            font-weight: 600;
            color: #000;
            text-decoration: none;
            display: block;
            font-size: 1rem;
        }

        .home-button:hover {
            background-color: #62b8ff;
        }
    </style>
</head>
<body>

<div class="home-container">
    <h1>MySpace Clone</h1>
    <p>Benvenuta nel tuo social anni 2000 reinventato.<br>
    Accedi o crea un profilo per iniziare ✨</p>

    <div class="button-group">
        <a href="login.php" class="home-button">Accedi</a>
        <a href="register.php" class="home-button">Registrati</a>
    </div>
</div>

</body>
</html>
