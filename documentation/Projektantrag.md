# Projektname
VibeSeat

## USP
VibeSeat ist ein Ticketshop mit Sitzplatzwahl, der nicht nur „freie Plätze“ zeigt, sondern Sitzplätze nach **Seat‑Vibes** (z.B. ruhig, mittendrin, beste Sicht, schneller Ausgang, Gruppe) empfiehlt und kurz begründet, warum genau diese Plätze passen.

---

## UI & UX | Projekt aus Sicht des Users

### Zielgruppe
- Personen, die Tickets für Kino/Theater/Konzert kaufen und schnell den geeigneten Platz finden wollen
- Gruppen, die zusammen sitzen möchten

### Haupt-Flow (User Journey)
1. User wählt ein Event und eine Vorstellung (Datum/Uhrzeit).
2. User wählt seine Seat‑Vibes (Präferenzen).
3. System zeigt Saalplan + markiert empfohlene Plätze (Top‑Vorschläge).
4. User klickt Plätze an → Plätze werden **reserviert** (Timer läuft).
5. User prüft Warenkorb/Reservation → Checkout → Ticket gekauft.

### Seiten / Screens und Inhalte
#### (1) Event-/Vorstellungsübersicht
- Liste von Events
- Pro Event: verfügbare Vorstellungen (Datum/Uhrzeit)
- Aktion: Vorstellung öffnen

#### (2) Seat‑Vibe Auswahl
- Auswahlmöglichkeiten (Beispiele):
  - Stimmung: ruhig oder eher mittendrin
  - Sicht: „beste Sicht priorisieren“
  - Abstand: nah an Leinwand/Bühne oder weiter weg
  - Ausgang: nah an Ausgang priorisieren
  - Gruppe: Anzahl Personen (z.B. 2–6), möglichst zusammen
- Button: Plätze vorschlagen

#### (3) Saalplan + Empfehlungen
- Visualisierung: Reihen/Sitze als Grid
- Markierung:
  - Empfohlen (z.B. grün), okay (gelb), nicht empfohlen (grau/rot), ausverkauft (durchgestrichen, transparente farbe)
- Klick auf Sitz:
  - „Warum empfohlen?“ → 2–3 kurze Gründe (z.B. zentral, nicht am Gang, nahe Ausgang)
  - Preisinfo
- Aktion: „Zur Reservation hinzufügen“

#### (4) Warenkorb / Reservation (mit Timeout)
- Zeigt:
  - ausgewählte Sitze
  - Einzelpreise + Gesamtpreis
  - Countdown „Reservation gültig bis: …“
- Aktionen:
  - Sitz entfernen
  - Reservation abbrechen
  - Checkout starten

#### (5) Checkout (vereinfachter Kaufabschluss)
- Eingaben:
  - Name + E‑Mail (oder Login, optional)
  - Zahlungsart (nur simuliert)
- Ergebnis:
  - Bestätigung + Ticket-ID / Bestellnummer
  - Übersicht der gekauften Plätze

### UX-Details
- Echtzeit-Feedback: Wenn Vibes geändert werden, aktualisieren sich Empfehlungen.
- Fehlerszenario: „Dieser Platz wurde gerade reserviert/verkauft“ → UI schlägt Alternativen vor.
- Gruppensuche: Wenn nicht genug zusammenhängende Plätze frei sind, zeigt das System „so nah wie möglich“ an.

---

## Coder Plan | Projekt aus Sicht des Entwicklers

### Technologien
- Frontend: HTML, CSS, JavaScrip
- Backend: PHP
- Datenbank:  MySQL vermutlich

### Backend-Schwerpunkte
- Sitzplatz-Verfügbarkeit und Doppelbuchung verhindern
- Reservation mit Ablaufzeit (Timeout)
- Regel-/Score-System für Seat‑Vibe Empfehlungen + Begründungstexte
- Gruppensitz-Algorithmus (zusammenhängende Plätze finden)

### Welche Daten werden in der DB gespeichert?

#### Benutzer/Identität 
- Username oder Anzeigename
- E‑Mail (eindeutig)
- Passwort **gehasht und gesalzen** 
- Erstellungsdatum
- Optional: Rolle (Admin/User)

Wenn kein Login gemacht werden möchte:
- Stattdessen „Gast-Session-ID“ für den Warenkorb/Reservation speichern (Cookie → Session-Key)

#### Events und Vorstellungen
- Eventtitel, Beschreibung, ggf. Dauer/Genre
- Vorstellungstermin (Datum/Uhrzeit)
- Saal-ID oder Saalbezeichnung (weil Sitzplan zur Vorstellung gehört)

#### Sitzplan-Daten
- Für jede Vorstellung: alle Sitze mit
  - Reihe (z.B. A–N)
  - Sitznummer (z.B. 1–20)
  - Position/Koordinate (für „zentral“, „nahe Ausgang“ etc.)
  - Bereich/Zone (optional, z.B. Parkett/Balkon) + Preisregel

#### Sitzstatus (Verfügbarkeit)
- Pro Sitz und Vorstellung:
  - Status: verfügbar / reserviert / verkauft

#### Reservation (Warenkorb-Äquivalent)
- Wem gehört die Reservation:
  - User-ID (wenn Login) oder Session-Key (wenn Gast)
