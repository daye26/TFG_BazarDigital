function escapeHtml(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function createSearchResultsUrl(baseUrl, query) {
    const url = new window.URL(baseUrl, window.location.origin);
    url.searchParams.set('q', query);

    return url.toString();
}

function closeSearchPanel(panel) {
    if (!panel) {
        return;
    }

    panel.classList.add('hidden');
    panel.innerHTML = '';
}

function openSearchPanel(panel, content) {
    if (!panel) {
        return;
    }

    panel.innerHTML = content;
    panel.classList.remove('hidden');
}

function renderSearchSuggestions(panel, payload, query, resultsUrl) {
    const categories = Array.isArray(payload?.categories) ? payload.categories : [];
    const products = Array.isArray(payload?.products) ? payload.products : [];
    const categoryEntries = categories.map((category) => ({
        url: category.url,
        name: category.name,
    }));
    const productEntries = products.map((product) => ({
        url: product.url,
        name: product.name,
    }));
    const entries = [...categoryEntries, ...productEntries];

    if (!entries.length) {
        openSearchPanel(
            panel,
            `<div class="store-search-empty">No hemos encontrado resultados para "${escapeHtml(query)}".</div>`,
        );

        return;
    }

    openSearchPanel(
        panel,
        `
            <div class="store-search-panel-body">
                ${categoryEntries.length ? `
                    <section class="store-search-section">
                        <p class="store-search-section-label">Filtros</p>
                        <ul class="store-search-list">
                            ${categoryEntries.map((entry) => `
                                <li class="store-search-item">
                                    <a href="${escapeHtml(entry.url)}" class="store-search-option">
                                        ${escapeHtml(entry.name)}
                                    </a>
                                </li>
                            `).join('')}
                        </ul>
                    </section>
                ` : ''}
                ${productEntries.length ? `
                    <section class="store-search-section">
                        <ul class="store-search-list">
                            ${productEntries.map((entry) => `
                                <li class="store-search-item">
                                    <a href="${escapeHtml(entry.url)}" class="store-search-option">
                                        ${escapeHtml(entry.name)}
                                    </a>
                                </li>
                            `).join('')}
                        </ul>
                    </section>
                ` : ''}
            </div>
            <div class="store-search-footer">
                <a href="${escapeHtml(createSearchResultsUrl(resultsUrl, query))}" class="store-search-footer-link">
                    Ver todos los resultados
                </a>
            </div>
        `,
    );
}

export function initGlobalSearch() {
    document.querySelectorAll('[data-global-search]').forEach((searchRoot) => {
        if (searchRoot.dataset.initialized === 'true') {
            return;
        }

        const trigger = searchRoot.querySelector('[data-search-trigger]');
        const dropdown = searchRoot.querySelector('[data-search-dropdown]');
        const input = searchRoot.querySelector('[data-search-input]');
        const panel = searchRoot.querySelector('[data-search-panel]');
        const suggestionsUrl = searchRoot.dataset.suggestionsUrl;
        const resultsUrl = searchRoot.dataset.resultsUrl;

        if (!trigger || !dropdown || !input || !panel || !suggestionsUrl || !resultsUrl) {
            return;
        }

        searchRoot.dataset.initialized = 'true';

        let debounceTimer = null;
        let activeController = null;

        const closeDropdown = ({ returnFocus = false } = {}) => {
            if (debounceTimer) {
                window.clearTimeout(debounceTimer);
                debounceTimer = null;
            }

            if (activeController) {
                activeController.abort();
                activeController = null;
            }

            closeSearchPanel(panel);
            dropdown.classList.add('hidden');
            trigger.setAttribute('aria-expanded', 'false');

            if (returnFocus) {
                trigger.focus();
            }
        };

        const openDropdown = () => {
            dropdown.classList.remove('hidden');
            trigger.setAttribute('aria-expanded', 'true');
            input.focus();

            if (input.value.trim()) {
                requestSuggestions();
            }
        };

        const requestSuggestions = async () => {
            const query = input.value.trim();

            if (!query) {
                closeSearchPanel(panel);
                return;
            }

            const url = new window.URL(suggestionsUrl, window.location.origin);
            url.searchParams.set('q', query);

            openSearchPanel(panel, '<div class="store-search-empty">Buscando sugerencias...</div>');

            if (activeController) {
                activeController.abort();
            }

            activeController = new window.AbortController();

            try {
                const response = await window.fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                    signal: activeController.signal,
                });

                const payload = await response.json().catch(() => ({}));

                if (!response.ok) {
                    throw new Error(payload?.message || 'No se pudieron cargar las sugerencias.');
                }

                renderSearchSuggestions(panel, payload, query, resultsUrl);
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                openSearchPanel(panel, '<div class="store-search-empty">No se pudieron cargar las sugerencias.</div>');
            }
        };

        input.addEventListener('input', () => {
            if (debounceTimer) {
                window.clearTimeout(debounceTimer);
            }

            const query = input.value.trim();

            if (!query) {
                if (activeController) {
                    activeController.abort();
                }

                closeSearchPanel(panel);
                return;
            }

            debounceTimer = window.setTimeout(requestSuggestions, 260);
        });

        input.addEventListener('focus', () => {
            if (dropdown.classList.contains('hidden')) {
                openDropdown();
            }

            if (input.value.trim() && panel.innerHTML.trim()) {
                panel.classList.remove('hidden');
            }
        });

        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                event.preventDefault();
                closeDropdown({ returnFocus: true });
            }
        });

        trigger.addEventListener('click', () => {
            if (dropdown.classList.contains('hidden')) {
                openDropdown();
                return;
            }

            closeDropdown();
        });

        document.addEventListener('click', (event) => {
            if (!searchRoot.contains(event.target)) {
                closeDropdown();
            }
        });
    });
}
