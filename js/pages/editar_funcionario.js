document.addEventListener('DOMContentLoaded', () => {
    const whatsappInput = document.querySelector('[data-whatsapp-input]');

    if (!whatsappInput) {
        return;
    }

    const formatWhatsApp = (value) => {
        let digits = value.replace(/\D/g, '');

        if ((digits.length === 12 || digits.length === 13) && digits.startsWith('55')) {
            digits = digits.slice(2);
        }

        digits = digits.slice(0, 11);

        if (digits.length === 0) {
            return '';
        }

        if (digits.length <= 2) {
            return `(${digits}`;
        }

        const ddd = digits.slice(0, 2);
        const local = digits.slice(2);

        if (digits.length <= 10) {
            if (local.length <= 4) {
                return `(${ddd}) ${local}`;
            }

            return `(${ddd}) ${local.slice(0, 4)}-${local.slice(4)}`;
        }

        if (local.length <= 5) {
            return `(${ddd}) ${local.slice(0, 1)} ${local.slice(1)}`;
        }

        return `(${ddd}) ${local.slice(0, 1)} ${local.slice(1, 5)}-${local.slice(5)}`;
    };

    whatsappInput.addEventListener('input', () => {
        whatsappInput.value = formatWhatsApp(whatsappInput.value);
    });
});
