<?php
require_once "auth.php";   // Vérifie que l'utilisateur est connecté
require_once "log.php";    // Pour enregistrer l'action dans les logs

// Vérifier que le paramètre "file" est présent
if (!isset($_GET["file"])) {
    die("Aucun fichier spécifié.");
}

// Récupère le nom du fichier en sécurisant le chemin pour éviter la traversée de répertoire
$file = basename($_GET["file"]);
$user_dir = "files/" . $_SESSION["username"];
$filepath = $user_dir . "/" . $file;

// Vérifie que le fichier existe dans le dossier de l'utilisateur
if (!file_exists($filepath)) {
    die("Fichier introuvable.");
}

// Tente de supprimer le fichier
if (unlink($filepath)) {
    // Journaliser la suppression
    log_action("delete_file: $file", $_SESSION["user_id"]);
    $message = "Fichier supprimé avec succès.";
} else {
    $message = "Erreur lors de la suppression du fichier.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Suppression de fichier</title>
</head>
<body>
    <h2>Suppression de fichier</h2>
    <p><?php echo htmlspecialchars($message); ?></p>
    <p><a href="index.php">Retour à l'accueil</a></p>
</body>
</html>
