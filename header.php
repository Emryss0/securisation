<?php
require_once "auth.php"; // S'assurer que l'utilisateur est connecté
$nom=$_SESSION["username"]
?>
<header>
    <div class="header-left">
        <a class="profile-link" href="./p/<?php echo $nom ?>.php">Profil</a>
    </div>
    <div class="header-center">
        <img class="header-logo" src="./img/alexcloud.png" alt="Logo AlexCloud">
    </div>
    <div class="header-right">
        <a class="logout-link" href="logout.php">Se déconnecter</a>
    </div>
</header>
