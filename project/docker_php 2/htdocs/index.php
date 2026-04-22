<?php
require_once __DIR__ . '/data/events.php';

// hilfe von ki: sichere HTML-Ausgabe, damit Sonderzeichen sauber angezeigt werden
function esc($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

// hilfe von ki: für Suche/Filter alles in Kleinbuchstaben umwandeln,
// mb_strtolower ist für Umlaute robuster als strtolower
function lower_text($value): string
{
    $text = (string) $value;
    return function_exists('mb_strtolower') ? mb_strtolower($text) : strtolower($text);
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VibeSeat – Events entdecken</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800&family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header class="seitenkopf">
        <div class="seitenbreite seitenkopf__leiste">
            <a href="index.php" class="markenname">VibeSeat</a>

            <nav class="hauptnavigation" aria-label="Hauptnavigation">
                <a class="hauptnavigation__link" href="#events">Events</a>
                <a class="hauptnavigation__link" href="#prozess">Ablauf</a>
            </nav>
        </div>
    </header>

    <main>
        <section class="startbereich">
            <div class="seitenbreite">
                <div class="startbereich__inhalt">
                    <div class="startbereich__textblock">
                        <p class="startbereich__hinweis">
                            Ein Veranstaltungsort · mehrere Vorstellungen · klare Auswahl
                        </p>

                        <h1 class="startbereich__titel">
                            Tickets finden, Vorstellung aussuchen, Sitzplatz durch Vibes entdecken
                        </h1>

                    </div>

                    <aside class="veranstaltungsort_karte">
                        <span class="veranstaltungsort_karte__label">Veranstaltungsort</span>
                        <h2 class="veranstaltungsort_karte__titel"><?= esc(HALL_NAME) ?></h2>
                        <p>
                            Erlebe deine Lieblingsveranstaltungen in der <?= esc(HALL_NAME) ?>, dem
                            angesagten Veranstaltungsort in der Stadt. Von mitreißenden Konzerten über
                            fesselnde Theaterstücke bis hin zu spannenden Kinofilmen – hier findest du
                            alles an einem Ort. Genieße erstklassige Unterhaltung und reserviere jetzt
                            deine Tickets für unvergessliche Erlebnisse!
                        </p>
                    </aside>
                </div>

                <section class="suchbereich" aria-label="Eventsuche">
                    <div class="suchbereich__kopf">
                        <div>
                            <p class="suchbereich__untertitel">Eventsuche</p>
                            <h2 class="suchbereich__titel">Finde schnell die passende Veranstaltung</h2>
                        </div>

                        <p class="suchbereich__info">
                            Suche nach Eventnamen und filtere nach Kategorie. Der Ort bleibt fix
                            und muss nicht extra gewählt werden.
                        </p>
                    </div>

                    <form class="suchformular" id="eventSucheFormular" onsubmit="return false;">
                        <div class="suchformular__feldgruppe suchformular__feldgruppe--gross">
                            <label class="suchformular__label" for="search">Was suchst du?</label>
                            <input
                                class="suchformular__eingabe"
                                type="text"
                                id="search"
                                name="search"
                                placeholder="z. B. Starlight, Theater oder Cinema"
                                autocomplete="off"
                            >
                        </div>

                        <div class="suchformular__feldgruppe">
                            <label class="suchformular__label" for="category">Kategorie</label>
                            <select class="suchformular__auswahl" id="category" name="category">
                                <option value="">Alle Kategorien</option>
                                <option value="Konzert">Konzert</option>
                                <option value="Theater">Theater</option>
                                <option value="Kino">Kino</option>
                            </select>
                        </div>

                        <div class="suchformular__feldgruppe">
                            <span class="suchformular__label">Ort</span>
                            <div class="suchformular__festwert"><?= esc(HALL_NAME) ?></div>
                        </div>

                        <div class="suchformular__aktionen">
                            <button class="schaltflaeche schaltflaeche--primaer" type="button" id="suchButton">
                                Events finden
                            </button>
                            <button class="schaltflaeche schaltflaeche--sekundaer" type="button" id="resetButton">
                                Zurücksetzen
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </section>

        <section class="eventuebersicht" id="events">
            <div class="seitenbreite">
                <div class="bereichskopf">
                    <div>
                        <p class="bereichskopf__untertitel">Kommende Veranstaltungen</p>
                        <h2 class="bereichskopf__titel">Verfügbare Events</h2>
                    </div>

                    <div class="bereichskopf__ergebniszahl">
                        <span id="ergebnisAnzahl"><?= count($events) ?></span> Ergebnisse
                    </div>
                </div>

                <div class="eventuebersicht__raster" id="eventRaster">
                    <?php foreach ($events as $event): ?>
                        <?php
                            // hilfe von ki: sichere Fallbacks, damit keine Undefined-array-key-Warnings kommen
                            $title = $event['title'] ?? 'Unbenanntes Event';
                            $description = $event['description'] ?? 'Keine Beschreibung vorhanden.';
                            $category = $event['category'] ?? 'Sonstiges';
                            $theme = $event['theme'] ?? 'nachtblau';
                            $price = isset($event['price']) ? (float)$event['price'] : 0;
                            $shows = $event['shows'] ?? [];
                        ?>

                        <article
                            class="veranstaltungskarte"
                            data-title="<?= esc(lower_text($title)) ?>"
                            data-description="<?= esc(lower_text($description)) ?>"
                            data-category="<?= esc(lower_text($category)) ?>"
                        >
                            <div class="veranstaltungskarte__bildbereich veranstaltungskarte__bildbereich--<?= esc($theme) ?>">
                                <span class="veranstaltungskarte__kategorie"><?= esc($category) ?></span>
                                <h3 class="veranstaltungskarte__titel"><?= esc($title) ?></h3>
                            </div>

                            <div class="veranstaltungskarte__inhalt">
                                <p class="veranstaltungskarte__beschreibung"><?= esc($description) ?></p>

                                <div class="veranstaltungskarte__meta">
                                    <span>📍 <?= esc(HALL_NAME) ?></span>
                                    <span>🎟️ ab € <?= number_format($price, 2, ',', '.') ?></span>
                                </div>

                                <div class="vorstellungszeiten">
                                    <?php if (!empty($shows) && is_array($shows)): ?>
                                        <?php foreach ($shows as $show): ?>
                                            <?php
                                                // hilfe von ki: auch bei einzelnen Shows wieder sichere Fallbacks
                                                $showId = $show['id'] ?? '';
                                                $showDisplay = $show['display'] ?? 'Unbekannte Zeit';
                                            ?>
                                            <a
                                                class="vorstellungszeiten__eintrag"
                                                href="show.php?show=<?= urlencode((string)$showId) ?>"
                                            >
                                                <?= esc($showDisplay) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <span class="vorstellungszeiten__leer">Keine Vorstellungen verfügbar</span>
                                    <?php endif; ?>
                                </div>

                                <div class="veranstaltungskarte__aktionen">
                                    <a class="schaltflaeche schaltflaeche--sekundaer" href="#">
                                        Details
                                    </a>

                                    <?php if (!empty($shows) && is_array($shows)): ?>
                                        <?php
                                            // hilfe von ki: erste Vorstellung als Standard-Link verwenden
                                            $firstShowId = $shows[0]['id'] ?? '';
                                        ?>
                                        <a
                                            class="schaltflaeche schaltflaeche--primaer"
                                            href="show.php?show=<?= urlencode((string)$firstShowId) ?>"
                                        >
                                            Vorstellung wählen
                                        </a>
                                    <?php else: ?>
                                        <span class="schaltflaeche schaltflaeche--primaer schaltflaeche--deaktiviert">
                                            Keine Vorstellung
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <div class="leerzustand" id="leerzustand" hidden>
                    <h3 class="leerzustand__titel">Keine passenden Events gefunden</h3>
                    <p class="leerzustand__text">
                        Versuche einen anderen Suchbegriff oder eine andere Kategorie.
                    </p>
                    <button class="schaltflaeche schaltflaeche--primaer" type="button" id="leerzustandResetButton">
                        Alles anzeigen
                    </button>
                </div>
            </div>
        </section>

        <section class="prozesshinweise" id="prozess">
            <div class="seitenbreite prozesshinweise__raster">
                <article class="prozesskarte">
                    <h3 class="prozesskarte__titel">1. Event wählen</h3>
                    <p class="prozesskarte__text">
                        Die Startseite zeigt bewusst nur Events und Vorstellungen.
                    </p>
                </article>

                <article class="prozesskarte">
                    <h3 class="prozesskarte__titel">2. Vibes setzen</h3>
                    <p class="prozesskarte__text">
                        Seat‑Vibes kommen erst auf der nächsten Seite.
                    </p>
                </article>

                <article class="prozesskarte">
                    <h3 class="prozesskarte__titel">3. Plätze entdecken</h3>
                    <p class="prozesskarte__text">
                        Saalplan, Empfehlungen und Reservation folgen später im Ablauf.
                    </p>
                </article>
            </div>
        </section>
    </main>

    <footer class="seitenfuss">
        <div class="seitenbreite seitenfuss__inhalt">
            <p>© <?= date('Y') ?> VibeSeat</p>
            <p>Reservierungsdauer: <?= (int) RESERVATION_TIMEOUT_MINUTES ?> Minuten</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>