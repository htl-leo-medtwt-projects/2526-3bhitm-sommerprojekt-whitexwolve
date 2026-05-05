<?php
require_once __DIR__ . '/data/events.php';
require_once __DIR__ . '/data/db.php';

function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$showId   = '';
$seats    = '';
$personen = 1;

if (isset($_GET['show'])) {
    $showId = $_GET['show'];
}
if (isset($_POST['show'])) {
    $showId = $_POST['show'];
}

if (isset($_GET['seats'])) {
    $seats = $_GET['seats'];
}
if (isset($_POST['seats'])) {
    $seats = $_POST['seats'];
}

if (isset($_GET['personen'])) {
    $personen = $_GET['personen'];
}
if (isset($_POST['personen'])) {
    $personen = $_POST['personen'];
}

$show  = getShowById($showId);
$event = getEventById($show['event_id']);

$seatList     = explode(',', $seats);
$pricePerSeat = $event['price'];
$totalPrice   = $pricePerSeat * count($seatList);

$errors   = [];
$success  = false;
$vorname  = '';
$nachname = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $vorname  = trim($_POST['vorname']);
    $nachname = trim($_POST['nachname']);
    $email    = trim($_POST['email']);

    if ($vorname === '') {
        $errors[] = 'Bitte gib deinen Vornamen ein.';
    }

    if ($nachname === '') {
        $errors[] = 'Bitte gib deinen Nachnamen ein.';
    }

    if ($email === '') {
        $errors[] = 'Bitte gib deine E-Mail-Adresse ein.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Die E-Mail-Adresse ist ungültig.';
    }

    if (empty($errors)) {

        // Schreiben der Reservierung in die Datenbank erfolgt mit Hilfe von KI.
        $eventTitle  = $event['title'];
        $showDisplay = $show['display'];
        $seatsString = implode(', ', $seatList);
        $seatCount   = count($seatList);

        // 1. SQL vorbereiten – ? sind Platzhalter, noch keine echten Werte
        $stmt = $conn->prepare('INSERT INTO reservations (show_id, event_title, show_display, seats, seat_count, price_per_seat, total_price, vorname, nachname, email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        
        // 2. Echte Werte an die ? binden – 'ssssiddss' = Typen der Werte (s=string, i=integer, d=double)
        $stmt->bind_param('ssssiddsss', $showId, $eventTitle, $showDisplay, $seatsString, $seatCount, $pricePerSeat, $totalPrice, $vorname, $nachname, $email);
        
        // 3. Statement ausführen – die Werte werden jetzt in die Datenbank geschrieben
        $stmt->execute();

        // 4. Statement schließen
        $stmt->close();

        $success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservierung – VibeSeat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/reservation.css">
</head>
<body>
<main class="reservation-page">
    <div class="seitenbreite">

        <?php if ($success): ?>

            <div class="reservation-erfolg">
                <div class="reservation-erfolg__icon">✓</div>
                <h1 class="reservation-erfolg__titel">Reservierung erfolgreich</h1>
                <p class="reservation-erfolg__text">
                    Hallo <?= esc($vorname) ?>, deine Reservierung wurde entgegengenommen.<br>
                    Eine Bestätigung wird an <strong><?= esc($email) ?></strong> gesendet.
                </p>
                <div class="reservation-erfolg__details">
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Event</span>
                        <span class="reservation-detail__wert"><?= esc($event['title']) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Vorstellung</span>
                        <span class="reservation-detail__wert"><?= esc($show['display']) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Plätze</span>
                        <span class="reservation-detail__wert"><?= esc(implode(', ', $seatList)) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Gesamt</span>
                        <span class="reservation-detail__wert">€ <?= number_format($totalPrice, 2, ',', '.') ?></span>
                    </div>
                </div>
                <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Zurück zur Startseite</a>
            </div>

        <?php else: ?>

            <div class="reservation-kontext">
                <a class="reservation-kontext__zurueck" href="javascript:history.back()">← Zurück</a>
                <div class="reservation-kontext__info">
                    <span><?= esc($event['title']) ?></span>
                    <span class="trenner">·</span>
                    <span><?= esc($show['display']) ?></span>
                    <span class="trenner">·</span>
                    <span><?= esc(implode(', ', $seatList)) ?></span>
                </div>
            </div>

            <div class="reservation-layout">

                <div class="reservation-box">
                    <h1 class="reservation-titel">Deine Angaben</h1>

                    <?php if (!empty($errors)): ?>
                        <div class="reservation-fehler">
                            <?php foreach ($errors as $error): ?>
                                <p><?= esc($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="reservation.php">
                        <input type="hidden" name="show"     value="<?= esc($showId) ?>">
                        <input type="hidden" name="seats"    value="<?= esc($seats) ?>">
                        <input type="hidden" name="personen" value="<?= esc($personen) ?>">

                        <div class="feld">
                            <label class="feld__label" for="vorname">Vorname</label>
                            <input class="feld__input" type="text" id="vorname" name="vorname"
                                value="<?= esc($vorname) ?>" placeholder="Max" required>
                        </div>

                        <div class="feld">
                            <label class="feld__label" for="nachname">Nachname</label>
                            <input class="feld__input" type="text" id="nachname" name="nachname"
                                value="<?= esc($nachname) ?>" placeholder="Mustermann" required>
                        </div>

                        <div class="feld">
                            <label class="feld__label" for="email">E-Mail</label>
                            <input class="feld__input" type="email" id="email" name="email"
                                value="<?= esc($email) ?>" placeholder="max@beispiel.at" required>
                        </div>

                        <button class="schaltflaeche schaltflaeche--primaer reservation-submit" type="submit">
                            Jetzt reservieren
                        </button>
                    </form>
                </div>

                <aside class="reservation-zusammenfassung">
                    <h2>Zusammenfassung</h2>

                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Event</span>
                        <span class="reservation-detail__wert"><?= esc($event['title']) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Vorstellung</span>
                        <span class="reservation-detail__wert"><?= esc($show['display']) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Ort</span>
                        <span class="reservation-detail__wert"><?= esc(HALL_NAME) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Plätze</span>
                        <span class="reservation-detail__wert"><?= esc(implode(', ', $seatList)) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Preis/Sitz</span>
                        <span class="reservation-detail__wert">€ <?= number_format($pricePerSeat, 2, ',', '.') ?></span>
                    </div>
                    <div class="reservation-detail reservation-detail--gesamt">
                        <span class="reservation-detail__label">Gesamt</span>
                        <span class="reservation-detail__wert">€ <?= number_format($totalPrice, 2, ',', '.') ?></span>
                    </div>

                    <p class="reservation-hinweis">
                        Reservierung gilt für <?= RESERVATION_TIMEOUT_MINUTES ?> Minuten.
                    </p>
                </aside>

            </div>

        <?php endif; ?>

    </div>
</main>
</body>
</html>