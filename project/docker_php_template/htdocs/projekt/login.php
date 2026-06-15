<?php
session_start();
require_once __DIR__ . '/data/db.php';

function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errors   = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if ($username === '') $errors[] = 'Bitte gib deinen Benutzernamen ein.';
    if ($password === '') $errors[] = 'Bitte gib dein Passwort ein.';

    if (empty($errors)) {
        $stmt = $conn->prepare('SELECT id, username, email, password_hash FROM users WHERE username = ?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id']       = $user['id'];
            $_SESSION['user_username'] = $user['username'];
            $_SESSION['user_email']    = $user['email'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Benutzername oder Passwort falsch.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – VibeSeat</title>
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
            <a class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" href="register.php">Registrieren</a>
            <a class="schaltflaeche schaltflaeche--primaer schaltflaeche--klein hauptnavigation__link--aktiv" href="login.php">Login</a>
        </nav>
    </div>
</header>

<main class="auth-seite">
    <div class="auth-box">
        <h1 class="auth-titel">Willkommen zurück</h1>
        <p class="auth-untertitel">Noch kein Konto? <a href="register.php">Jetzt registrieren</a></p>

        <?php if (!empty($errors)): ?>
            <div class="auth-fehler">
                <?php foreach ($errors as $error): ?>
                    <p><?= esc($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="auth-feld">
                <label class="auth-label" for="username">Benutzername</label>
                <input class="auth-input" type="text" id="username" name="username" value="<?= esc($username) ?>" placeholder="max_mustermann" required>
            </div>
            <div class="auth-feld">
                <label class="auth-label" for="password">Passwort</label>
                <input class="auth-input" type="password" id="password" name="password" required>
            </div>
            <button class="schaltflaeche schaltflaeche--primaer auth-submit" type="submit">Einloggen</button>
        </form>
    </div>
</main>

<footer class="seitenfuss">
    <div class="seitenbreite seitenfuss__inhalt">
        <p>© <?= date('Y') ?> VibeSeat</p>
    </div>
</footer>
</body>
</html>
