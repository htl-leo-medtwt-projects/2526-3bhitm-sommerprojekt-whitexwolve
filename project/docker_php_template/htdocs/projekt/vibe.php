<?php
session_start();
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/data/functions.php';

function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

$showId = intval($_GET['show'] ?? 0);
$show   = $showId ? getShowById($conn, $showId) : null;
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seat Vibes – VibeSeat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/vibe.css">
</head>
<body>

<main class="vibe-page">
    <div class="seitenbreite">

        <?php if ($show): ?>

            <div class="vibe-kontext">
                <a class="vibe-kontext__zurueck" href="show.php?show=<?= urlencode((string)$showId) ?>">
                    ← Zurück
                </a>
                <div class="vibe-kontext__info">
                    <span><?= esc($show['title']) ?></span>
                    <span class="trenner">·</span>
                    <span>
                        <?= esc(date('d.m.Y', strtotime($show['show_date']))) ?>
                        um <?= esc(substr($show['show_time'], 0, 5)) ?> Uhr
                    </span>
                </div>
            </div>

            <div class="vibe-layout">

                <form class="vibe-formular" action="seat.php" method="get">
                    <input type="hidden" name="show" value="<?= esc((string)$showId) ?>">

                    <h1 class="vibe-titel">Deine Seat Vibes</h1>
                    <p class="vibe-untertitel">
                        Sag uns, was dir wichtig ist – wir zeigen dir die besten Plätze.
                    </p>

                    <div class="vibe-gruppe">
                        <label class="vibe-label" for="personen">Wie viele Personen?</label>
                        <div class="vibe-anzahl">
                            <button type="button" class="anzahl-btn" id="anzahlMinus">−</button>
                            <input type="number" id="personen" name="personen" value="1" min="1" max="20" class="anzahl-input" readonly>
                            <button type="button" class="anzahl-btn" id="anzahlPlus">+</button>
                        </div>
                    </div>

                    <div class="vibe-gruppe">
                        <span class="vibe-label">Was ist dir wichtig?</span>
                        <div class="vibe-optionen">

                            <label class="vibe-option">
                                <input type="checkbox" name="vibe[]" value="ruhig">
                                <span class="vibe-option__box">
                                    <span class="vibe-option__dot"></span>
                                    <span class="vibe-option__text">Ruhig</span>
                                    <span class="vibe-option__sub">Wenig Durchgang, kein Gangplatz</span>
                                </span>
                            </label>

                            <label class="vibe-option">
                                <input type="checkbox" name="vibe[]" value="mittendrin">
                                <span class="vibe-option__box">
                                    <span class="vibe-option__dot"></span>
                                    <span class="vibe-option__text">Mittendrin</span>
                                    <span class="vibe-option__sub">Zentrale Spalte und Reihe</span>
                                </span>
                            </label>

                            <label class="vibe-option">
                                <input type="checkbox" name="vibe[]" value="beste_sicht">
                                <span class="vibe-option__box">
                                    <span class="vibe-option__dot"></span>
                                    <span class="vibe-option__text">Beste Sicht</span>
                                    <span class="vibe-option__sub">Mittlere Reihen, Sweet Spot</span>
                                </span>
                            </label>

                            <label class="vibe-option">
                                <input type="checkbox" name="vibe[]" value="schnell_raus">
                                <span class="vibe-option__box">
                                    <span class="vibe-option__dot"></span>
                                    <span class="vibe-option__text">Schnell raus</span>
                                    <span class="vibe-option__sub">Nahe am Ausgang</span>
                                </span>
                            </label>

                        </div>
                    </div>

                    <div class="vibe-gruppe">
                        <span class="vibe-label">Wie möchtest du wählen?</span>
                        <div class="vibe-modus">

                            <label class="vibe-modus__option">
                                <input type="radio" name="modus" value="empfehlung" checked>
                                <span class="vibe-modus__box">
                                    <span class="vibe-modus__indicator"></span>
                                    <span class="vibe-modus__text">Beste Plätze vorschlagen</span>
                                </span>
                            </label>

                            <label class="vibe-modus__option">
                                <input type="radio" name="modus" value="selbst">
                                <span class="vibe-modus__box">
                                    <span class="vibe-modus__indicator"></span>
                                    <span class="vibe-modus__text">Selbst im Saalplan wählen</span>
                                </span>
                            </label>

                        </div>
                    </div>

                    <button class="schaltflaeche schaltflaeche--primaer vibe-submit" type="submit">
                        Saalplan anzeigen →
                    </button>

                </form>

                <aside class="vibe-info">
                    <h2>So funktioniert's</h2>
                    <ol class="vibe-schritte">
                        <li>Wähle deine Vibes und Personenanzahl</li>
                        <li>Wir berechnen einen Score für jeden Platz</li>
                        <li>Im Saalplan siehst du grün = top, gelb = okay, grau = nicht ideal</li>
                        <li>Klicke Plätze an oder lass sie automatisch vorschlagen</li>
                    </ol>
                </aside>

            </div>

        <?php else: ?>

            <section class="seat-box">
                <h1>Vorstellung nicht gefunden</h1>
                <p>Diese Vorstellung konnte nicht geladen werden.</p>
                <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Zurück</a>
            </section>

        <?php endif; ?>

    </div>
</main>

<script src="assets/js/vibe.js"></script>
</body>
</html>
