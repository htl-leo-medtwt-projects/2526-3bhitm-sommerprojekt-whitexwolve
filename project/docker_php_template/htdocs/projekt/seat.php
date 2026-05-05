<?php
require_once __DIR__ . '/data/events.php';
require_once __DIR__ . '/data/halls.php';

function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// ─── URL-Parameter einlesen ───────────────────────────────────────────────────
$showId   = $_GET['show']     ?? null;
$personen = max(1, (int)($_GET['personen'] ?? 1));
$vibes    = $_GET['vibe']     ?? [];   // Array z.B. ['ruhig', 'beste_sicht']
$modus    = $_GET['modus']    ?? 'empfehlung'; // 'empfehlung' oder 'selbst'

// Simulierte belegte Sitze (später aus DB)
$taken = ['A3', 'A4', 'B5', 'C2', 'D7', 'E3', 'E4', 'E5'];

// ─── Event + Show laden ───────────────────────────────────────────────────────
$show  = null;
$event = null;

if ($showId) {
    $show = getShowById($showId);
    if ($show) {
        $event = getEventById($show['event_id']);
    }
}

// ─── Saal-Layout für diese Show bestimmen ────────────────────────────────────
$layout = $showId ? getLayoutForShow($showId) : $hall_layouts['kino'];
$seats  = $layout['seats'];

// ─── Score-Algorithmus ───────────────────────────────────────────────────────
// Jeder Sitz bekommt einen Score 0–100 basierend auf den gewählten Vibes.
// Mehrere Vibes werden gleichwertig kombiniert (Durchschnitt der Einzelscores).
//
// Vibe-Logik:
//   ruhig       → kein Gangplatz (+), mittlere/hintere Reihe (+), weg vom Gang
//   mittendrin  → Spalte nah an Reihenmitte (+), Reihe nah an Saalmitte (+)
//   beste_sicht → "Sweet Spot" = ca. 40–60% der Reihentiefe vom Bühnenende
//   schnell_raus → niedrige exit_distance (nah am Ausgang) = hoher Score

function scoreForSeat(array $seat, array $vibes): int
{
    if (empty($vibes)) {
        // Kein Vibe gewählt → alle Sitze gleich, Score 50
        return 50;
    }

    $scores = [];

    foreach ($vibes as $vibe) {
        $rowRatio = $seat['row_index'] / $seat['row_count'];       // 0 = vorne, 1 = hinten
        $colMid   = ($seat['cols_in_row'] + 1) / 2;               // Spaltenmitte
        $colDist  = abs($seat['col'] - $colMid) / $colMid;        // 0 = Mitte, 1 = Rand

        switch ($vibe) {
            case 'ruhig':
                // Kein Gangplatz → hoher Score; je weiter innen, desto besser
                $aisleBonus = $seat['is_aisle'] ? 0 : 30;
                $innerScore = (int)((1 - $colDist) * 50);
                $backBonus  = (int)($rowRatio * 20); // etwas weiter hinten = ruhiger
                $scores[]   = min(100, $aisleBonus + $innerScore + $backBonus);
                break;

            case 'mittendrin':
                // Nah an Spalten- UND Reihenmitte
                $colScore = (int)((1 - $colDist) * 50);
                $rowMidDist = abs($rowRatio - 0.5) * 2; // 0 = Mitte, 1 = Rand
                $rowScore   = (int)((1 - $rowMidDist) * 50);
                $scores[]   = $colScore + $rowScore;
                break;

            case 'beste_sicht':
                // Sweet Spot: 35–65% der Saaldistanz von vorne
                // Senkrecht: möglichst nah an der Reihenmitte
                $sweetSpot  = abs($rowRatio - 0.5);  // 0 = perfekte Mitte, 0.5 = Rand
                $rowScore   = (int)((1 - $sweetSpot * 2) * 60);
                $colScore   = (int)((1 - $colDist) * 40);
                $scores[]   = max(0, $rowScore) + $colScore;
                break;

            case 'schnell_raus':
                // exit_distance 1 = nah → Score hoch; 10 = weit → Score niedrig
                $scores[] = (int)((1 - ($seat['exit_distance'] - 1) / 9) * 100);
                break;
        }
    }

    // Durchschnitt aller Vibe-Scores
    return (int)(array_sum($scores) / count($scores));
}

