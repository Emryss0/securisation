<?php

require_once "../auth.php";    // Vérifie la session (si nécessaire)
require_once "../config.php";  // Fichier de configuration de la base de données
require_once "../db.php";      // Connexion PDO (doit définir $pdo)

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['t'])) {
    // Récupération et nettoyage de l'adresse mail
    $email = filter_var($_POST['t'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['msg'] = "Adresse mail invalide.";
    } else {
        // Vérification de l'existence de l'email dans la table "mails"
        $stmt = $pdo->prepare("SELECT * FROM mails WHERE mail = ?");
        $stmt->execute([$email]);
        $mailEntry = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$mailEntry) {
            // Insertion de la nouvelle adresse
            $stmt = $pdo->prepare("INSERT INTO mails (mail) VALUES (?)");
            if ($stmt->execute([$email])) {
                $_SESSION['msg'] = "Votre adresse a bien été ajoutée à la liste de diffusion !";
            } else {
                $errorInfo = $stmt->errorInfo();
                $_SESSION['msg'] = "Erreur lors de l'ajout de votre adresse : " . $errorInfo[2];
            }
        } else {
            $_SESSION['msg'] = "Erreur : votre adresse est déjà enregistrée.";
        }
    }
    header("Location: index.php");
    exit;
} else {
    header("Location: index.php");
    exit;
}
?>