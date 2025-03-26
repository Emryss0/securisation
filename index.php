<?php
require_once "auth.php";   // Vérifie que l'utilisateur est connecté
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Accueil - AlexCloud</title>
    <link rel="stylesheet" href="./style/index.css">
    <!-- Inclure le CSS du header si nécessaire -->
    <link rel="stylesheet" href="./style/header.css">
</head>
<body>
    <?php require_once "header.php"; // Inclusion du header (profil, logo, déconnexion) ?>
    <div class="container">
        <h1>Bienvenue, <?php echo htmlspecialchars($_SESSION["username"]); ?> !</h1>
        <p>Voici votre espace AlexCloud.</p>

        <div class="links">
            <a href="upload.php">Uploader un fichier</a>
        </div>

        <h2>Vos fichiers :</h2>
        <div id="files-list">
            <?php
            // Chemin du dossier utilisateur
            $user_dir = "files/" . $_SESSION["username"];
            if (is_dir($user_dir)) {
                // Récupère la liste des fichiers en excluant '.' et '..'
                $files = array_diff(scandir($user_dir), array('.', '..'));
                if (count($files) > 0) {
                    echo "<table id='files-table'>";
                    echo "<tr><th>Nom du fichier</th><th>Télécharger</th><th>Supprimer</th></tr>";
                    foreach ($files as $file) {
                        // Attribuer un ID à chaque ligne pour pouvoir la supprimer dynamiquement
                        echo "<tr id='row-" . htmlspecialchars($file) . "'>";
                        echo "<td class='file-name'>" . htmlspecialchars($file) . "</td>";
                        
                        // Lien de téléchargement avec icône SVG
                        echo "<td class='action-cell'>";
                        echo "<a class='download-link' href='download.php?file=" . urlencode($file) . "' title='Télécharger'>";
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-arrow-down-fill" viewBox="0 0 16 16">';
                        echo '  <path d="M9.293 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.707A1 1 0 0 0 13.707 4L10 .293A1 1 0 0 0 9.293 0ZM9.5 3.5v-2l3 3h-2a1 1 0 0 1-1-1Zm-1 4v3.793l1.146-1.147a.5.5 0 0 1 .708.708l-2 2a.5.5 0 0 1-.708 0l-2-2a.5.5 0 0 1 .708-.708L7.5 11.293V7.5a.5.5 0 0 1 1 0Z"/>';
                        echo '</svg>';
                        echo "</a>";
                        echo "</td>";
                        
                        // Lien de suppression avec icône SVG, géré en AJAX
                        echo "<td class='action-cell'>";
                        echo "<a class='delete-link' href='#' data-file='" . htmlspecialchars($file) . "' title='Supprimer'>";
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash" viewBox="0 0 16 16">';
                        echo '  <path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0Z"/>';
                        echo '  <path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1Z"/>';
                        echo '</svg>';
                        echo "</a>";
                        echo "</td>";
                        
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
        </div>
    </div>
    <script>
    // Ajout d'un écouteur sur les liens de suppression pour les gérer en AJAX
    document.addEventListener('DOMContentLoaded', function() {
        var deleteLinks = document.querySelectorAll('.delete-link');
        deleteLinks.forEach(function(link) {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                var fileName = this.getAttribute('data-file');
                if (confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?")) {
                    fetch("delete.php?file=" + encodeURIComponent(fileName), {
                        method: "GET",
                        headers: {
                            "X-Requested-With": "XMLHttpRequest"
                        }
                    })
                    .then(function(response) {
                        return response.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            // Supprimer la ligne correspondante du tableau
                            var row = document.getElementById("row-" + fileName);
                            if (row) {
                                row.parentNode.removeChild(row);
                            }
                            alert(data.success);
                        } else if (data.error) {
                            alert(data.error);
                        }
                    })
                    .catch(function(error) {
                        console.error("Erreur:", error);
                        alert("Erreur lors de la suppression du fichier.");
                    });
                }
            });
        });
    });
    </script>
</body>
</html>
