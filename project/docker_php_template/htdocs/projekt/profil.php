<?php
session_start();
require_once __DIR__ . '/data/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id']) && !isset($_FILES['profilbild'])) {
    $deleteId = (int)$_POST['delete_id'];
    $stmt = $conn->prepare('DELETE FROM reservierungen WHERE id = ? AND user_id = ?');
    $stmt->bind_param('ii', $deleteId, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->close();
    header('Location: profil.php');
    exit;
}

function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// Nur eingeloggte User dürfen diese Seite sehen
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId   = $_SESSION['user_id'];
$errors   = [];
$success  = '';

// Profilbild hochladen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilbild'])) {

    $file     = $_FILES['profilbild'];
    $erlaubte = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize  = 2 * 1024 * 1024; // 2 MB

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Fehler beim Upload.';
    } elseif (!in_array($file['type'], $erlaubte)) {
        $errors[] = 'Nur JPG, PNG oder WEBP erlaubt.';
    } elseif ($file['size'] > $maxSize) {
        $errors[] = 'Die Datei darf maximal 2 MB groß sein.';
    } else {
        // Dateiname sicher machen: user_ID.erweiterung
        $extension  = pathinfo($file['name'], PATHINFO_EXTENSION);
        $dateiname  = 'user_' . $userId . '.' . $extension;
        $zielordner = __DIR__ . '/uploads/';
        $zielpfad   = $zielordner . $dateiname;

        // Ordner anlegen falls nicht vorhanden
        if (!is_dir($zielordner)) {
            mkdir($zielordner, 0755, true);
        }

        if (move_uploaded_file($file['tmp_name'], $zielpfad)) {
            // Pfad in der Datenbank speichern
            $pfadInDb = 'uploads/' . $dateiname;
            $stmt = $conn->prepare('UPDATE users SET profilbild = ? WHERE id = ?');
            $stmt->bind_param('si', $pfadInDb, $userId);
            $stmt->execute();
            $stmt->close();

            // Session aktualisieren
            $_SESSION['user_profilbild'] = $pfadInDb;
            $success = 'Profilbild erfolgreich gespeichert!';
        } else {
            $errors[] = 'Datei konnte nicht gespeichert werden.';
        }
    }
}

// Aktuelle Userdaten aus DB holen
$stmt = $conn->prepare('SELECT username, email, profilbild, created_at FROM users WHERE id = ?');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();
$user   = $result->fetch_assoc();
$stmt->close();

// Reservierungen des Users holen
$stmt = $conn->prepare('SELECT id, event_title, show_display, seats, total_price, created_at FROM reservierungen WHERE user_id = ? ORDER BY created_at DESC');
$stmt->bind_param('i', $userId);
$stmt->execute();
$result       = $stmt->get_result();
$reservations = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mein Profil – VibeSeat</title>
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
            <a class="hauptnavigation__link hauptnavigation__link--aktiv" href="profil.php">
                <?php if (!empty($user['profilbild'])): ?>
                    <img class="nav-avatar" src="<?= esc($user['profilbild']) ?>" alt="Profilbild">
                <?php endif; ?>
                <?= esc($user['username']) ?>
            </a>
            <a class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" href="logout.php">Logout</a>
        </nav>
    </div>
</header>

<main class="seitenbreite profil-seite">

    <div class="profil-layout">

        <!-- Linke Spalte: Profilkarte -->
        <aside class="profil-karte">

            <div class="profil-avatar-bereich">
                <?php if (!empty($user['profilbild'])): ?>
                    <img class="profil-avatar" src="<?= esc($user['profilbild']) ?>" alt="Profilbild">
                <?php else: ?>
                    <div class="profil-avatar profil-avatar--platzhalter">
                        <?= strtoupper(substr($user['username'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
            </div>

            <h2 class="profil-name"><?= esc($user['username']) ?></h2>
            <p class="profil-email"><?= esc($user['email']) ?></p>
            <p class="profil-seit">Dabei seit <?= date('M Y', strtotime($user['created_at'])) ?></p>

            <!-- Profilbild hochladen -->
            <form method="POST" action="profil.php" enctype="multipart/form-data" class="profil-upload-form">

                <?php if (!empty($success)): ?>
                    <p class="auth-erfolg"><?= esc($success) ?></p>
                <?php endif; ?>

                <?php if (!empty($errors)): ?>
                    <div class="auth-fehler">
                        <?php foreach ($errors as $error): ?>
                            <p><?= esc($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <label class="auth-label" for="profilbild">Profilbild ändern</label>
                <input class="profil-file-input" type="file" id="profilbild" name="profilbild" accept="image/jpeg,image/png,image/webp">
                <button class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" type="submit">Hochladen</button>
            </form>

            <a class="schaltflaeche schaltflaeche--sekundaer schaltflaeche--klein" href="logout.php" style="margin-top:1rem;">Logout</a>
        </aside>

        <!-- Rechte Spalte: Reservierungen -->
        <section class="profil-reservierungen">
            <h2 class="profil-section-titel">Meine Reservierungen</h2>

            <?php if (empty($reservations)): ?>
                <div class="leerzustand">
                    <p class="leerzustand__text">Du hast noch keine Reservierungen.</p>
                    <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Events entdecken</a>
                </div>
            <?php else: ?>
                <div class="reservierungen-liste">
                    <?php foreach ($reservations as $res): ?>
                        <div class="reservierungs-karte">
                            <div class="reservierungs-karte__kopf">
                                <h3 class="reservierungs-karte__titel"><?= esc($res['event_title']) ?></h3>
                                <span class="reservierungs-karte__preis">€ <?= number_format($res['total_price'], 2, ',', '.') ?></span>
                            </div>
                            <p class="reservierungs-karte__info">📅 <?= esc($res['show_display']) ?></p>
                            <p class="reservierungs-karte__info">🎟️ <?= esc($res['seats']) ?></p>
                            <p class="reservierungs-karte__datum"><?= date('d.m.Y H:i', strtotime($res['created_at'])) ?> Uhr</p>
                            <form method="POST" action="profil.php">
                                <input type="hidden" name="delete_id" value="<?= (int)$res['id'] ?>">
                                <button type="submit" class="schaltflaeche schaltflaeche--sekundaer">
                                    Stornieren
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>

</main>

<footer class="seitenfuss">
    <div class="seitenbreite seitenfuss__inhalt">
        <p>© <?= date('Y') ?> VibeSeat</p>
    </div>
</footer>
</body>
</html>
