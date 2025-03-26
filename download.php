<?php
require_once "auth.php";   // Vérifie la session
require_once "log.php";    // Pour log_action()

if (!isset($_GET["file"])) {
    die("Aucun fichier spécifié.");
}

// On récupère le nom du fichier dans l'URL
$file = basename($_GET["file"]); // Sécurise en enlevant les chemins
$user_dir = "files/" . $_SESSION["username"];
$filepath = $user_dir . "/" . $file;

// Vérifie si le fichier existe vraiment
if (!file_exists($filepath)) {
    die("Fichier introuvable.");
}

// Log le téléchargement
log_action("download_file: $file", $_SESSION["user_id"]);

// Force le téléchargement (headers HTTP)
header("Content-Description: File Transfer");
header("Content-Type: application/octet-stream");
header('Content-Disposition: attachment; filename="'.basename($filepath).'"');
header("Expires: 0");
header("Cache-Control: must-revalidate");
header("Pragma: public");
header("Content-Length: " . filesize($filepath));

// Lit le fichier et l’envoie au navigateur
readfile($filepath);
exit;
