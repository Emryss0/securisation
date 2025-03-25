<?php
require_once "db.php"; // Pour accéder à la variable $pdo

function log_action($action, $user_id = null) {
    // Récupère l'adresse IP
    $ip = $_SERVER["REMOTE_ADDR"] ?? "unknown";

    // Prépare la requête d'insertion dans la table "logs"
    $stmt = $GLOBALS["pdo"]->prepare("
        INSERT INTO logs (user_id, action, ip_address)
        VALUES (?, ?, ?)
    ");
    $stmt->execute([$user_id, $action, $ip]);
}
