// Preis pro Sitz aus data-price Attribut
const seatBox      = document.querySelector('.seat-box');
const pricePerSeat = parseFloat(seatBox?.dataset.price ?? 0);

// Alle freien Sitze
const seats         = document.querySelectorAll('.seat--free');
const seatAnzahl    = document.getElementById('seatAnzahl');
const seatPreis     = document.getElementById('seatPreis');
const seatListe     = document.getElementById('seatListe');
const reservieren   = document.getElementById('reservierenButton');

// Set mit aktuell ausgewählten Sitz-IDs
const ausgewaehlt = new Set();

// Wenn Modus = Empfehlung, empfohlene Sitze automatisch vorauswählen
if (typeof EMPFEHLUNG !== 'undefined' && EMPFEHLUNG.length > 0) {
    EMPFEHLUNG.forEach((id) => {
        ausgewaehlt.add(id);
        const btn = document.querySelector(`.seat[data-seat="${id}"]`);
        if (btn) btn.classList.add('seat--selected');
    });
}

function formatPreis(wert) {
    return wert.toFixed(2).replace('.', ',');
}

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
            // Score aus data-score Attribut holen
            const btn   = document.querySelector(`.seat[data-seat="${id}"]`);
            const score = btn?.dataset.score ?? '–';
            const item  = document.createElement('li');
            item.innerHTML = `<span class="seat-liste__id">${id}</span>
                              <span class="seat-liste__score">Score ${score}</span>
                              <span class="seat-liste__preis">€ ${formatPreis(pricePerSeat)}</span>`;
            seatListe.appendChild(item);
        });
    }

    // Button freischalten wenn genau die gewünschte Personenzahl ausgewählt ist
    // (oder bei modus=selbst: mindestens 1)
    const ziel = typeof PERSONEN !== 'undefined' ? PERSONEN : 1;
    reservieren.disabled = ausgewaehlt.size === 0;

    // Visuelles Feedback: Button-Text anpassen
    if (ausgewaehlt.size === 0) {
        reservieren.textContent = 'Zur Reservierung';
    } else if (ausgewaehlt.size < ziel) {
        reservieren.textContent = `Noch ${ziel - ausgewaehlt.size} Platz/Plätze wählen`;
    } else {
        reservieren.textContent = 'Zur Reservierung →';
    }
}

// Klick auf freien Sitz
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

// Zur Reservierung weiterleiten
reservieren.addEventListener('click', () => {
    if (ausgewaehlt.size === 0) return;
    const params = new URLSearchParams(window.location.search);
    params.set('seats', Array.from(ausgewaehlt).join(','));
    window.location.href = 'reservation.php?' + params.toString();
});

// Initiale Anzeige
updateAnzeige();