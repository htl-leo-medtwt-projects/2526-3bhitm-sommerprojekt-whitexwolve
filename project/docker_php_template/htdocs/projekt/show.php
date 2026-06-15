<?php
session_start();
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/data/functions.php';

function esc($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$showId = (int)($_GET['show'] ?? 0);
$show   = $showId ? getShowById($conn, $showId) : null;
$event  = $show; // show enthält bereits title, description, category vom JOIN
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $event ? esc($event['title']) . ' – VibeSeat' : 'Vorstellung | VibeSeat' ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/show.css">
</head>
<body>
<?php require_once __DIR__ . '/partials/site-header.php'; ?>

<main class="show">
    <div class="seitenbreite">

        <a class="show-zurueck" href="index.php">← Zurück zur Übersicht</a>

        <?php if ($show && $event): ?>

            <div class="show-box">

                <span class="show-box__kategorie"><?= esc($event['category']) ?></span>

                <h1><?= esc($event['title']) ?></h1>

                <p class="show-box__beschreibung"><?= esc($event['description']) ?></p>

                <hr class="show-box__trennlinie">

                <div class="show-box__meta">
                    <div class="show-meta-eintrag">
                        <span class="show-meta-eintrag__label">Vorstellung</span>
                        <span class="show-meta-eintrag__wert">
                            <?= esc(date('d.m.Y', strtotime($show['show_date'])) . ' um ' . substr($show['show_time'], 0, 5) . ' Uhr') ?>
                        </span>
                    </div>
                    <div class="show-meta-eintrag">
                        <span class="show-meta-eintrag__label">Ort</span>
                        <span class="show-meta-eintrag__wert"><?= esc($show['hall'] ?? 'VibeSeat Arena') ?></span>
                    </div>
                    <div class="show-meta-eintrag">
                        <span class="show-meta-eintrag__label">Saal</span>
                        <span class="show-meta-eintrag__wert"><?= esc($show['hall'] ?? 'Hauptsaal') ?></span>
                    </div>
                    <div class="show-meta-eintrag">
                        <span class="show-meta-eintrag__label">Preis</span>
                        <span class="show-meta-eintrag__wert">€ <?= number_format((float)$show['ticket_price'], 2, ',', '.') ?></span>
                    </div>
                </div>

                <hr class="show-box__trennlinie">

                <div class="show-box__aktionen">
                    <a class="schaltflaeche schaltflaeche--sekundaer" href="index.php">
                        Zurück
                    </a>
                    <a class="schaltflaeche schaltflaeche--primaer" href="vibe.php?show=<?= urlencode((string)$showId) ?>">
                        Sitzplätze auswählen →
                    </a>
                </div>

            </div>

        <?php else: ?>

            <div class="show-box">
                <h1>Vorstellung nicht gefunden</h1>
                <p class="show-box__beschreibung">Diese Vorstellung konnte nicht geladen werden.</p>
                <div class="show-box__aktionen">
                    <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Zurück zur Startseite</a>
                </div>
            </div>

        <?php endif; ?>

    </div>
</main>

<?php require_once __DIR__ . '/partials/site-footer.php'; ?>
</body>
</html>