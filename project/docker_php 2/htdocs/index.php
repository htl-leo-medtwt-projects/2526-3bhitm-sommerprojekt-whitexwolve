<?php
require_once __DIR__ . '/data/events.php';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>VibeSeat – Events entdecken</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <?php include __DIR__ . '/partials/site-header.php'; ?>

  <main>
    <section class="startbereich">
      <div class="seitenbreite">
        <div class="startbereich__inhalt">
          <div class="startbereich__textblock">
            <p class="startbereich__hinweis">Ein Veranstaltungsort · mehrere Vorstellungen · klare Auswahl</p>
            <h1 class="startbereich__titel">Tickets finden, Vorstellung wählen, Sitzplatz später perfekt auswählen</h1>
            <p class="startbereich__beschreibung">
              VibeSeat zeigt dir zuerst alle kommenden Events in einem festen Veranstaltungsort.
              Seat‑Vibes, Empfehlungen und der Saalplan kommen erst im nächsten Schritt.
            </p>
          </div>

          <aside class="veranstaltungsort_karte">
            <span class="veranstaltungsort_karte__label">Veranstaltungsort</span>
            <h2 class="veranstaltungsort_karte__titel">VibeSeat Hall</h2>
            <p class="veranstaltungsort_karte__text">
              Alle Events finden in derselben Location statt. Dadurch bleibt die Startseite übersichtlich und näher an einem echten Ticketshop.
            </p>
          </aside>
        </div>

        <section class="suchbereich">
          <div class="suchbereich__kopf">
            <div>
              <p class="suchbereich__untertitel">Eventsuche</p>
              <h2 class="suchbereich__titel">Finde schnell die passende Veranstaltung</h2>
            </div>
            <p class="suchbereich__info">
              Suche nach Eventnamen und filtere nach Kategorie. Der Ort bleibt fix und muss nicht extra gewählt werden.
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
              <label class="suchformular__label">Ort</label>
              <div class="suchformular__festwert">VibeSeat Hall</div>
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

    <section class="eventuebersicht" id="ergebnisbereich">
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
            <article
              class="veranstaltungskarte"
              data-title="<?= htmlspecialchars(mb_strtolower($event['title'])) ?>"
              data-description="<?= htmlspecialchars(mb_strtolower($event['description'])) ?>"
              data-category="<?= htmlspecialchars(mb_strtolower($event['category'])) ?>"
            >
              <div class="veranstaltungskarte__bildbereich veranstaltungskarte__bildbereich--<?= htmlspecialchars($event['theme']) ?>">
                <span class="veranstaltungskarte__kategorie"><?= htmlspecialchars($event['category']) ?></span>
                <h3 class="veranstaltungskarte__titel"><?= htmlspecialchars($event['title']) ?></h3>
              </div>

              <div class="veranstaltungskarte__inhalt">
                <p class="veranstaltungskarte__beschreibung"><?= htmlspecialchars($event['description']) ?></p>

                <div class="veranstaltungskarte__meta">
                  <span>📍 VibeSeat Hall</span>
                  <span>🎟️ ab € <?= number_format($event['price'], 2, ',', '.') ?></span>
                </div>

                <div class="vorstellungszeiten">
                  <?php foreach ($event['showtimes'] as $showtime): ?>
                    <a class="vorstellungszeiten__eintrag" href="#">
                      <?= htmlspecialchars($showtime) ?>
                    </a>
                  <?php endforeach; ?>
                </div>

                <div class="veranstaltungskarte__aktionen">
                  <a class="schaltflaeche schaltflaeche--sekundaer" href="#">Details</a>
                  <a class="schaltflaeche schaltflaeche--primaer" href="#">Vorstellung wählen</a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div class="leerzustand" id="leerzustand" hidden>
          <h3 class="leerzustand__titel">Keine passenden Events gefunden</h3>
          <p class="leerzustand__text">Versuche einen anderen Suchbegriff oder eine andere Kategorie.</p>
          <button class="schaltflaeche schaltflaeche--primaer" type="button" id="leerzustandResetButton">
            Alles anzeigen
          </button>
        </div>
      </div>
    </section>

    <section class="prozesshinweise">
      <div class="seitenbreite prozesshinweise__raster">
        <article class="prozesskarte">
          <h3 class="prozesskarte__titel">1. Event wählen</h3>
          <p class="prozesskarte__text">Die Startseite zeigt bewusst nur Events und Vorstellungen.</p>
        </article>

        <article class="prozesskarte">
          <h3 class="prozesskarte__titel">2. Vibes setzen</h3>
          <p class="prozesskarte__text">Seat‑Vibes kommen erst auf der nächsten Seite.</p>
        </article>

        <article class="prozesskarte">
          <h3 class="prozesskarte__titel">3. Plätze entdecken</h3>
          <p class="prozesskarte__text">Saalplan, Empfehlungen und Reservation folgen später im Ablauf.</p>
        </article>
      </div>
    </section>
  </main>

  <?php include __DIR__ . '/partials/site-footer.php'; ?>
  <script src="assets/js/main.js"></script>
</body>
</html>