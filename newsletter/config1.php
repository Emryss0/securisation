<?php
// Démarrage de la session


if (!isset($_SESSION['csp_nonce'])) {
    $_SESSION['csp_nonce'] = bin2hex(random_bytes(16));
}

$csp_nonce = $_SESSION['csp_nonce'];

// Ajout du nonce à la directive CSP pour les styles
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'nonce-$csp_nonce'");
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
// Régénérer le token CSRF à chaque requête GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>