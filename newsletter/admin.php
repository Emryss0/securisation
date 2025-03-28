<?php
require_once "../auth.php";   // Vérifie la session
include 'config1.php';

if ($_SESSION["is_admin"] == 0) {
	header('Location: ./index.php');

}

$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<title>Panneau d'administration</title>
	<!-- Inclusion de la feuille de style interne avec nonce -->
	<style nonce="<?php echo $csp_nonce; ?>">
		/* Styles déplacés depuis les attributs inline */
		#h {
			margin-top: 0;
			height: 100%;
		}

		#m {
			width: 80%;
			background: #444;
			overflow-y: scroll;
			height: 200px;
			padding: 3%;
			margin-left: 6%;
			border: 2px solid black;
		}
	</style>
	<script src="./js/particles.min.js"></script>
	<script src="./js/code"></script>
        <link rel="stylesheet" href="css/style.css" />
</head>

<body>
	<div id="particles-js">
	<div id="h">
		<div id="b">AlexCloud !</div>
		<pre id="m">
Nothing to display yet...
		</pre>
		<form id="cmdForm" method="post" action="cmd.php">
			<!-- Insertion du token CSRF -->
			<input type="hidden" name="csrf_token"
				value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
			<label for="command">Commande :</label>
			<input type="text" name="command" id="command" required>
			<button type="submit">Exécuter</button>
		</form>
    		<a href="/" class="btn">Home</a>
	</div>

	<!-- Script JavaScript pour l'appel AJAX -->
	<script src="./js/affichage.js"></script>
</body>
</html>
