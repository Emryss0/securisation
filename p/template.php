<?php

require_once "../auth.php"; // Vérifie que l'utilisateur est connecté



// Vérification que l'utilisateur est correctement authentifié
if (!isset($_SESSION['username'])) {
    header('Location: /login.php');
    exit();
}

$username = $_SESSION['username'];

// Recherche de l'utilisateur avec vérification de sécurité (supposé sécurisée dans auth.php)
if (!($user_data = search_user($username))) {
    error_log("User not found: " . $username);
    printf("<h1>Une erreur est survenue</h1>");
    die();
}

// Génération d'un token CSRF pour le formulaire
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

?>
<html>

<head>
    <meta charset="UTF-8">
    <title>Profile page</title>
    <link rel="stylesheet" href="./style/template.css">
</head>

<body>
    <?php require_once "../header.php"; // Inclusion du header (profil, logo, déconnexion) ?>
    <div class="container">
        <div id="profile">
            <a href="/"><img src="/alexcloud.png" alt="Logo" /></a>
            <h1>Bienvenue sur la page de profil de
                <?php echo htmlspecialchars($user_data["username"], ENT_QUOTES, 'UTF-8'); ?>
            </h1>
            <h3>
                <?php
                if ($user_data["is_admin"] == 1) {
                    echo "L'utilisateur est administrateur";
                } else {
                    echo "L'utilisateur n'est pas administrateur";
                }
                ?>
            </h3>
            <br><br>
            <br><br>
            <h2>Description de l'utilisateur :</h2>
            <form action="../modification.php" method="post">
                <!-- Token CSRF pour la sécurité -->
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>" />
                <input type="text" name="description"
                    value="<?php echo htmlspecialchars($user_data['description'], ENT_QUOTES, 'UTF-8'); ?>" />
                <button type="submit">Modifier</button>
            </form>
        </div>
    </div>
</body>

</html>