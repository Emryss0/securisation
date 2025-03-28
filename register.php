<?php
require_once "config.php";   // Infos BDD
require_once "db.php";       // Connexion PDO

session_start();
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];
    $password2 = $_POST["password2"];
    $description = trim($_POST["description"]);
    $csrf_token = $_POST["csrf_token"];

    // Vérification CSRF
    if (!isset($_SESSION["csrf_token"]) || $csrf_token !== $_SESSION["csrf_token"]) {
        $errors[] = "Requête invalide.";
    }

    // Vérification des champs obligatoires
    if (empty($username) || empty($password) || empty($description)) {
        $errors[] = "Tous les champs sont requis.";
    }

    // Vérifier la concordance des mots de passe
    if ($password !== $password2) {
        $errors[] = "Les mots de passe ne correspondent pas.";
    }

    // Vérification de la complexité du mot de passe :
    // Au moins 16 caractères, une minuscule, une majuscule et un caractère spécial
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{16,}$/', $password)) {
        $errors[] = "Le mot de passe doit contenir au moins 16 caractères, incluant des majuscules, des minuscules et des caractères spéciaux.";
    }

    // Vérification du captcha Google reCAPTCHA
    if (empty($_POST['g-recaptcha-response'])) {
        $errors[] = "Veuillez confirmer que vous n'êtes pas un robot.";
    } else {
        $secret = "6LfPggIrAAAAAGgUVeCcsMoCZQ1bvp_Srzv3eJD7";
        $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$_POST['g-recaptcha-response']);
        $data = json_decode($response);
        if (!$data->success) {
            $errors[] = "Captcha non validé. Essayez encore.";
        }
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

        // Insérer l'utilisateur dans la base de données
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, description) VALUES (?, ?, ?)");
        $stmt->execute([$username, $hash, $description]);

        header("Location: login.php");
        exit;
    }
}

// Génération d'un token CSRF pour le formulaire
$_SESSION["csrf_token"] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - AlexCloud</title>
    <link rel="stylesheet" href="./style/register.css">
    <!-- Chargement de la librairie Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>
    <div class="register-container">
        <h2>Inscription à AlexCloud</h2>
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>
            <input type="password" name="password" placeholder="Mot de passe" required>
            <input type="password" name="password2" placeholder="Confirmez le mot de passe" required>
            <input type="text" name="description" placeholder="Description" required>

            <!-- Section Google reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="6LfPggIrAAAAACs4xh0j9BrqlD0K-CjSVQCxlkJQ"></div>

            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="submit" value="Créer mon compte">
        </form>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $err): ?>
                    <p><?php echo htmlspecialchars($err); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <p class="login-text">Vous avez déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>
</html>
