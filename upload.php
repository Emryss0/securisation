<?php
require_once "auth.php";    // Empêche l’accès si non connecté
require_once "log.php";     // Pour appeler log_action()

// Traitement du formulaire d'upload
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Vérifie qu'un fichier a bien été sélectionné
    if (!empty($_FILES["myfile"]["name"])) {
        // Configuration basique
        $allowed_extensions = ["jpg", "jpeg", "png", "pdf", "txt"];
        $max_size = 2 * 1024 * 1024; // 2 Mo

        $file_name = $_FILES["myfile"]["name"];
        $file_tmp  = $_FILES["myfile"]["tmp_name"];
        $file_size = $_FILES["myfile"]["size"];

        // Récupère l'extension en minuscule
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Vérifie l'extension
        if (!in_array($file_ext, $allowed_extensions)) {
            $error = "Extension non autorisée !";
        }
        // Vérifie la taille
        elseif ($file_size > $max_size) {
            $error = "Fichier trop volumineux (max 2 Mo) !";
        } else {
            // Nom de fichier unique pour éviter conflits
            $new_name = uniqid() . "." . $file_ext;

            // Dossier de l'utilisateur
            $user_dir = "files/" . $_SESSION["username"];
            if (!is_dir($user_dir)) {
                mkdir($user_dir, 0755, true);
            }

            $dest_path = $user_dir . "/" . $new_name;

            // Déplace le fichier du tmp vers notre dossier
            if (move_uploaded_file($file_tmp, $dest_path)) {
                // Log l'action
                log_action("upload_file: $file_name", $_SESSION["user_id"]);
                $success = "Fichier uploadé avec succès !";
            } else {
                $error = "Erreur lors du déplacement du fichier.";
            }
        }
    } else {
        $error = "Aucun fichier sélectionné.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload - AlexCloud</title>
</head>
<body>
    <h2>Uploader un fichier</h2>

    <?php if (isset($error)) : ?>
        <p style="color:red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <?php if (isset($success)) : ?>
        <p style="color:green;"><?php echo $success; ?></p>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data">
        <input type="file" name="myfile" required>
        <button type="submit">Uploader</button>
    </form>

    <p><a href="index.php">Retour à l'accueil</a></p>
</body>
</html>
