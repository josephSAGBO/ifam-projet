const searchableSelects = new WeakMap();

function normalizeSearchText(value) {
    return value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLocaleLowerCase();
}

function filterSearchableSelect(select, searchInput, resultsMessage) {
    const searchTerm = normalizeSearchText(searchInput.value.trim());
    let matchingOptions = 0;

    Array.from(select.options).forEach((option) => {
        const matches = option.value !== ''
            && normalizeSearchText(option.textContent).includes(searchTerm);

        if (matches) {
            matchingOptions += 1;
        }

        option.hidden = Boolean(searchTerm) && !matches && !option.selected;
    });

    resultsMessage.hidden = !searchTerm || matchingOptions > 0;
}

function initializeSearchableSelect(select) {
    if (searchableSelects.has(select)) {
        return;
    }

    const label = select.dataset.searchableSelect || 'une option';
    const searchContainer = document.createElement('div');
    searchContainer.className = 'admin-select-search';

    const searchIcon = document.createElement('span');
    searchIcon.className = 'material-symbols-outlined';
    searchIcon.setAttribute('aria-hidden', 'true');
    searchIcon.textContent = 'search';

    const searchInput = document.createElement('input');
    searchInput.type = 'search';
    searchInput.className = 'admin-select-search-input';
    searchInput.placeholder = label;
    searchInput.setAttribute('aria-label', label);

    const resultsMessage = document.createElement('p');
    resultsMessage.className = 'admin-select-search-empty';
    resultsMessage.setAttribute('role', 'status');
    resultsMessage.textContent = 'Aucun résultat trouvé.';
    resultsMessage.hidden = true;

    searchContainer.append(searchIcon, searchInput);
    select.parentElement.insertBefore(searchContainer, select);
    select.parentElement.insertBefore(resultsMessage, select);

    searchableSelects.set(select, { searchInput, resultsMessage });
    searchInput.addEventListener('input', () => {
        filterSearchableSelect(select, searchInput, resultsMessage);
    });
    select.addEventListener('change', () => {
        searchInput.value = '';
        filterSearchableSelect(select, searchInput, resultsMessage);
    });

    filterSearchableSelect(select, searchInput, resultsMessage);
}

window.refreshAdminSelectSearch = (select) => {
    const searchState = searchableSelects.get(select);

    if (searchState) {
        filterSearchableSelect(select, searchState.searchInput, searchState.resultsMessage);
    }
};

function initializeSearchableSelects(root = document) {
    if (root instanceof HTMLSelectElement && root.hasAttribute('data-searchable-select')) {
        initializeSearchableSelect(root);

        return;
    }

    root.querySelectorAll('select[data-searchable-select]').forEach(initializeSearchableSelect);
}

function observeSearchableSelects() {
    initializeSearchableSelects();

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.target instanceof HTMLSelectElement) {
                window.refreshAdminSelectSearch(mutation.target);
            }

            mutation.addedNodes.forEach((node) => {
                if (node instanceof Element) {
                    initializeSearchableSelects(node);
                }
            });
        });
    });

    observer.observe(document.body, { childList: true, subtree: true });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', observeSearchableSelects, { once: true });
} else {
    observeSearchableSelects();
}
