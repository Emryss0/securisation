<?php
require_once "config.php";   // infos BDD
require_once "db.php";       // connexion PDO

session_start();
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $password2 = $_POST["password2"];
    $description = trim($_POST["description"]);
    $csrf_token = $_POST["csrf_token"];

    // Vérification CSRF Cross-Site Request Forgery,
    if (!isset($_SESSION["csrf_token"]) || $csrf_token !== $_SESSION["csrf_token"]) {
        $errors[] = "Requête invalide.";
    }

    // Vérification des champs
    if (empty($username) || empty($password) || empty($description)) {
        $errors[] = "Tous les champs sont requis.";
    }

    if ($password !== $password2) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérifier si le username existe déjà
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        $errors[] = "Ce nom d'utilisateur est déjà pris.";
    }

    if (empty($errors)) {
        // Hasher le mot de passe avec Argon2id
        $hash = password_hash($password, PASSWORD_ARGON2ID);

        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, description) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $description]);

        header("Location: login.php");
        exit;
    }
}

// Génération du token CSRF Cross-Site Request Forgery,
$_SESSION["csrf_token"] = bin2hex(random_bytes(32));
?>

<!DOCTYPE html>
<html>
<head><title>Inscription</title></head>
<body>
    <form method="POST" action="">
        <input type="text" name="username" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <input type="password" name="password2" placeholder="Confirmez le mot de passe" required>
        <input type="text" name="description" placeholder="Description">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="submit" value="Créer mon compte">
    </form>

    <?php if (!empty($errors)) {
        foreach ($errors as $err) {
            echo "<div style='color:red;'>$err</div>";
        }
    } ?>
</body>
</html>