// Score für alle Sitze berechnen und Tier zuweisen
// Tier: 'top' (≥70), 'ok' (40–69), 'low' (<40)
foreach ($seats as &$seat) {
    $seat['score'] = scoreForSeat($seat, $vibes);
    if ($seat['score'] >= 70)      $seat['tier'] = 'top';
    elseif ($seat['score'] >= 40)  $seat['tier'] = 'ok';
    else                           $seat['tier'] = 'low';

    $seat['is_taken'] = in_array($seat['id'], $taken);
}
unset($seat); // Referenz aufheben nach foreach mit &

// ─── Automatische Empfehlung berechnen ───────────────────────────────────────
// Bei Modus 'empfehlung': die $personen besten zusammenhängenden freien Sitze finden.
// "Zusammenhängend" = gleiche Reihe, aufeinanderfolgende Spalten.
$empfehlung = [];

if ($modus === 'empfehlung') {
    // Sitze nach Score sortieren (höchster zuerst), belegte ausschließen
    $freeSeats = array_filter($seats, fn($s) => !$s['is_taken']);

    // Reihen gruppieren
    $byRow = [];
    foreach ($freeSeats as $s) {
        $byRow[$s['row']][] = $s;
    }

    $bestGroup      = [];
    $bestGroupScore = -1;

    foreach ($byRow as $rowLetter => $rowSeats) {
        // Sitze in der Reihe nach Spalte sortieren
        usort($rowSeats, fn($a, $b) => $a['col'] - $b['col']);

        // Sliding Window der Größe $personen über die Reihe
        $count = count($rowSeats);
        for ($i = 0; $i <= $count - $personen; $i++) {
            // Prüfen ob die Sitze wirklich aufeinanderfolgend sind (keine Lücke)
            $group     = array_slice($rowSeats, $i, $personen);
            $cols      = array_column($group, 'col');
            $isConsec  = (max($cols) - min($cols) === $personen - 1);

            if (!$isConsec) continue;

            $groupScore = (int)(array_sum(array_column($group, 'score')) / $personen);

            if ($groupScore > $bestGroupScore) {
                $bestGroupScore = $groupScore;
                $bestGroup      = array_column($group, 'id');
            }
        }
    }

    $empfehlung = $bestGroup;
}

