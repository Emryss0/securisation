<?php
require_once "auth.php";   // Vérifie que l'utilisateur est connecté
require_once "log.php";    // Pour enregistrer l'action dans les logs

// On force la réponse au format JSON
header('Content-Type: application/json');

if (!isset($_GET["file"])) {
    echo json_encode(["error" => "Aucun fichier spécifié."]);
    exit;
}

$file = basename($_GET["file"]);
$user_dir = "files/" . $_SESSION["username"];
$filepath = $user_dir . "/" . $file;

if (!file_exists($filepath)) {
    echo json_encode(["error" => "Fichier introuvable."]);
    exit;
}

if (unlink($filepath)) {
    log_action("delete_file: $file", $_SESSION["user_id"]);
    echo json_encode(["success" => "Fichier supprimé avec succès."]);
} else {
    echo json_encode(["error" => "Erreur lors de la suppression du fichier."]);
}
exit;
?>