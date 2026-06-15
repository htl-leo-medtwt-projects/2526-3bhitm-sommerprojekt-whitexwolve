<?php
session_start();
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/data/functions.php';

function esc($value): string {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$showId   = (int)($_GET['show'] ?? $_POST['show'] ?? 0);
$seatsRaw = trim((string)($_GET['seats'] ?? $_POST['seats'] ?? ''));
$personen = max(1, (int)($_GET['personen'] ?? $_POST['personen'] ?? 1));

$seatList = array_values(array_filter(array_map('trim', explode(',', $seatsRaw))));
$show     = $showId ? getShowById($conn, $showId) : null;
$event    = $show;

$hallName     = $show['hall'] ?? 'VibeSeat Arena';
$showDisplay  = $show
    ? date('d.m.Y', strtotime($show['show_date'])) . ' um ' . substr($show['show_time'], 0, 5) . ' Uhr'
    : '';
$eventTitle   = $show['title'] ?? '';
$pricePerSeat = (float)($show['ticket_price'] ?? 0);
$totalPrice   = $pricePerSeat * count($seatList);

// E-Mail + Name aus Session vorausfüllen wenn eingeloggt
$loggedIn = isset($_SESSION['user_id']);
$vorname  = '';
$nachname = '';
$email    = $loggedIn ? ($_SESSION['user_email'] ?? '') : '';

$errors  = [];
$success = false;

if (!$show) {
    $errors[] = 'Die Vorstellung konnte nicht geladen werden.';
}
if (empty($seatList)) {
    $errors[] = 'Bitte wähle mindestens einen Sitzplatz aus.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vorname  = trim($_POST['vorname'] ?? '');
    $nachname = trim($_POST['nachname'] ?? '');

    if (!$loggedIn) {
        $email = trim($_POST['email'] ?? '');
    }

    if ($vorname === '')  $errors[] = 'Bitte gib deinen Vornamen ein.';
    if ($nachname === '') $errors[] = 'Bitte gib deinen Nachnamen ein.';

    if ($email === '') {
        $errors[] = 'Bitte gib deine E-Mail-Adresse ein.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Die E-Mail-Adresse ist ungültig.';
    }

    // Doppelt-Belegungs-Check
    if (empty($errors) && $showId) {
        $bereitsBelegt = [];
        $stmt = $conn->prepare(
            "SELECT seats FROM reservierungen WHERE show_id = ? AND status != 'storniert'"
        );
        $stmt->bind_param('i', $showId);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            foreach (explode(',', (string)$row['seats']) as $s) {
                $s = trim($s);
                if ($s !== '') $bereitsBelegt[] = $s;
            }
        }
        $stmt->close();

        $doppelte = array_values(array_intersect($seatList, $bereitsBelegt));
        if (!empty($doppelte)) {
            $errors[] = 'Diese Plätze sind leider nicht mehr frei: ' . implode(', ', $doppelte);
        }
    }

    if (empty($errors)) {
        $seatsString = implode(', ', $seatList);
        $status      = 'aktiv';

        if ($loggedIn) {
            $userId = (int)$_SESSION['user_id'];
            $stmt = $conn->prepare("
                INSERT INTO reservierungen
                    (show_id, event_title, show_display, seats, price_per_seat, total_price,
                     vorname, nachname, email, user_id, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'isssddsssis',
                $showId, $eventTitle, $showDisplay, $seatsString,
                $pricePerSeat, $totalPrice,
                $vorname, $nachname, $email,
                $userId, $status
            );
        } else {
            $stmt = $conn->prepare("
                INSERT INTO reservierungen
                    (show_id, event_title, show_display, seats, price_per_seat, total_price,
                     vorname, nachname, email, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param(
                'isssddssss',
                $showId, $eventTitle, $showDisplay, $seatsString,
                $pricePerSeat, $totalPrice,
                $vorname, $nachname, $email,
                $status
            );
        }

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = 'Die Reservierung konnte nicht gespeichert werden.';
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
    <title>Reservierung – VibeSeat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/auth.css">
    <link rel="stylesheet" href="assets/css/reservation.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/site-header.php'; ?>

<main class="reservation-page">
    <div class="seitenbreite">

        <?php if ($success): ?>

            <div class="reservation-erfolg">
                <div class="reservation-erfolg__icon">✓</div>
                <h1 class="reservation-erfolg__titel">Reservierung erfolgreich!</h1>
                <p class="reservation-erfolg__text">
                    Hallo <strong><?= esc($vorname) ?></strong>, deine Plätze sind gesichert.<br>
                    Bestätigung geht an <strong><?= esc($email) ?></strong>.
                </p>
                <div class="reservation-erfolg__details">
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Veranstaltung</span>
                        <span class="reservation-detail__wert"><?= esc($eventTitle) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Vorstellung</span>
                        <span class="reservation-detail__wert"><?= esc($showDisplay) ?></span>
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
                <?php if ($loggedIn): ?>
                    <a class="schaltflaeche schaltflaeche--primaer" href="profil.php">Meine Reservierungen</a>
                <?php else: ?>
                    <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Zurück zur Startseite</a>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <div class="reservation-kontext">
                <a class="reservation-kontext__zurueck" href="javascript:history.back()">← Zurück</a>
                <div class="reservation-kontext__info">
                    <span><?= esc($eventTitle) ?></span>
                    <span class="trenner">·</span>
                    <span><?= esc($showDisplay) ?></span>
                    <?php if (!empty($seatList)): ?>
                        <span class="trenner">·</span>
                        <span><?= esc(implode(', ', $seatList)) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="reservation-layout">
                <div class="reservation-box">
                    <h1 class="reservation-titel">Deine Angaben</h1>

                    <?php if (!empty($errors)): ?>
                        <div class="auth-fehler">
                            <?php foreach ($errors as $err): ?>
                                <p><?= esc($err) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($loggedIn): ?>
                        <p class="auth-untertitel" style="margin-bottom:1.25rem;">
                            Eingeloggt als <strong><?= esc($_SESSION['user_username'] ?? '') ?></strong>
                            · <?= esc($email) ?>
                            · <a href="profil.php">Profil bearbeiten</a>
                        </p>
                    <?php endif; ?>

                    <form method="POST" action="reservation.php">
                        <input type="hidden" name="show"     value="<?= esc((string)$showId) ?>">
                        <input type="hidden" name="seats"    value="<?= esc($seatsRaw) ?>">
                        <input type="hidden" name="personen" value="<?= esc((string)$personen) ?>">

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

                        <?php if ($loggedIn): ?>
                            <input type="hidden" name="email" value="<?= esc($email) ?>">
                        <?php else: ?>
                            <div class="feld">
                                <label class="feld__label" for="email">E-Mail</label>
                                <input class="feld__input" type="email" id="email" name="email"
                                       value="<?= esc($email) ?>" placeholder="max@beispiel.at" required>
                            </div>
                        <?php endif; ?>

                        <button class="schaltflaeche schaltflaeche--primaer reservation-submit" type="submit">
                            Jetzt reservieren
                        </button>
                    </form>
                </div>

                <aside class="reservation-zusammenfassung">
                    <h2>Zusammenfassung</h2>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Event</span>
                        <span class="reservation-detail__wert"><?= esc($eventTitle) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Vorstellung</span>
                        <span class="reservation-detail__wert"><?= esc($showDisplay) ?></span>
                    </div>
                    <div class="reservation-detail">
                        <span class="reservation-detail__label">Ort</span>
                        <span class="reservation-detail__wert"><?= esc($hallName) ?></span>
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
                        Die Plätze werden direkt in deiner Reservierung gespeichert.
                    </p>
                </aside>
            </div>

        <?php endif; ?>
    </div>
</main>

<?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>