// ─── Sitze nach Reihe gruppieren (für Ausgabe im HTML) ───────────────────────
$seatsByRow = [];
foreach ($seats as $s) {
    $seatsByRow[$s['row']][] = $s;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sitzplatz wählen – VibeSeat</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/seat.css">
</head>
<body>

<main class="seat-page">
    <div class="seitenbreite">

        <?php if ($show && $event): ?>

            <div class="seat-kontext">
                <a class="seat-kontext__zurueck" href="vibe.php?show=<?= urlencode((string)$showId) ?>">
                    ← Zurück zu Vibes
                </a>
                <div class="seat-kontext__info">
                    <span class="seat-kontext__event"><?= esc($event['title']) ?></span>
                    <span class="seat-kontext__trenner">·</span>
                    <span class="seat-kontext__zeit"><?= esc($show['display']) ?></span>
                    <span class="seat-kontext__trenner">·</span>
                    <span class="seat-kontext__ort"><?= esc(HALL_NAME) ?> – <?= esc($layout['name']) ?></span>
                </div>
            </div>

            <?php if ($modus === 'empfehlung' && !empty($empfehlung)): ?>
                <div class="empfehlung-banner">
                    ✦ Empfehlung für <?= $personen ?> Person<?= $personen > 1 ? 'en' : '' ?>:
                    <strong><?= esc(implode(', ', $empfehlung)) ?></strong>
                    – basierend auf deinen Vibes
                </div>
            <?php endif; ?>

            <div class="seat-layout">

                <section class="seat-box" data-price="<?= esc((string)($event['price'] ?? 0)) ?>">
                    <h1>Sitzplatz wählen</h1>
                    <div class="screen">LEINWAND / BÜHNE</div>

                    <div class="seat-grid" style="--cols: <?= max(array_map(fn($r) => count($r), $seatsByRow)) ?>">
                        <?php foreach ($seatsByRow as $rowLetter => $rowSeats): ?>
                            <div class="seat-row">
                                <span class="seat-row__label"><?= esc($rowLetter) ?></span>
                                <div class="seat-row__seats">
                                    <?php foreach ($rowSeats as $s): ?>
                                        <?php
                                            $isEmpfohlen = in_array($s['id'], $empfehlung);
                                            $classes = 'seat';
                                            if ($s['is_taken'])          $classes .= ' seat--taken';
                                            elseif ($isEmpfohlen)        $classes .= ' seat--free seat--empfohlen seat--tier-' . $s['tier'];
                                            else                         $classes .= ' seat--free seat--tier-' . $s['tier'];
                                        ?>
                                        <button
                                            class="<?= $classes ?>"
                                            data-seat="<?= esc($s['id']) ?>"
                                            data-score="<?= $s['score'] ?>"
                                            title="<?= esc($s['id']) ?> · Score: <?= $s['score'] ?>"
                                            <?= $s['is_taken'] ? 'disabled aria-disabled="true"' : '' ?>
                                        >
                                            <?= esc($s['id']) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="seat-aktionen">
                        <div class="seat-auswahl-info">
                            <span id="seatAnzahl">0</span> / <?= $personen ?> Plätze gewählt –
                            Gesamt: € <span id="seatPreis">0,00</span>
                        </div>
                        <button class="schaltflaeche schaltflaeche--primaer" id="reservierenButton" disabled>
                            Zur Reservierung
                        </button>
                    </div>
                </section>

                <aside class="legend-box">
                    <h2>Legende</h2>
                    <p><span class="legend-color legend-top"></span> Top Match</p>
                    <p><span class="legend-color legend-ok"></span> Okay</p>
                    <p><span class="legend-color legend-low"></span> Nicht ideal</p>
                    <p><span class="legend-color legend-taken"></span> Belegt</p>
                    <p><span class="legend-color legend-selected"></span> Ausgewählt</p>

                    <hr>

                    <h2>Deine Auswahl</h2>
                    <ul id="seatListe" class="seat-liste">
                        <li class="seat-liste__leer">Noch kein Platz gewählt.</li>
                    </ul>

                    <hr>

                    <div class="legend-vibes">
                        <h2>Deine Vibes</h2>
                        <?php if (!empty($vibes)): ?>
                            <?php foreach ($vibes as $v): ?>
                                <span class="vibe-tag"><?= esc($v) ?></span>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <span class="legend-leer">Keine Vibes gewählt</span>
                        <?php endif; ?>
                    </div>
                </aside>

            </div>

        <?php else: ?>
            <section class="seat-box">
                <h1>Vorstellung nicht gefunden</h1>
                <p>Diese Vorstellung konnte nicht geladen werden.</p>
                <a class="schaltflaeche schaltflaeche--primaer" href="index.php">Zurück zur Übersicht</a>
            </section>
        <?php endif; ?>

    </div>
</main>

<script>
    // Personen-Anzahl und Empfehlung aus PHP an JS übergeben
    const PERSONEN    = <?= $personen ?>;
    const EMPFEHLUNG  = <?= json_encode($empfehlung) ?>;
</script>
<script src="assets/js/seat.js"></script>
</body>
</html>