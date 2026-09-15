document.addEventListener('DOMContentLoaded', () => {
    const titleInput = document.getElementById('document_title');
    const contentInput = document.getElementById('document_content');
    const titleCount = document.getElementById('title_count');
    const contentCount = document.getElementById('content_count');

    if (!titleInput || !contentInput || !titleCount || !contentCount) {
        return;
    }

    const updateCounts = () => {
        titleCount.textContent = titleInput.value.length;
        contentCount.textContent = contentInput.value.length;
    };

    titleInput.addEventListener('input', updateCounts);
    contentInput.addEventListener('input', updateCounts);
    updateCounts();
});
