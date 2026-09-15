document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('.suggestions-admin');
    const filterInput = document.getElementById('suggestions-filter');
    const totalEl = document.getElementById('suggestions-total');
    const statusEl = document.getElementById('suggestions-status');
    const tableBody = document.getElementById('suggestions-table-body');

    if (!root || !filterInput || !totalEl || !statusEl || !tableBody) {
        return;
    }

    let suggestions = [];

    const setStatus = (message) => {
        statusEl.textContent = message;
    };

    const formatName = (item) => {
        const firstName = (item.first_name || '').trim();
        const lastName = (item.last_name || '').trim();
        const fullName = `${firstName} ${lastName}`.trim();

        return fullName || item.username || '-';
    };

    const formatDate = (value) => {
        if (!value) {
            return '-';
        }

        const date = new Date(value.replace(' ', 'T'));

        if (Number.isNaN(date.getTime())) {
            return value;
        }

        return date.toLocaleString('pt-BR');
    };

    const appendCell = (row, text, className) => {
        const cell = document.createElement('td');
        cell.textContent = text || '-';

        if (className) {
            cell.className = className;
        }

        row.appendChild(cell);
    };

    const render = (items) => {
        tableBody.innerHTML = '';
        totalEl.textContent = suggestions.length;

        if (items.length === 0) {
            setStatus(suggestions.length === 0 ? 'Nenhuma sugestão encontrada.' : 'Nenhum resultado para o filtro informado.');
            return;
        }

        setStatus('');

        items.forEach((item) => {
            const row = document.createElement('tr');

            appendCell(row, formatName(item), 'suggestion-user');
            appendCell(row, item.sugestao, 'suggestion-text');
            appendCell(row, formatDate(item.data_envio), 'suggestion-date');

            tableBody.appendChild(row);
        });
    };

    const filterSuggestions = () => {
        const term = filterInput.value.trim().toLowerCase();

        if (term === '') {
            render(suggestions);
            return;
        }

        render(suggestions.filter((item) => {
            const haystack = [
                formatName(item),
                item.username,
                item.sugestao,
                item.data_envio,
            ].join(' ').toLowerCase();

            return haystack.includes(term);
        }));
    };

    const loadSuggestions = async () => {
        try {
            setStatus('Carregando sugestões...');

            const response = await fetch(root.dataset.apiUrl, {
                headers: {
                    Accept: 'application/json',
                },
            });

            const json = await response.json();

            if (!response.ok || json.error) {
                throw new Error(json.error || 'Não foi possível carregar as sugestões.');
            }

            suggestions = Array.isArray(json) ? json : [];
            render(suggestions);
        } catch (error) {
            suggestions = [];
            totalEl.textContent = '0';
            tableBody.innerHTML = '';
            setStatus(error.message || 'Erro ao carregar sugestões.');
        }
    };

    filterInput.addEventListener('input', filterSuggestions);
    loadSuggestions();
});
