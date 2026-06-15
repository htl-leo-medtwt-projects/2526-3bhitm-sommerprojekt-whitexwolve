<?php
session_start();
require_once __DIR__ . '/data/db.php';

// Ausgabe gegen XSS absichern
function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// bereits eingeloggte User direkt weiterleiten
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors   = [];
$success  = false;
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    if ($username === '') $errors[] = 'Bitte gib einen Benutzernamen ein.';

    if ($email === '') {
        $errors[] = 'Bitte gib eine E-Mail-Adresse ein.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Die E-Mail-Adresse ist ungültig.';
    }

    if ($password === '') {
        $errors[] = 'Bitte gib ein Passwort ein.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Das Passwort muss mindestens 6 Zeichen lang sein.';
    }

    if (empty($errors)) {
        // password_hash() mit PASSWORD_DEFAULT — nie Klartext in die DB
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $username, $email, $passwordHash);

        if ($stmt->execute()) {
            $success = true;
        } else {
            // execute() schlägt fehl wenn username oder email bereits existiert (UNIQUE constraint)
            $errors[] = 'Benutzername oder E-Mail bereits vergeben.';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrieren – VibeSeat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>
<header class="seitenkopf">
    <div class="seitenbreite seitenkopf__leiste">
        <a href="index.php" class="markenname">VibeSeat</a>
        <nav class="hauptnavigation">
            <a class="hauptnavigation__link" href="index.php">Events</a>
            <a class="hauptnavigation__link hauptnavigation__link--aktiv" href="register.php">Registrieren</a>
            <a class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" href="login.php">Login</a>
        </nav>
    </div>
</header>

<main class="auth-seite">
    <div class="auth-box">
        <h1 class="auth-titel">Konto erstellen</h1>
        <p class="auth-untertitel">Bereits registriert? <a href="login.php">Einloggen</a></p>

        <?php if ($success): ?>
            <!-- Formular ausblenden nach erfolgreicher Registrierung -->
            <div class="auth-erfolg">
                Registrierung erfolgreich! <a href="login.php">Jetzt einloggen →</a>
            </div>
        <?php else: ?>

            <?php if (!empty($errors)): ?>
                <div class="auth-fehler">
                    <?php foreach ($errors as $error): ?>
                        <p><?= esc($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="auth-feld">
                    <label class="auth-label" for="username">Benutzername</label>
                    <input class="auth-input" type="text" id="username" name="username"
                           value="<?= esc($username) ?>" placeholder="max_mustermann" required>
                </div>
                <div class="auth-feld">
                    <label class="auth-label" for="email">E-Mail</label>
                    <input class="auth-input" type="email" id="email" name="email"
                           value="<?= esc($email) ?>" placeholder="max@beispiel.at" required>
                </div>
                <div class="auth-feld">
                    <!-- auth-hinweis für den Mindestlängen-Hinweis neben dem Label -->
                    <label class="auth-label" for="password">Passwort <span class="auth-hinweis">(min. 6 Zeichen)</span></label>
                    <input class="auth-input" type="password" id="password" name="password" required>
                </div>
                <button class="schaltflaeche schaltflaeche--primaer auth-submit" type="submit">Registrieren</button>
            </form>

        <?php endif; ?>
    </div>
</main>

<footer class="seitenfuss">
    <div class="seitenbreite seitenfuss__inhalt">
        <p>© <?= date('Y') ?> VibeSeat</p>
    </div>
</footer>
</body>
</html>