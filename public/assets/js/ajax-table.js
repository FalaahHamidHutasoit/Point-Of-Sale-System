(() => {
    'use strict';

    const CONFIG = {
        rootSelector: '.main-content',
        formSelector: 'form[method="get"], form[method="GET"]',
        debounceMs: 350,
        loadingClass: 'ajax-content-loading',
    };

    let requestController = null;
    let debounceTimer = null;
    let isPopState = false;

    const css = `
        .ajax-content-loading { position: relative; min-height: 180px; }
        .ajax-content-loading::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 1040;
            background: rgba(246, 248, 252, .58);
            backdrop-filter: blur(1px);
            border-radius: 14px;
        }
        .ajax-content-loading::after {
            content: '';
            position: absolute;
            z-index: 1041;
            width: 34px;
            height: 34px;
            left: 50%;
            top: 110px;
            margin-left: -17px;
            border: 3px solid rgba(37, 99, 235, .18);
            border-top-color: #2563eb;
            border-radius: 50%;
            animation: ajax-spin .65s linear infinite;
        }
        @keyframes ajax-spin { to { transform: rotate(360deg); } }
        .ajax-live-indicator {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 11px;
            font-weight: 600;
            color: #667085;
            white-space: nowrap;
        }
        .ajax-live-indicator::before {
            content: '';
            width: 7px;
            height: 7px;
            background: #12b76a;
            border-radius: 50%;
            box-shadow: 0 0 0 3px rgba(18,183,106,.10);
        }
    `;

    function injectStyles() {
        if (document.getElementById('ajax-table-style')) return;
        const style = document.createElement('style');
        style.id = 'ajax-table-style';
        style.textContent = css;
        document.head.appendChild(style);
    }

    function getRoot(doc = document) {
        return doc.querySelector(CONFIG.rootSelector);
    }

    function hasTable(root) {
        return !!root?.querySelector('table');
    }

    function eligibleForms(root = getRoot()) {
        if (!root || !hasTable(root)) return [];
        return [...root.querySelectorAll(CONFIG.formSelector)].filter((form) => {
            const action = form.getAttribute('action');
            if (action && /^https?:\/\//i.test(action)) {
                try {
                    return new URL(action).origin === window.location.origin;
                } catch (_) {
                    return false;
                }
            }
            return true;
        });
    }

    function buildUrl(form) {
        const action = form.getAttribute('action') || window.location.pathname;
        const url = new URL(action, window.location.href);
        const params = new URLSearchParams(new FormData(form));

        // Jangan memenuhi URL dengan parameter kosong.
        [...params.keys()].forEach((key) => {
            if ((params.get(key) ?? '').toString().trim() === '') {
                params.delete(key);
            }
        });

        url.search = params.toString();
        return url;
    }

    function captureFocus(root) {
        const el = document.activeElement;
        if (!el || !root?.contains(el) || !el.name) return null;
        return {
            name: el.name,
            start: typeof el.selectionStart === 'number' ? el.selectionStart : null,
            end: typeof el.selectionEnd === 'number' ? el.selectionEnd : null,
        };
    }

    function restoreFocus(root, state) {
        if (!state) return;
        const escapedName = window.CSS?.escape ? CSS.escape(state.name) : state.name.replace(/"/g, '\\"');
        const el = root.querySelector(`[name="${escapedName}"]`);
        if (!el) return;
        el.focus({ preventScroll: true });
        if (state.start !== null && typeof el.setSelectionRange === 'function') {
            try { el.setSelectionRange(state.start, state.end); } catch (_) {}
        }
    }

    function addLiveIndicator(form) {
        if (form.dataset.ajaxEnhanced === '1') return;
        form.dataset.ajaxEnhanced = '1';

        const submitButton = form.querySelector('button[type="submit"], input[type="submit"]');
        if (!submitButton || submitButton.parentElement?.querySelector('.ajax-live-indicator')) return;

        const indicator = document.createElement('span');
        indicator.className = 'ajax-live-indicator ms-2';
        indicator.textContent = 'Live';
        submitButton.insertAdjacentElement('afterend', indicator);
    }

    async function loadUrl(url, { pushState = true, preserveFocus = true } = {}) {
        const root = getRoot();
        if (!root) return;

        if (requestController) requestController.abort();
        requestController = new AbortController();

        const focusState = preserveFocus ? captureFocus(root) : null;
        root.classList.add(CONFIG.loadingClass);
        root.setAttribute('aria-busy', 'true');

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                signal: requestController.signal,
                credentials: 'same-origin',
            });

            if (!response.ok) throw new Error(`HTTP ${response.status}`);

            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextRoot = getRoot(parsed);

            if (!nextRoot) {
                window.location.href = url.toString();
                return;
            }

            root.innerHTML = nextRoot.innerHTML;

            if (pushState && !isPopState) {
                history.pushState({ ajaxTable: true }, '', url.toString());
            }

            enhance();
            restoreFocus(root, focusState);

            // Bootstrap tooltip/popover atau komponen page-specific bisa listen event ini.
            document.dispatchEvent(new CustomEvent('nexapos:ajax-updated', {
                detail: { url: url.toString() },
            }));
        } catch (error) {
            if (error.name === 'AbortError') return;
            console.error('[KAMELA AJAX]', error);
            // Progressive enhancement: jika AJAX gagal, fallback ke navigasi normal.
            window.location.href = url.toString();
        } finally {
            const currentRoot = getRoot();
            currentRoot?.classList.remove(CONFIG.loadingClass);
            currentRoot?.removeAttribute('aria-busy');
        }
    }

    function submitAjax(form, options = {}) {
        const url = buildUrl(form);
        loadUrl(url, options);
    }

    function bindForm(form) {
        if (form.dataset.ajaxBound === '1') return;
        form.dataset.ajaxBound = '1';
        addLiveIndicator(form);

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            clearTimeout(debounceTimer);
            submitAjax(form);
        });

        form.querySelectorAll('input[type="search"], input[type="text"]').forEach((input) => {
            input.addEventListener('input', () => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => submitAjax(form), CONFIG.debounceMs);
            });
        });

        form.querySelectorAll('select, input[type="date"], input[type="month"], input[type="number"]').forEach((input) => {
            input.addEventListener('change', () => {
                clearTimeout(debounceTimer);
                submitAjax(form);
            });
        });
    }

    function bindPagination(root) {
        root.querySelectorAll('.pagination a[href], a.page-link[href]').forEach((link) => {
            if (link.dataset.ajaxBound === '1') return;
            link.dataset.ajaxBound = '1';
            link.addEventListener('click', (event) => {
                if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) return;
                const url = new URL(link.href, window.location.href);
                if (url.origin !== window.location.origin) return;
                event.preventDefault();
                loadUrl(url, { preserveFocus: false });
            });
        });
    }

    function bindResetLinks(root) {
        root.querySelectorAll('a[href]').forEach((link) => {
            if (link.dataset.ajaxResetBound === '1') return;
            const text = (link.textContent || '').toLowerCase();
            const looksLikeReset = text.includes('reset') || text.includes('bersihkan') || text.includes('semua data');
            if (!looksLikeReset) return;

            const url = new URL(link.href, window.location.href);
            if (url.origin !== window.location.origin || url.pathname !== window.location.pathname) return;

            link.dataset.ajaxResetBound = '1';
            link.addEventListener('click', (event) => {
                event.preventDefault();
                loadUrl(url, { preserveFocus: false });
            });
        });
    }

    function enhance() {
        const root = getRoot();
        if (!root || !hasTable(root)) return;
        eligibleForms(root).forEach(bindForm);
        bindPagination(root);
        bindResetLinks(root);
    }

    window.addEventListener('popstate', () => {
        if (!getRoot() || !hasTable(getRoot())) return;
        isPopState = true;
        loadUrl(new URL(window.location.href), { pushState: false, preserveFocus: false })
            .finally(() => { isPopState = false; });
    });

    document.addEventListener('DOMContentLoaded', () => {
        injectStyles();
        enhance();
    });
})();
