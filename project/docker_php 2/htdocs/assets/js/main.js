const showtimeLinks = document.querySelectorAll('.vorstellungszeiten__eintrag');

showtimeLinks.forEach((link) => {
  link.addEventListener('click', (event) => {
    event.preventDefault();

    const currentCard = link.closest('.veranstaltungskarte');
    const localShowtimes = currentCard.querySelectorAll('.vorstellungszeiten__eintrag');

    localShowtimes.forEach((item) => item.classList.remove('ist-ausgewaehlt'));
    link.classList.add('ist-ausgewaehlt');
  });
});
