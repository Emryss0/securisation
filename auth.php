<?php
session_start();

// Vérifie que l'utilisateur est connecté
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}


require("lib_login.php");

?>