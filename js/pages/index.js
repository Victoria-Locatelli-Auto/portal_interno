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

    document.querySelectorAll('[data-banner-carousel]').forEach((carousel) => {
        const slides = [...carousel.querySelectorAll('[data-banner-slide]')];
        const dots = [...carousel.querySelectorAll('[data-banner-dot]')];
        if (slides.length < 2) return;

        let current = 0;
        let timer = null;
        const show = (index) => {
            current = (index + slides.length) % slides.length;
            slides.forEach((slide, position) => {
                const active = position === current;
                slide.classList.toggle('is-active', active);
                slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            });
            dots.forEach((dot, position) => {
                const active = position === current;
                dot.classList.toggle('is-active', active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        };
        const stop = () => { if (timer) window.clearInterval(timer); timer = null; };
        const start = () => { stop(); if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) timer = window.setInterval(() => show(current + 1), 7000); };

        carousel.querySelector('[data-banner-previous]')?.addEventListener('click', () => { show(current - 1); start(); });
        carousel.querySelector('[data-banner-next]')?.addEventListener('click', () => { show(current + 1); start(); });
        dots.forEach((dot) => dot.addEventListener('click', () => { show(Number(dot.dataset.bannerDot || 0)); start(); }));
        carousel.addEventListener('mouseenter', stop);
        carousel.addEventListener('mouseleave', start);
        carousel.addEventListener('focusin', stop);
        carousel.addEventListener('focusout', start);
        start();
    });
});
