<?php
require_once "auth.php";    // Vérifie la session
require_once "config.php";  // config BDD
require_once "db.php";      // connexion PDO

// Traitement du formulaire lorsque le formulaire est soumis en POST
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['description'])) {
    $description = $_POST['description'];
    $user_id = $_SESSION['user_id'];

    // Utilisation d'une requête préparée avec PDO pour mettre à jour la description
    $stmt = $pdo->prepare("UPDATE users SET description = ? WHERE id = ?");
    if ($stmt->execute([$description, $user_id])) {
        $message = "Description mise à jour avec succès.";
        header("Location: ./p/template.php");
    } else {
        $errorInfo = $stmt->errorInfo();
        $message = "Erreur lors de la mise à jour de la description : " . $errorInfo[2];
    }



}
?>