- Zu welcher Vorstellung
- Ablaufzeitpunkt (expires_at)
- Status: aktiv / abgelaufen / abgeschlossen
- Liste der reservierten Sitzplätze (mehrere Sitze pro Reservation)
- Preis-Snapshot (damit der Preis stabil bleibt, auch wenn Preisregeln später geändert werden)

#### Bestellung / Kaufabschluss
- Bestellnummer / Ticket-ID
- Käuferdaten (Name, E‑Mail)
- Kaufzeitpunkt
- Gesamtbetrag
- Gekaufte Sitzplätze (mehrere Sitze pro Bestellung)
- Status: bezahlt / storniert (optional)

#### Seat‑Vibes / Präferenzen
- Pro User oder pro Reservation speicherbar:
  - ausgewählte Vibes (ruhig/mittendrin, beste Sicht, schneller Ausgang, etc.)
  - Gruppengröße
- Optional für Debug/Erklärungen:
  - gespeicherter Sitz-Score und die „Begründungsbausteine“ (z.B. „zentral“, „nicht am Gang“)

---

## Welche DB-Daten werden abgerufen? 

### (1) Eventliste
- Titel + kommende Vorstellungen (Datum/Uhrzeit) + Bild oder Video dazu z.B. Cover der Band oder der Trailer des Films

### (2) Seatmap einer Vorstellung
- Alle Sitze mit Reihe/Nummer/Koordinate/Preis + aktuellem Status (verfügbar/reserviert/verkauft)

### (3) Empfehlungen (Seat‑Vibe)
- Für eine Vorstellung: freie Sitze holen + Score berechnen + Top‑N Sitzvorschläge zurückgeben
- Zusätzlich: pro Vorschlag 2–3 Gründe (Strings)

### (4) Reservation anzeigen
- Für User/Session: aktuell reservierte Sitze + Ablaufzeit + Preis

### (5) Checkout
- Prüfen: Reservation ist noch gültig, alle Sitze sind noch reserviert für diesen User
- Dann: Sitze auf "verkauft“ setzen, Bestellung speichern, Reservation abschließen

---

## Datenbank

### Tabelle: Shows (Grunddaten zur Vorstellung)
- id
- titel (z.B. „Konzert XY“)
- beschreibung
- startzeit (Datum + Uhrzeit)
- ort / saal_name
- bild_pfad (Poster)
- basis_preis

### Tabelle: Seats (Grunddaten zu Sitzplätzen pro Show)
- id
- show_id (zu welcher Vorstellung gehört der Sitz)
- reihe (z.B. "G")
- nummer (z.B. 12)
- x_pos (für Saalplan-Layout)
- y_pos (für Saalplan-Layout)
- zone (optional, z.B. „Parkett“, „Balkon“)
- preis_aufschlag (optional)

### Tabelle: Seat_Meta (Seat‑Vibe-Werte)
- seat_id
- ist_gangplatz (0/1)
- ist_randplatz (0/1)
- abstand_zentrum (Zahl)
- abstand_ausgang (Zahl)
- sicht_score (Zahl, z.B. 1–100)
- lautstaerke_score (Zahl, z.B. 1–100)

### Tabelle: Users (Login optional)
- id
- username
- email
- passwort_hash (Hash-String von `password_hash`, enthält Salt/Parameter)
- created_at

### Tabelle: Reservations (Warenkorb / Sitz-Hold)
- id
- user_id (nullable) ODER session_key (wenn ohne Login)
- show_id
- status (z.B. aktiv / abgelaufen / abgeschlossen)
- created_at
- expires_at (Timeout, z.B. +10 Minuten)

### Tabelle: Reserved_Seats (welche Sitze sind in einer Reservation)
- reservation_id
- seat_id
- preis_snapshot (Preis zum Zeitpunkt der Reservation)

### Tabelle: Orders (Kaufabschluss)
- id
- user_id (nullable) ODER email/name (für Gastkauf)
- show_id
- bestellnummer (unique)
- status (z.B. bezahlt / storniert)
- total_preis
- gekauft_am (Timestamp)

### Tabelle: Order_Seats (welche Sitze wurden gekauft)
- order_id
- seat_id
- einzelpreis

### Tabelle: Vibe_Settings (vom User gewählte Präferenzen)
- id
- reservation_id (oder user_id, je nachdem wie du es speicherst)
- vibe_ruhig (0/1)
- vibe_mittendrin (0/1)
- vibe_beste_sicht (0/1)
- vibe_schneller_ausgang (0/1)
- gruppengroesse (Zahl)

---

## Seat‑Vibe Regeln (Beispiel)

### Beispiel-Vibes
- „Ruhig“: meidet Gangplätze, bevorzugt Randbereiche mit weniger Durchgang
- „Mittendrin“: bevorzugt zentrale Spalten/Reihen
- „Beste Sicht“: bevorzugt mittlere Reihen (Sweet Spot)
- „Schnell raus“: bevorzugt Plätze nahe Ausgängen
- „Gruppe“: sucht zusammenhängende Sitzblöcke

### Begründungs-Ausgabe (UX)
Beim Klick auf einen empfohlenen Sitz zeigt das UI z.B.:
- „Zentral im Saal“
- „Nicht am Gang“
- „Nahe Ausgang“