<?php
require_once "auth.php"; // S'assurer que l'utilisateur est connecté

?>
<header>
    <div class="header-left">
        <a class="profile-link" href="./p/template.php">Profil</a>
    </div>
    <div class="header-center">
        <img class="header-logo" src="http://localhost/securisation/img/alexcloud.png" alt="Logo AlexCloud">
    </div>
    <div class="header-right">
        <a class="logout-link" href="logout.php">Se déconnecter</a>
    </div>
</header>