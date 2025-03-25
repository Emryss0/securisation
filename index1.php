<?php
	session_start();
	require("forced.php");

	// Générer un token CSRF si nécessaire
	if (!isset($_SESSION['csrf_token'])) {
		$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
	}
	$csrf_token = $_SESSION['csrf_token'];

	$UPLOADED = false;
	$error = '';

	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		// Vérification du token CSRF
		if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
			$error = 'Token CSRF invalide.';
		} elseif (isset($_FILES["filecontent"]) && isset($_POST["description"])) {
			// Vérifier que le fichier a bien été uploadé par HTTP POST
			if (is_uploaded_file($_FILES["filecontent"]["tmp_name"])) {

				// Récupération et assainissement du nom de fichier
				$originalFilename = basename($_FILES["filecontent"]["name"]);
				// Remplacer les caractères non autorisés par un underscore
				$safeFilename = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalFilename);
				
				// Vérifier l'extension du fichier (liste noire)
				$dangerousExtensions = ['php', 'php3', 'php4', 'php5', 'phtml', 'exe', 'sh', 'bat', 'pl', 'py', 'jsp', 'asp', 'aspx'];
				$fileExtension = strtolower(pathinfo($safeFilename, PATHINFO_EXTENSION));
				if (in_array($fileExtension, $dangerousExtensions)) {
					$error = 'Extension de fichier non autorisée.';
				} else {
					// Vérification du type MIME
					$finfo = finfo_open(FILEINFO_MIME_TYPE);
					$mimeType = finfo_file($finfo, $_FILES["filecontent"]["tmp_name"]);
					finfo_close($finfo);
					// Liste d'exemples de types MIME autorisés (à adapter selon vos besoins)
					$allowedMimeTypes = [
						'image/jpeg', 'image/png', 'image/gif', 'application/pdf',
						'text/plain', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
					];
					// Pour un site d'hébergement de fichiers, vous pouvez autoriser plus de types en fonction du contexte
					if (!in_array($mimeType, $allowedMimeTypes)) {
						$error = 'Type de fichier non autorisé.';
					} else {
						$userDir = "/var/www/html/files/" . $__connected["USERNAME"] . "/";
						if (!is_dir($userDir)) {
							mkdir($userDir, 0755, true);
						}
						$destination = $userDir . $safeFilename;
						if (move_uploaded_file($_FILES["filecontent"]["tmp_name"], $destination)) {
							// Définir des permissions non exécutables (lecture/écriture pour le propriétaire et lecture pour les autres)
							chmod($destination, 0644);
							// Assainissement de la description avant de la stocker
							$desc = strip_tags($_POST["description"]);
							file_put_contents($destination . ".desc", $desc);
							$UPLOADED = true;
						} else {
							$error = 'Erreur lors du déplacement du fichier uploadé.';
						}
					}
				}
			} else {
				$error = 'Fichier non uploadé correctement.';
			}
		}
	}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<title>Accueil - AlexCloud</title>
	<style>
		/* Styles généraux */
		body, html {
			margin: 0;
			padding: 0;
			font-family: Arial, sans-serif;
			background: linear-gradient(90deg, #020024, #090979, #00d4ff);
			color: #333;
		}
		header {
			background: rgba(255,255,255,0.9);
			padding: 10px 20px;
			display: flex;
			align-items: center;
			justify-content: space-between;
		}
		header img {
			height: 50px;
		}
		nav {
			display: flex;
			gap: 15px;
		}
		nav a {
			text-decoration: none;
			color: #333;
			font-weight: bold;
		}
		main {
			width: 80%;
			margin: 20px auto;
			background: rgba(255,255,255,0.8);
			padding: 20px;
			border-radius: 8px;
		}
		.message {
			padding: 10px;
			border-radius: 4px;
			margin-bottom: 10px;
		}
		.success { background: rgba(0, 255, 0, 0.2); }
		.error { background: rgba(255, 0, 0, 0.2); }
		form {
			margin-bottom: 20px;
		}
		form input[type="text"], form input[type="file"] {
			padding: 8px;
			margin-right: 10px;
		}
		form input[type="submit"] {
			padding: 8px 16px;
		}
		.file-entry {
			background: rgba(50,50,255,0.3);
			padding: 10px;
			margin-bottom: 10px;
			border-radius: 4px;
			display: flex;
			justify-content: space-between;
			align-items: center;
		}
		.file-entry button {
			margin-left: 10px;
			padding: 5px 10px;
		}
	</style>
</head>
<body>
	<header>
		<img src="/alexcloud.png" alt="Logo AlexCloud"/>
		<nav>
			<a href="/logout.php">Déconnexion</a>
			<?php if($__connected["ADMIN"] == 1): ?>
				<a href="/admin.php">Admin</a>
			<?php endif; ?>
			<a href="/p/<?php echo htmlspecialchars($__connected["USERNAME"]); ?>.php">Mon Profil</a>
		</nav>
	</header>
	<main>
		<?php if($UPLOADED): ?>
			<div class="message success">Fichier téléchargé avec succès.</div>
		<?php elseif($error): ?>
			<div class="message error"><?php echo htmlspecialchars($error); ?></div>
		<?php endif; ?>
		<section id="upload-section">
			<h2>Uploader un fichier</h2>
			<form action="#" method="POST" enctype="multipart/form-data">
				<input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
				<input type="text" name="description" placeholder="Description du fichier" required>
				<input type="file" name="filecontent" required>
				<input type="submit" value="Télécharger">
			</form>
		</section>
		<section id="files-section">
			<h2>Mes Fichiers</h2>
			<?php
				$userDir = "/var/www/html/files/" . $__connected["USERNAME"] . "/";
				if (is_dir($userDir)) {
					$files = array_diff(scandir($userDir), array('.', '..'));
					foreach($files as $file) {
						// Exclure les fichiers de description
						if (str_ends_with($file, ".desc")) continue;
						$descFile = $file . ".desc";
						$description = file_exists($userDir . $descFile) ? file_get_contents($userDir . $descFile) : '';
						echo '<div class="file-entry" data-filename="'.htmlspecialchars($file).'">';
						echo '<span>'.htmlspecialchars($file).' ('.htmlspecialchars($description).')</span>';
						echo '<div>';
						echo '<button onclick="deleteFile(\''.htmlspecialchars($file, ENT_QUOTES).'\')">Supprimer</button>';
						echo '<button onclick="downloadFile(\''.htmlspecialchars($file, ENT_QUOTES).'\')">Télécharger</button>';
						echo '</div>';
						echo '</div>';
					}
				} else {
					echo '<p>Aucun fichier trouvé.</p>';
				}
			?>
		</section>
	</main>
	<script>
		function deleteFile(filename) {
			if (confirm("Êtes-vous sûr de vouloir supprimer ce fichier ?")) {
				window.location.href = "/delete_file.php?file=" + encodeURIComponent(filename);
			}
		}
		function downloadFile(filename) {
			window.location.href = "/download_file.php?file=" + encodeURIComponent(filename);
		}
	</script>
</body>
</html>
