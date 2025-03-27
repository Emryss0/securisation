<?php
require_once "config.php";  // config BDD
require_once "db.php";      // connexion PDO
session_start();

$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    // Vérifier que les champs ne sont pas vides
    if (empty($username) || empty($password)) {
        $errors[] = "Tous les champs sont requis.";
    } else {
        // Préparer et exécuter une requête sécurisée
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user) {
            // Vérification du mot de passe hashé
            if (password_verify($password, $user["password_hash"])) {
                // Session sécurisée
                session_regenerate_id(true);

                $_SESSION["user_id"] = $user["id"];
                $_SESSION["username"] = $user["username"];
                $_SESSION["is_admin"] = $user["is_admin"] ?? false;

                // Redirection vers l'espace utilisateur
                header("Location: index.php");
                exit;
            } else {
                $errors[] = "Identifiants incorrects.";
            }
        } else {
            $errors[] = "Identifiants incorrects.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>Connexion - AlexCloud</title>
    <link rel="stylesheet" href="./style/login.css">
</head>

<body>
    <div class="login-container">
        <img class="logo" src="http://localhost/securisation/img/alexcloud.png" alt="Logo AlexCloud">
        <h2>Connexion à AlexCloud</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="submit" value="Se connecter">
            <?php if (!empty($errors)): ?>
                <div class="error-message">
                    <?php foreach ($errors as $e): ?>
                        <p><?php echo htmlspecialchars($e); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </form>
        <p class="register-text">Vous n'avez pas de compte ? <a href="register.php">Créez-en un</a></p>
    </div>
</body>

</html>