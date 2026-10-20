document.addEventListener('DOMContentLoaded', () => {
    const search = document.querySelector('[data-systems-search]');
    const cards = Array.from(document.querySelectorAll('[data-system-card]'));
    const noResults = document.querySelector('[data-systems-no-results]');

    if (!search || cards.length === 0) return;

    search.addEventListener('input', () => {
        const term = search.value.trim().toLocaleLowerCase('pt-BR');
        let visible = 0;

        cards.forEach((card) => {
            const matches = !term || (card.dataset.searchText || '').includes(term);
            card.hidden = !matches;
            if (matches) visible++;
        });

        if (noResults) noResults.hidden = visible !== 0;
    });
});
