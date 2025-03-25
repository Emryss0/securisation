<?php
require_once "auth.php"; // Vérifie que l'utilisateur est connecté
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - AlexCloud</title>
</head>
<body>
    <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION["username"]); ?> !</h1>
    <p>Voici votre espace AlexCloud.</p>

    <p>
        <a href="upload.php">Uploader un fichier</a> |
        <a href="logout.php">Se déconnecter</a>
    </p>

    <h2>Vos fichiers :</h2>
    <?php
    // Chemin du dossier utilisateur
    $user_dir = "files/" . $_SESSION["username"];

    // Vérifie si le dossier existe
    if (is_dir($user_dir)) {
        // Récupère la liste des fichiers en excluant '.' et '..'
        $files = array_diff(scandir($user_dir), array('.', '..'));
        
        if (count($files) > 0) {
            echo "<table border='1'>";
            echo "<tr><th>Nom du fichier</th><th>Télécharger</th><th>Supprimer</th></tr>";
            foreach ($files as $file) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($file) . "</td>";
                echo "<td><a href='download.php?file=" . urlencode($file) . "'>Télécharger</a></td>";
                echo "<td><a href='delete.php?file=" . urlencode($file) . "' onclick='return confirm(\"Êtes-vous sûr de vouloir supprimer ce fichier ?\");'>Supprimer</a></td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>Aucun fichier n'a été uploadé pour le moment.</p>";
        }
    } else {
        echo "<p>Votre dossier de fichiers n'existe pas. Essayez d'uploader un fichier pour le créer.</p>";
    }
    ?>
</body>
</html>
