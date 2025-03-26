<?php
session_start();
$msg = "";
if (isset($_SESSION['msg'])) {
	$msg = $_SESSION['msg'];
	unset($_SESSION['msg']);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<title>AlexCloud - NewsLetter !</title>
	<link rel="stylesheet" href="css/style.css" />
	<script src="js/particles.min.js"></script>
	<script>
		particlesJS.load('particles-js', 'assets/particles.json', function () { });
	</script>
</head>

<body>
	<div id="particles-js"></div>
	<div id="h">
		<div id="b">Beta AlexNews !</div>
		<div id="d">
			Le AlexCloud est une application qui a pour vocation de détrôner la suite Google ainsi que Office 365 !
			(On n'est pas loin)<br><br>
			Abonnez-vous à notre newsletter pour recevoir plus d'informations à notre sujet !
		</div>
		<form id="e" action="process.php" method="POST">
			<?php if (!empty($msg))
				echo '<p id="r">' . htmlspecialchars($msg) . '</p>'; ?>
			<input name="t" type="email" placeholder="Adresse Mail" required />
			<input type="submit" value="Recevoir la Newsletter !" />
		</form>
	</div>
</body>

</html>