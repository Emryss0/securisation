<?php
require_once "config.php";  // config BDD
require_once "db.php";      // connexion PDO


function search_user()
{
    // $username = $_SESSION["username"];
    // Utiliser l'instance PDO déclarée dans db.php
    global $pdo;
    // $tutu = "toto";
    $username = $_SESSION['username'];
    // Préparation de la requête sécurisée
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Retourner l'utilisateur trouvé ou false sinon
    return $user ? $user : false;
}
?>