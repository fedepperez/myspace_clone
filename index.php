<?php
require 'config.php';

if (isset($_SESSION['user_id'])) {
    // Se loggato
    header("Location: home.php");
    exit;
} else {
    // Se non loggato
    header("Location: login.php");
    exit;
}
