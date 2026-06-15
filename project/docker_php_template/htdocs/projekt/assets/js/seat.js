const seatBox      = document.querySelector('.seat-box');
const pricePerSeat = parseFloat(seatBox?.dataset.price ?? 0);

const seats       = document.querySelectorAll('.seat--free');
const seatAnzahl  = document.getElementById('seatAnzahl');
const seatPreis   = document.getElementById('seatPreis');
const seatListe   = document.getElementById('seatListe');
const reservieren = document.getElementById('reservierenButton');

// Set statt Array damit doppeltes Hinzufügen automatisch ignoriert wird
const ausgewaehlt = new Set();

// EMPFEHLUNG kommt als globale Variable aus seat.php (via <script> vor diesem File)
// wenn Modus = "empfehlung", werden die besten Sitze direkt vorausgewählt
if (typeof EMPFEHLUNG !== 'undefined' && EMPFEHLUNG.length > 0) {
    EMPFEHLUNG.forEach((id) => {
        ausgewaehlt.add(id);
        const btn = document.querySelector(`.seat[data-seat="${id}"]`);
        if (btn) btn.classList.add('seat--selected');
    });
}

// Dezimalkomma statt Punkt für Preisanzeige
function formatPreis(wert) {
    return wert.toFixed(2).replace('.', ',');
}

// Sidebar aktualisieren: Anzahl, Preis, Liste, Button-Status
// (Logik in Kooperation mit KI entwickelt)
function updateAnzeige() {
    seatAnzahl.textContent = ausgewaehlt.size;
    seatPreis.textContent  = formatPreis(ausgewaehlt.size * pricePerSeat);

    seatListe.innerHTML = '';

    if (ausgewaehlt.size === 0) {
        const leer = document.createElement('li');
        leer.className   = 'seat-liste__leer';
        leer.textContent = 'Noch kein Platz gewählt.';
        seatListe.appendChild(leer);
    } else {
        ausgewaehlt.forEach((id) => {
            const btn   = document.querySelector(`.seat[data-seat="${id}"]`);
            const score = btn?.dataset.score ?? '–';
            const item  = document.createElement('li');
            item.innerHTML = `<strong>${id}</strong> <span>Score ${score}</span> <span>€ ${formatPreis(pricePerSeat)}</span>`;
            seatListe.appendChild(item);
        });
    }

    // PERSONEN kommt ebenfalls aus seat.php — gibt an wie viele Sitze gewählt werden sollen
    const ziel = typeof PERSONEN !== 'undefined' ? PERSONEN : 1;
    reservieren.disabled = ausgewaehlt.size === 0;

    if (ausgewaehlt.size === 0) {
        reservieren.textContent = 'Zur Reservierung';
    } else if (ausgewaehlt.size < ziel) {
        reservieren.textContent = `Noch ${ziel - ausgewaehlt.size} Platz/Plätze wählen`;
    } else {
        reservieren.textContent = 'Zur Reservierung →';
    }
}

// Toggle: nochmal klicken hebt die Auswahl wieder auf
seats.forEach((btn) => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.seat;
        if (ausgewaehlt.has(id)) {
            ausgewaehlt.delete(id);
            btn.classList.remove('seat--selected');
        } else {
            ausgewaehlt.add(id);
            btn.classList.add('seat--selected');
        }
        updateAnzeige();
    });
});

// alle aktuellen URL-Parameter übernehmen (show, personen, modus …)
// und die gewählten Sitze als "seats" anhängen
reservieren.addEventListener('click', () => {
    if (ausgewaehlt.size === 0) return;
    const params = new URLSearchParams(window.location.search);
    params.set('seats', Array.from(ausgewaehlt).join(','));
    window.location.href = 'reservation.php?' + params.toString();
});

// einmal beim Laden aufrufen damit Sidebar den richtigen Startzustand hat
updateAnzeige();