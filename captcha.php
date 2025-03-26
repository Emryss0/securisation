<?php
session_start();

$width = 200;
$height = 50;
$image = imagecreatetruecolor($width, $height);

// Définir les couleurs
$white = imagecolorallocate($image, 255, 255, 255);
$black = imagecolorallocate($image, 0, 0, 0);

// Remplir l'image avec du blanc
imagefilledrectangle($image, 0, 0, $width, $height, $white);

/**
 * Génère une chaîne aléatoire sécurisée de la longueur spécifiée.
 *
 * @param int $length Longueur souhaitée de la chaîne (défaut 6).
 * @return string La chaîne aléatoire générée.
 */
function generateRandomString($length = 6)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

$text = generateRandomString(6);
$_SESSION["captcha"] = $text; // Stocker le code captcha dans la session

// Chemin vers une police TTF (assurez-vous qu'elle existe, ici 'arial.ttf' dans le même dossier)
$fontPath = __DIR__ . '/arial.ttf';
$fontSize = 20;

if (file_exists($fontPath)) {
    // Applique une légère rotation aléatoire pour complexifier la lecture
    $angle = random_int(-10, 10);
    imagettftext($image, $fontSize, $angle, 20, 35, $black, $fontPath, $text);
} else {
    // Si la police TTF n'est pas disponible, utiliser imagestring() comme fallback
    imagestring($image, 5, 20, 20, $text, $black);
}

// Ajout de plusieurs lignes aléatoires pour le bruit visuel
for ($i = 0; $i < 5; $i++) {
    $lineColor = imagecolorallocate($image, random_int(100, 255), random_int(100, 255), random_int(100, 255));
    imageline($image, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $lineColor);
}

// Ajout de nombreux points aléatoires pour le bruit
for ($i = 0; $i < 200; $i++) {
    $dotColor = imagecolorallocate($image, random_int(150, 255), random_int(150, 255), random_int(150, 255));
    imagesetpixel($image, random_int(0, $width - 1), random_int(0, $height - 1), $dotColor);
}

header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>