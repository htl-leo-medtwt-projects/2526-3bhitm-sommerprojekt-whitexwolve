// Plus/Minus Buttons für Personenanzahl
const input    = document.getElementById('personen');
const btnMinus = document.getElementById('anzahlMinus');
const btnPlus  = document.getElementById('anzahlPlus');

btnMinus.addEventListener('click', () => {
    if (parseInt(input.value) > 1) input.value = parseInt(input.value) - 1;
});

btnPlus.addEventListener('click', () => {
    if (parseInt(input.value) < 20) input.value = parseInt(input.value) + 1;
});