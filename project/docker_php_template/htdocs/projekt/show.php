<?php
require_once __DIR__ . '/data/events.php';

// hilfe von ki
function esc($text)
{
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

// hilfe von ki
$showId = $_GET['show'] ?? '';

$show = null;
$event = null;

if ($showId != '') {
    $show = getShowById($showId);
}

if ($show) {
    $eventId = $show['event_id'];
    $event = getEventById($eventId);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vorstellung | VibeSeat</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/show.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/site-header.php'; ?>

    <main class="show">
        <div class="seitenbreite">
            <?php if ($show && $event): ?>
                <section class="show-box">
                    <h1><?= esc($event['title']) ?></h1>

                    <p><strong>Vorstellung:</strong> <?= esc($show['display']) ?></p>
                    <p><strong>Kategorie:</strong> <?= esc($event['category']) ?></p>
                    <p><strong>Ort:</strong> <?= esc(HALL_NAME) ?></p>
                    <!-- hilfe von ki -->
                    <p><strong>Preis:</strong> € <?= number_format($event['price'], 2, ',', '.') ?></p>

                    <p class="beschreibung">
                        <?= esc($event['description']) ?>
                    </p>

                    <a class="schaltflaeche schaltflaeche--primaer" href="vibe.php?show=<?= $showId ?>">
                        Sitzplätze auswählen
                    </a>
                </section>
            <?php else: ?>
                <section class="show-box">
                    <h1>Vorstellung nicht gefunden</h1>
                    <p>Diese Vorstellung konnte nicht geladen werden.</p>
                    <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Zurück</a>
                </section>
            <?php endif; ?>
        </div>
      
    </main>
      <?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>