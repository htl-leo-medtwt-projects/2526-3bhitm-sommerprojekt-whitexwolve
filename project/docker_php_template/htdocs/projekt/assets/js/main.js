
const showtimeLinks = document.querySelectorAll('.vorstellungszeiten__eintrag');
const searchInput = document.getElementById('search');
const categorySelect = document.getElementById('category');
const searchButton = document.getElementById('suchButton');
const resetButton = document.getElementById('resetButton');
const emptyResetButton = document.getElementById('leerzustandResetButton');
const resultSection = document.getElementById('ergebnisbereich');
const resultCount = document.getElementById('ergebnisAnzahl');
const emptyState = document.getElementById('leerzustand');
const eventCards = document.querySelectorAll('.veranstaltungskarte');

let hasScrolledAfterSearch = false;

showtimeLinks.forEach((link) => {
  link.addEventListener('click', (event) => {
    event.preventDefault();

    const currentCard = link.closest('.veranstaltungskarte');
    const localShowtimes = currentCard.querySelectorAll('.vorstellungszeiten__eintrag');

    localShowtimes.forEach((item) => item.classList.remove('ist-ausgewaehlt'));
    link.classList.add('ist-ausgewaehlt');
  });
});

function normalizeText(value) {
  return value.toLowerCase().trim();
}

function filterEvents(scrollToResults = false) {
  const searchValue = normalizeText(searchInput.value);
  const categoryValue = normalizeText(categorySelect.value);
  let visibleCount = 0;

  eventCards.forEach((card) => {
    const title = card.dataset.title || '';
    const description = card.dataset.description || '';
    const category = card.dataset.category || '';

    const matchesSearch =
      searchValue === '' ||
      title.includes(searchValue) ||
      description.includes(searchValue);

    const matchesCategory =
      categoryValue === '' ||
      category === categoryValue;

    const isVisible = matchesSearch && matchesCategory;
    card.hidden = !isVisible;

    if (isVisible) {
      visibleCount++;
    }
  });

  resultCount.textContent = visibleCount;
  emptyState.hidden = visibleCount !== 0;

  // Hilfe von KI: Wenn scrollToResults true ist, wird sanft zu den Ergebnissen gescrollt.
  if (scrollToResults) {
    resultSection.scrollIntoView({
      behavior: 'smooth',
      block: 'start'
    });
    hasScrolledAfterSearch = true;
  }
}

function resetFilters() {
  searchInput.value = '';
  categorySelect.value = '';
  filterEvents(hasScrolledAfterSearch);
}

searchInput.addEventListener('input', () => {
  filterEvents(false);
});

categorySelect.addEventListener('change', () => {
  filterEvents(true);
});

searchButton.addEventListener('click', () => {
  filterEvents(true);
});

resetButton.addEventListener('click', resetFilters);

if (emptyResetButton) {
  emptyResetButton.addEventListener('click', resetFilters);
}