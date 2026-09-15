document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('.home-search input[name="q"]');

    document.addEventListener('keydown', function (event) {
        if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();

            if (searchInput) {
                searchInput.focus();
            }
        }
    });
});
