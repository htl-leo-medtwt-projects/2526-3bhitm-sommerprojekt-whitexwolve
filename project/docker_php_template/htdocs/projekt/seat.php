<?php
session_start();
require_once __DIR__ . '/data/db.php';
require_once __DIR__ . '/data/functions.php';

// Ausgabe gegen XSS absichern
function esc($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

// ─── Parameter aus URL ────────────────────────────────────────────────────────
$showId   = (int)($_GET['show']     ?? 0);
$personen = max(1, (int)($_GET['personen'] ?? 1));
// modus: 'empfehlung' = beste Sitze automatisch vorschlagen, 'selbst' = User wählt frei
$modus    = in_array($_GET['modus'] ?? '', ['empfehlung', 'selbst']) ? $_GET['modus'] : 'selbst';
$vibes    = isset($_GET['vibe']) ? (array)$_GET['vibe'] : [];


// ─── Belegte Sitze aus DB laden ───────────────────────────────────────────────
// stornierte Reservierungen werden ignoriert
$taken = [];
if ($showId) {
    $stmt = $conn->prepare(
        "SELECT seats FROM reservierungen WHERE show_id = ? AND status != 'storniert'"
    );
    $stmt->bind_param('i', $showId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        foreach (explode(',', (string)$row['seats']) as $sitz) {
            $sitz = trim($sitz);
            if ($sitz !== '') $taken[] = $sitz;
        }
    }
    $stmt->close();
}


// ─── Show + Event laden ───────────────────────────────────────────────────────
$show  = $showId ? getShowById($conn, $showId) : null;
$event = $show; // getShowById liefert per JOIN bereits alle Event-Felder


// ─── Saal-Layout dynamisch aus DB aufbauen ────────────────────────────────────
// Reihen A–X, Spalten 1–N — Werte kommen aus saele.anzahl_reihen / anzahl_spalten
$seats = [];
if ($show) {
    $rows    = (int)($show['anzahl_reihen']  ?? 8);
    $cols    = (int)($show['anzahl_spalten'] ?? 10);
    $letters = range('A', chr(ord('A') + $rows - 1));

    foreach ($letters as $rowIndex => $rowLetter) {
        for ($col = 1; $col <= $cols; $col++) {
            $seatId  = $rowLetter . $col;
            $isAisle = ($col === 1 || $col === $cols); // äußerste Spalten = Gangplätze
            $exitDist = min(10, max(1, (int)round(
                (abs($col - 1) + abs($rowIndex)) / max(1, $rows + $cols) * 9 + 1
            )));
            $seats[] = [
                'id'           => $seatId,
                'row'          => $rowLetter,
                'row_index'    => $rowIndex,
                'row_count'    => $rows,
                'col'          => $col,
                'cols_in_row'  => $cols,
                'is_aisle'     => $isAisle,
                'exit_distance'=> $exitDist,
            ];
        }
    }
}


// ─── Score-Algorithmus ────────────────────────────────────────────────────────
// berechnet einen Wert 0–100 pro Sitz basierend auf den gewählten Vibes
// (Logik in Kooperation mit KI entwickelt)
function scoreForSeat(array $seat, array $vibes): int {
    if (empty($vibes)) return 50;
    $scores = [];
    foreach ($vibes as $vibe) {
        $rowRatio = $seat['row_count'] > 1 ? $seat['row_index'] / ($seat['row_count'] - 1) : 0;
        $colMid   = ($seat['cols_in_row'] + 1) / 2;
        $colDist  = $colMid > 0 ? abs($seat['col'] - $colMid) / $colMid : 0;
        switch ($vibe) {
            case 'ruhig':
                // Mitte + hinten bevorzugt, Gangplätze vermeiden
                $scores[] = min(100, ($seat['is_aisle'] ? 0 : 30) + (int)((1 - $colDist) * 50) + (int)($rowRatio * 20));
                break;
            case 'mittendrin':
                // Mitte der Reihe + mittlere Reihen
                $rowMidDist = abs($rowRatio - 0.5) * 2;
                $scores[] = (int)((1 - $colDist) * 50) + (int)((1 - $rowMidDist) * 50);
                break;
            case 'beste_sicht':
                // Sweet-Spot: nicht zu weit vorne, nicht zu weit hinten — und zentral
                $sweetSpot = abs($rowRatio - 0.5);
                $scores[] = max(0, (int)((1 - $sweetSpot * 2) * 60)) + (int)((1 - $colDist) * 40);
                break;
            case 'schnell_raus':
                // niedrige exit_distance = nah am Ausgang = hoher Score
                $scores[] = (int)((1 - ($seat['exit_distance'] - 1) / 9) * 100);
                break;
        }
    }
    return (int)(array_sum($scores) / count($scores));
}

// Score + Tier für alle Sitze berechnen
foreach ($seats as &$seat) {
    $seat['score']    = scoreForSeat($seat, $vibes);
    $seat['tier']     = $seat['score'] >= 70 ? 'top' : ($seat['score'] >= 40 ? 'ok' : 'low');
    $seat['is_taken'] = in_array($seat['id'], $taken);
}
unset($seat); // Referenz aus foreach entfernen


// ─── Empfehlung berechnen ─────────────────────────────────────────────────────
// sucht die zusammenhängende Gruppe freier Sitze mit dem höchsten Durchschnits-Score
$empfehlung = [];
if ($modus === 'empfehlung') {
    $freeSeats = array_filter($seats, fn($s) => !$s['is_taken']);
    $byRow = [];
    foreach ($freeSeats as $s) $byRow[$s['row']][] = $s;

    $bestGroup = [];
    $bestScore = -1;
    foreach ($byRow as $rowSeats) {
        usort($rowSeats, fn($a, $b) => $a['col'] - $b['col']);
        $count = count($rowSeats);
        for ($i = 0; $i <= $count - $personen; $i++) {
            $group = array_slice($rowSeats, $i, $personen);
            $cols  = array_column($group, 'col');
            // nur lückenlose Blöcke gelten als Gruppe
            if (max($cols) - min($cols) !== $personen - 1) continue;
            $groupScore = (int)(array_sum(array_column($group, 'score')) / $personen);
            if ($groupScore > $bestScore) {
                $bestScore = $groupScore;
                $bestGroup = array_column($group, 'id');
            }
        }
    }
    $empfehlung = $bestGroup;
}


// ─── Sitze nach Reihe gruppieren für HTML-Ausgabe ────────────────────────────
$seatsByRow = [];
foreach ($seats as $s) $seatsByRow[$s['row']][] = $s;

$pricePerSeat = (float)($show['ticket_price'] ?? 0);
$showDisplay  = $show
    ? date('d.m.Y', strtotime($show['show_date'])) . ' um ' . substr($show['show_time'], 0, 5) . ' Uhr'
    : '';
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
<?php require_once __DIR__ . '/partials/site-header.php'; ?>

<main class="seat-page">
    <div class="seitenbreite">

        <?php if ($show && $event): ?>

            <div class="seat-kontext">
                <a class="seat-kontext__zurueck" href="vibe.php?show=<?= urlencode((string)$showId) ?>">← Zurück</a>
                <div class="seat-kontext__info">
                    <span><?= esc($event['title']) ?></span>
                    <span class="trenner">·</span>
                    <span><?= esc($showDisplay) ?></span>
                </div>
            </div>

            <div class="seat-layout">

                <!-- Saalplan — data-* Attribute werden von seat.js gelesen -->
                <section
                    class="seat-box"
                    data-price="<?= esc((string)$pricePerSeat) ?>"
                    data-show="<?= esc((string)$showId) ?>"
                    data-personen="<?= esc((string)$personen) ?>"
                    data-modus="<?= esc($modus) ?>"
                >
                    <div class="seat-buehne">
                        <span>🎭 Bühne</span>
                    </div>

                    <div class="seat-legende">
                        <span class="seat-legende__item seat-legende__item--top">Top</span>
                        <span class="seat-legende__item seat-legende__item--ok">Ok</span>
                        <span class="seat-legende__item seat-legende__item--low">Niedrig</span>
                        <span class="seat-legende__item seat-legende__item--taken">Belegt</span>
                    </div>

                    <div class="seat-grid">
                        <?php foreach ($seatsByRow as $rowLetter => $rowSeats): ?>
                            <div class="seat-reihe">
                                <span class="seat-reihe__label"><?= esc($rowLetter) ?></span>
                                <?php foreach ($rowSeats as $seat): ?>
                                    <?php
                                        $classes = ['seat'];
                                        if ($seat['is_taken']) {
                                            $classes[] = 'seat--taken';
                                        } else {
                                            $classes[] = 'seat--free';
                                            $classes[] = 'seat--' . $seat['tier'];
                                        }
                                        // empfohlene Sitze bekommen Glow-Ring (via seat.css)
                                        if (in_array($seat['id'], $empfehlung)) {
                                            $classes[] = 'seat--empfohlen';
                                        }
                                    ?>
                                    <button
                                        class="<?= esc(implode(' ', $classes)) ?>"
                                        data-seat="<?= esc($seat['id']) ?>"
                                        data-score="<?= esc((string)$seat['score']) ?>"
                                        <?= $seat['is_taken'] ? 'disabled' : '' ?>
                                        aria-label="Sitz <?= esc($seat['id']) ?><?= $seat['is_taken'] ? ' (belegt)' : ' (Score ' . $seat['score'] . ')' ?>"
                                    ><?= esc($seat['id']) ?></button>
                                <?php endforeach; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </section>

                <!-- Sidebar -->
                <aside class="seat-sidebar">
                    <h2 class="seat-sidebar__titel">Auswahl</h2>

                    <?php if (!empty($empfehlung)): ?>
                        <div class="seat-empfehlung">
                            <p class="seat-empfehlung__label">✨ Empfehlung</p>
                            <p class="seat-empfehlung__sitze"><?= esc(implode(', ', $empfehlung)) ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="seat-auswahl-info">
                        <div class="seat-auswahl-zeile">
                            <span>Ausgewählt</span>
                            <strong id="seatAnzahl">0</strong>
                        </div>
                        <div class="seat-auswahl-zeile">
                            <span>Gesamt</span>
                            <strong>€ <span id="seatPreis">0,00</span></strong>
                        </div>
                    </div>

                    <ul class="seat-liste" id="seatListe">
                        <li class="seat-liste__leer">Noch kein Platz gewählt.</li>
                    </ul>

                    <button
                        class="schaltflaeche schaltflaeche--primaer seat-reservieren"
                        id="reservierenButton"
                        disabled
                    >
                        Zur Reservierung
                    </button>
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

<?php require_once __DIR__ . '/partials/site-footer.php'; ?>

<!-- globale JS-Variablen für seat.js — müssen vor dem Script-Tag stehen -->
<script>
    const EVENT_TITEL = <?= json_encode($event['title'] ?? '') ?>;
    const SHOW_ZEIT   = <?= json_encode($showDisplay) ?>;
    const PERSONEN    = <?= (int)$personen ?>;
    const EMPFEHLUNG  = <?= json_encode($empfehlung) ?>;
</script>
<script src="assets/js/seat.js"></script>
</body>
</html>
