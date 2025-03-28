
<?php
require_once "../auth.php";   // Vérifie la session
include 'config1.php';

if ($_SESSION["is_admin"] == 0) {
	header('Location: ./securisation/index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Vérification du token CSRF
	if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		echo "Token CSRF invalide.";
		exit;
	}

	if (empty($_POST['command'])) {
		echo "Commande vide.";
		exit;
	}

	// Définition d'une liste blanche des commandes autorisées
	$whitelist = array(
		'id',
		'ping',
		'ss',
		'ps',
		'ls'
		// Vous pouvez ajouter d'autres commandes autorisées ici
	);

	// Récupération et nettoyage de la commande saisie
	$input_command = trim($_POST['command']);
	$parts = explode(' ', $input_command);
	$base_command = $parts[0];

	// Vérification que la commande est dans la liste blanche
	if (!in_array($base_command, $whitelist)) {
		echo "Commande non autorisée.";
		exit;
	}

	// Journalisation de la commande exécutée
	$log_entry = sprintf("[%s] User: %s, Command: %s\n", date('Y-m-d H:i:s'), $_SESSION['user_id'], $input_command);
	file_put_contents('cmd.log', $log_entry, FILE_APPEND);

	// Exécution sécurisée de la commande
	$safe_command = escapeshellcmd($input_command);
	$output = shell_exec($safe_command);

	echo "<pre>" . htmlspecialchars($output, ENT_QUOTES, 'UTF-8') . "</pre>";

} else {
	exit('Méthode non autorisée.');
}
