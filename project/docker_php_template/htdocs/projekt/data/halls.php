<?php
// data/halls.php
// 3 Saal-Layouts – jedes definiert Reihen und Sitze mit Metadaten für den Score-Algorithmus
//
// Pro Sitz:
//   id            → z.B. "B4"
//   row           → Reihenbuchstabe z.B. "B"
//   col           → Spaltennummer z.B. 4
//   row_index     → 1 = vorderste Reihe (Bühne/Leinwand nah)
//   cols_in_row   → Gesamtanzahl Sitze in dieser Reihe
//   row_count     → Gesamtanzahl Reihen im Saal
//   is_aisle      → 1 wenn Gangplatz (erste oder letzte Spalte der Reihe)
//   exit_distance → 1 = sehr nah am Ausgang, 10 = weit weg (Ausgang hinten/seitlich)

function buildHallLayout(string $name, array $rowConfig): array
{
    $seats     = [];
    $rowIndex  = 1;
    $rowCount  = count($rowConfig);

    foreach ($rowConfig as $rowLetter => $colCount) {
        for ($col = 1; $col <= $colCount; $col++) {
            // Gangplatz = ganz links oder ganz rechts in der Reihe
            $isAisle = ($col === 1 || $col === $colCount) ? 1 : 0;

            // Ausgang ist hinten (hoher row_index) und seitlich (col 1 oder col max)
            // → Kombination aus Reihenabstand zur letzten Reihe + Abstand zur nächsten Seite
            $distToBack  = $rowCount - $rowIndex;
            $distToSide  = min($col - 1, $colCount - $col);
            $exitDist    = max(1, min(10, (int)(($distToBack + $distToSide) / 2) + 1));

            $seats[] = [
                'id'          => $rowLetter . $col,
                'row'         => $rowLetter,
                'col'         => $col,
                'row_index'   => $rowIndex,
                'cols_in_row' => $colCount,
                'row_count'   => $rowCount,
                'is_aisle'    => $isAisle,
                'exit_distance' => $exitDist,
            ];
        }
        $rowIndex++;
    }

    return [
        'name'      => $name,
        'row_count' => $rowCount,
        'seats'     => $seats,
    ];
}

// Layout 1: Klassisches Kino – trapezförmig, breite Mitte
$hall_layouts['kino'] = buildHallLayout('Kino-Saal', [
    'A' => 10,
    'B' => 12,
    'C' => 14,
    'D' => 14,
    'E' => 14,
    'F' => 12,
    'G' => 10,
]);

// Layout 2: Kleines Theater – gleichmäßig, kompakt
$hall_layouts['theater'] = buildHallLayout('Theater-Saal', [
    'A' => 8,
    'B' => 8,
    'C' => 8,
    'D' => 8,
    'E' => 8,
]);

// Layout 3: Konzert-Arena – sehr breite Reihen
$hall_layouts['konzert'] = buildHallLayout('Konzert-Arena', [
    'A' => 16,
    'B' => 18,
    'C' => 20,
    'D' => 20,
    'E' => 18,
    'F' => 16,
]);


// crc32 der Show-ID ergibt immer dieselbe Zahl → dieselbe Show bekommt immer dasselbe Layout
function getLayoutForShow(string $showId): array
{
    global $hall_layouts;
    $keys  = array_keys($hall_layouts);
    $index = abs(crc32($showId)) % count($keys);
    return $hall_layouts[$keys[$index]];
}