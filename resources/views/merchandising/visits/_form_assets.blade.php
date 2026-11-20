@push('styles')
    <style>
        /* clip, not hidden: never lets the form widen the page sideways on a
           phone, without turning it into its own scroll area. */
        .mv { padding-bottom: 96px; max-width: 100%; overflow-x: clip; }
        /* break-word (not anywhere) only splits words too long for the line,
           so short labels like button text never wrap mid-word. */
        .mv-card, .mv-item, .mv-product { min-width: 0; overflow-wrap: break-word; }
        .mv .btn { white-space: nowrap; }
        .mv-card { background: var(--white); border: 1px solid var(--border-color); border-radius: 16px; padding: 20px; margin-bottom: 16px; }
        .mv-card.is-invalid { border-color: #EF4444; box-shadow: 0 0 0 3px rgba(239, 68, 68, .12); }
        .mv-card__title { display: flex; align-items: center; gap: 10px; font-size: 17px !important; font-weight: 600; margin-bottom: 14px; color: var(--text-primary-light); }
        .mv-step { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; background: rgba(72, 127, 255, .12); color: #487FFF; font-size: 14px; font-weight: 700; flex: 0 0 auto; }
        .mv-subtitle { font-size: 15px !important; font-weight: 600; display: flex; align-items: center; gap: 8px; color: var(--text-primary-light); }
        .mv-count { min-width: 26px; height: 22px; padding: 0 8px; border-radius: 999px; background: #487FFF; color: #fff; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
        .mv-label { display: block; font-weight: 600; font-size: 14px; margin-bottom: 6px; color: var(--text-primary-light); }
        .mv-hint { font-size: 13px; color: var(--text-secondary-light); margin-bottom: 12px; }
        .mv-empty { font-size: 13px; color: var(--text-secondary-light); margin: 8px 0 0; }
        /* 16px: iPhone Safari zooms into any smaller field on tap, and the
           zoomed page then scrolls sideways. */
        .mv-input, .mv textarea, .mv select { min-height: 46px; font-size: 16px !important; border-radius: 10px; }
        /* Safari gives date-time fields a minimum width wider than small phones. */
        .mv input[type="datetime-local"] { display: block; width: 100%; min-width: 0; max-width: 100%; -webkit-appearance: none; appearance: none; }
        .mv-catalog-count { font-size: 12px; color: var(--text-secondary-light); margin-bottom: 8px; }
        .mv-location { --tone: 107, 114, 128; display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; background: rgba(var(--tone), .1); border: 1px solid rgba(var(--tone), .35); color: var(--text-primary-light); }
        .mv-location > i { font-size: 22px; color: rgb(var(--tone)); flex: 0 0 auto; }
        .mv-location .text-xs { color: var(--text-secondary-light); }
        .mv-location.is-ok { --tone: 34, 167, 94; }
        .mv-location.is-warn { --tone: 240, 145, 30; }
        .mv-location.is-bad { --tone: 239, 68, 68; }
        .mv-spin { display: inline-block; animation: mv-spin .9s linear infinite; }
        @keyframes mv-spin { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .mv-spin { animation-duration: 2.5s; } }
        .mv-static { display: flex; align-items: center; gap: 10px; min-height: 46px; padding: 0 12px; border-radius: 10px; background: var(--neutral-50); color: var(--text-primary-light); }
        .mv-static img { width: 28px; height: 28px; border-radius: 50%; object-fit: cover; }
        .mv-link { border: 0; background: none; padding: 0; color: #487FFF; font-size: 14px; text-decoration: underline; }
        .mv-search { position: relative; }
        .mv-search > i { position: absolute; top: 50%; transform: translateY(-50%); inset-inline-start: 14px; color: var(--text-secondary-light); pointer-events: none; }
        .mv-search .mv-input { padding-inline-start: 40px; }
        .mv-results { margin-top: 6px; border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden; max-height: 320px; overflow-y: auto; background: var(--white); }
        .mv-result { display: block; width: 100%; text-align: start; border: 0; background: none; padding: 10px 14px; border-bottom: 1px solid var(--border-color); color: var(--text-primary-light); }
        .mv-result:last-child { border-bottom: 0; }
        .mv-result:hover, .mv-result:focus-visible, .mv-result.is-active { background: rgba(72, 127, 255, .08); outline: none; }
        .mv-result small { display: block; color: var(--text-secondary-light); font-size: 12px; }
        .mv-result-note { padding: 12px 14px; font-size: 13px; color: var(--text-secondary-light); }
        .mv-store-picked { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-radius: 12px; border: 1px solid rgba(72, 127, 255, .4); background: rgba(72, 127, 255, .06); color: var(--text-primary-light); }
        .mv-store-picked > i { font-size: 22px; color: #487FFF; }
        .min-w-0 { min-width: 0; }

        .mv-uploads { display: grid; grid-template-columns: repeat(auto-fill, minmax(104px, 1fr)); gap: 10px; }
        .mv-upload-add, .mv-tile { position: relative; aspect-ratio: 1; border-radius: 12px; overflow: hidden; }
        .mv-upload-add { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; border: 2px dashed var(--neutral-300); color: #487FFF; cursor: pointer; text-align: center; font-size: 13px; padding: 6px; background: var(--neutral-50); transition: border-color .15s; }
        .mv-upload-add:hover, .mv-uploads.is-dragover .mv-upload-add { border-color: #487FFF; }
        .mv-upload-add i { font-size: 26px; }
        .mv-upload-add input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .mv-tile { background: var(--neutral-100); border: 1px solid var(--border-color); }
        .mv-tile img, .mv-tile video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .mv-tile__icon { width: 100%; height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; font-size: 28px; color: var(--text-secondary-light); padding: 6px; text-align: center; }
        .mv-tile__icon small { font-size: 11px; word-break: break-all; line-height: 1.2; }
        .mv-tile__bar { position: absolute; inset-inline: 0; bottom: 0; height: 5px; background: rgba(0, 0, 0, .15); }
        .mv-tile__bar span { display: block; height: 100%; width: 0; background: #487FFF; transition: width .2s; }
        .mv-tile__state { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 4px; background: rgba(17, 24, 39, .55); color: #fff; font-size: 12px; text-align: center; padding: 6px; }
        .mv-tile__state button { border: 0; border-radius: 6px; padding: 4px 10px; font-size: 12px; background: #fff; color: #111827; }
        .mv-tile.is-done .mv-tile__state, .mv-tile.is-done .mv-tile__bar { display: none; }
        .mv-tile__done { position: absolute; top: 6px; inset-inline-start: 6px; width: 22px; height: 22px; border-radius: 50%; background: #22A75E; color: #fff; display: none; align-items: center; justify-content: center; font-size: 14px; }
        .mv-tile.is-done .mv-tile__done { display: flex; }
        .mv-tile__remove { position: absolute; top: 6px; inset-inline-end: 6px; width: 28px; height: 28px; border-radius: 50%; border: 0; background: rgba(17, 24, 39, .7); color: #fff; display: flex; align-items: center; justify-content: center; z-index: 2; }

        .mv-segment { display: flex; flex-wrap: wrap; gap: 6px; }
        .mv-segment button, .mv-chip, .mv-opt {
            min-height: 40px; padding: 6px 14px; border-radius: 999px; border: 1px solid var(--neutral-300); background: var(--white);
            color: var(--text-primary-light); font-size: 14px; transition: background-color .12s, border-color .12s, color .12s;
        }
        .mv-segment button.active, .mv-chip[aria-pressed="true"] { background: #487FFF; border-color: #487FFF; color: #fff; }
        .mv-segment--narrow button { min-width: 88px; }
        .mv-chips { display: flex; flex-wrap: wrap; gap: 8px; }
        .mv-chips--scroll { flex-wrap: nowrap; overflow-x: auto; padding-bottom: 4px; scrollbar-width: thin; }
        .mv-chips--scroll .mv-chip { flex: 0 0 auto; }

        .mv-catalog { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; }
        .mv-product {
            position: relative; display: flex; flex-direction: column; text-align: start; border: 1px solid var(--border-color); border-radius: 12px;
            background: var(--white); padding: 8px; color: var(--text-primary-light); transition: border-color .12s, box-shadow .12s;
        }
        .mv-product:hover { border-color: rgba(72, 127, 255, .5); }
        .mv-product[aria-pressed="true"] { border-color: #487FFF; box-shadow: 0 0 0 2px rgba(72, 127, 255, .35); }
        .mv-product__img { aspect-ratio: 1; border-radius: 8px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 6px; }
        .mv-product__img img { width: 100%; height: 100%; object-fit: contain; }
        .mv-product__img i { font-size: 30px; color: var(--neutral-400); }
        .mv-product__name { font-size: 12.5px; font-weight: 600; line-height: 1.3; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
        .mv-product__sku { font-size: 11px; color: var(--text-secondary-light); margin-top: 2px; }
        .mv-product__check { position: absolute; top: 6px; inset-inline-end: 6px; width: 24px; height: 24px; border-radius: 50%; background: #487FFF; color: #fff; display: none; align-items: center; justify-content: center; }
        .mv-product[aria-pressed="true"] .mv-product__check { display: flex; }

        .mv-item { border: 1px solid var(--border-color); border-radius: 14px; padding: 12px; margin-top: 10px; background: var(--white); }
        .mv-item__head { display: flex; align-items: center; gap: 10px; margin-bottom: 10px; }
        .mv-item__thumb { width: 44px; height: 44px; border-radius: 8px; background: #fff; border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; overflow: hidden; flex: 0 0 auto; }
        .mv-item__thumb img { width: 100%; height: 100%; object-fit: contain; }
        .mv-item__name { font-weight: 600; font-size: 14px; line-height: 1.3; }
        .mv-item__meta { font-size: 12px; color: var(--text-secondary-light); }
        .mv-item__remove { margin-inline-start: auto; border: 0; background: none; color: var(--text-secondary-light); font-size: 20px; width: 36px; height: 36px; border-radius: 8px; flex: 0 0 auto; }
        .mv-item__remove:hover { background: var(--neutral-100); color: #EF4444; }
        .mv-item__group { margin-top: 10px; }
        .mv-item__group-label { font-size: 12px; font-weight: 600; color: var(--text-secondary-light); margin-bottom: 6px; }
        .mv-opt { min-height: 38px; padding: 4px 12px; font-size: 13px; }
        .mv-opt.active { color: #fff; }
        .mv-opt.active.tone-good { background: #22A75E; border-color: #22A75E; }
        .mv-opt.active.tone-warn { background: #F0911E; border-color: #F0911E; }
        .mv-opt.active.tone-bad { background: #EF4444; border-color: #EF4444; }
        .mv-opt.active.tone-neutral { background: #487FFF; border-color: #487FFF; }
        .mv-badge { font-size: 11px; padding: 2px 8px; border-radius: 999px; background: rgba(240, 145, 30, .15); color: #B4690E; margin-inline-start: 6px; }
        .mv-order { padding: 12px; border-radius: 12px; background: var(--neutral-50); }

        .mv-submitbar {
            position: fixed; bottom: 0; inset-inline: 0; z-index: 20; display: flex; align-items: center; justify-content: flex-end; gap: 16px;
            padding: 12px 24px; background: var(--white); border-top: 1px solid var(--border-color); box-shadow: 0 -6px 18px rgba(0, 0, 0, .06);
        }
        @media (min-width: 1200px) { .mv-submitbar { inset-inline-start: 18.5rem; } }
        .mv-submitbar__status { font-size: 13px; color: var(--text-secondary-light); }
        @media (max-width: 575.98px) {
            .mv-card { padding: 16px; border-radius: 14px; }
            .mv-catalog { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .mv-submitbar { padding: 10px 16px; }
            .mv-submitbar .btn { flex: 1; }
            .mv-submitbar__status { display: none; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        (function () {
            'use strict';

            var CATALOG = @json($catalog);
            var ISSUES = @json($issues);
            var DIVISIONS = @json($divisions);
            var T = @json(trans('merchandising'));
            var URLS = {
                stores: @json(route('merchandising.stores.search')),
                uploadStart: @json(route('merchandising.uploads.start')),
                upload: @json(route('merchandising.uploads.status', ['token' => '__T__'])),
                chunk: @json(route('merchandising.uploads.chunk', ['token' => '__T__'])),
            };
            var CSRF = document.querySelector('meta[name="csrf-token"]').content;
            var DRAFT_KEY = 'merch-visit-draft-' + @json(auth()->id());
            var OLD = @json(old('payload'));
            var MAX_BYTES = @json(\App\Models\Merchandising\Upload::MAX_BYTES);
            var RADIUS_M = @json(\App\Services\Merchandising\VisitLocation::RADIUS_M);
            var ACCURACY_ALLOWANCE_M = @json(\App\Services\Merchandising\VisitLocation::MAX_ACCURACY_ALLOWANCE_M);
            // A photo the camera just took is seconds old; anything older came
            // from the gallery. The capture attribute asks phones to open the
            // camera; this catches browsers that offer the gallery anyway.
            var FRESH_MS = 10 * 60 * 1000;
            // A dozen cards at a time: on a phone the whole catalog is an endless page.
            var PAGE = 12;

            var form = document.getElementById('mv-form');
            var byId = function (id) { return document.getElementById(id); };
            // Store and product names are mostly Latin: dir="auto" keeps them
            // reading (and truncating) correctly inside the Persian layout.
            var el = function (tag, cls, text) {
                var n = document.createElement(tag);
                if (cls) { n.className = cls; }
                if (text !== undefined) { n.textContent = text; n.dir = 'auto'; }
                return n;
            };
            var icon = function (name) { var i = el('i', name); i.setAttribute('aria-hidden', 'true'); return i; };
            var uid = function () { return 'u' + Math.random().toString(36).slice(2) + Date.now().toString(36); };
            var productsById = {};
            CATALOG.forEach(function (p) { productsById[p.id] = p; });

            var state = {
                submission_key: @json($submissionKey),
                visited_at: @json($now),
                store: null,
                store_mode: 'search',
                store_name: '',
                items: [],
                actions: [],
                store_feedback: [],
                needs_order: false,
                note: '',
                uploads: [],
            };

            // ---------- Draft (per-viewer convenience only) ----------
            function saveDraft() {
                try {
                    var copy = JSON.parse(JSON.stringify(state));
                    copy.uploads = state.uploads.filter(function (u) { return u.status === 'done'; })
                        .map(function (u) { return { id: u.id, token: u.token, name: u.name, type: u.type, kind: u.kind, size: u.size, status: 'done' }; });
                    localStorage.setItem(DRAFT_KEY, JSON.stringify(copy));
                } catch (e) {}
            }
            var saveSoon = (function () { var t; return function () { clearTimeout(t); t = setTimeout(saveDraft, 400); }; })();

            function loadDraft() {
                if (OLD) {
                    try { return JSON.parse(OLD); } catch (e) {}
                }
                try {
                    var raw = localStorage.getItem(DRAFT_KEY);
                    if (raw) {
                        var draft = JSON.parse(raw);
                        byId('mv-draft-banner').classList.replace('d-none', 'd-flex');
                        return draft;
                    }
                } catch (e) {}
                return null;
            }

            byId('mv-draft-discard').addEventListener('click', function () {
                try { localStorage.removeItem(DRAFT_KEY); } catch (e) {}
                window.location.reload();
            });

            // ---------- Visit & store ----------
            var visitedAt = byId('mv-visited-at');
            visitedAt.addEventListener('change', function () { state.visited_at = visitedAt.value; saveSoon(); });

            var storeSearch = byId('mv-store-search');
            var storeResults = byId('mv-store-results');
            var searchSeq = 0;

            function renderStore() {
                var picked = !!state.store && state.store_mode === 'search';
                byId('mv-store-picked').classList.toggle('d-none', !picked);
                byId('mv-store-searchbox').classList.toggle('d-none', picked || state.store_mode === 'typed');
                byId('mv-store-typed').classList.toggle('d-none', state.store_mode !== 'typed');
                if (picked) {
                    byId('mv-store-picked-name').textContent = state.store.name;
                    byId('mv-store-picked-meta').textContent = [state.store.codes, state.store.place].filter(Boolean).join(' · ');
                }
                byId('mv-store-name').value = state.store_name || '';
            }

            function showResults(list, note) {
                storeResults.textContent = '';
                if (note) {
                    storeResults.appendChild(el('div', 'mv-result-note', note));
                }
                (list || []).forEach(function (s, index) {
                    var b = el('button', 'mv-result' + (index === 0 ? ' is-active' : ''));
                    b.type = 'button';
                    b.setAttribute('role', 'option');
                    b.dir = 'auto';
                    b.appendChild(document.createTextNode(s.name));
                    var meta = [s.codes, s.place, s.address].filter(Boolean).join(' · ');
                    if (meta) { b.appendChild(el('small', null, meta)); }
                    b.addEventListener('click', function () { pickStore(s); });
                    storeResults.appendChild(b);
                });
                storeResults.hidden = false;
                storeSearch.setAttribute('aria-expanded', 'true');
            }

            function pickStore(s) {
                state.store = s;
                state.store_mode = 'search';
                storeResults.hidden = true;
                storeSearch.value = '';
                storeSearch.setAttribute('aria-expanded', 'false');
                clearInvalid('mv-section-visit');
                renderStore();
                renderLocation();
                saveSoon();
            }

            var searchTimer;
            storeSearch.addEventListener('input', function () {
                clearTimeout(searchTimer);
                var q = storeSearch.value.trim();
                if (q.length < 2) { storeResults.hidden = true; return; }
                searchTimer = setTimeout(function () {
                    var seq = ++searchSeq;
                    showResults([], T.store_searching);
                    fetch(URLS.stores + '?q=' + encodeURIComponent(q), { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
                        .then(function (r) { if (!r.ok) { throw new Error(r.status); } return r.json(); })
                        .then(function (list) { if (seq === searchSeq) { showResults(list, list.length ? null : T.store_no_results); } })
                        .catch(function () { if (seq === searchSeq) { showResults([], T.store_no_results); } });
                }, 250);
            });

            storeSearch.addEventListener('keydown', function (e) {
                var options = Array.prototype.slice.call(storeResults.querySelectorAll('.mv-result'));
                var current = options.findIndex(function (o) { return o.classList.contains('is-active'); });
                if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                    e.preventDefault();
                    var next = Math.max(0, Math.min(options.length - 1, current + (e.key === 'ArrowDown' ? 1 : -1)));
                    options.forEach(function (o, i) { o.classList.toggle('is-active', i === next); });
                    if (options[next]) { options[next].scrollIntoView({ block: 'nearest' }); }
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (options[current]) { options[current].click(); }
                } else if (e.key === 'Escape') {
                    storeResults.hidden = true;
                }
            });

            byId('mv-store-change').addEventListener('click', function () {
                state.store = null; renderStore(); renderLocation(); storeSearch.focus(); saveSoon();
            });
            byId('mv-store-type-toggle').addEventListener('click', function () {
                state.store_mode = 'typed'; state.store = null; renderStore(); renderLocation(); byId('mv-store-name').focus(); saveSoon();
            });
            byId('mv-store-search-toggle').addEventListener('click', function () {
                state.store_mode = 'search'; state.store_name = ''; renderStore(); renderLocation(); storeSearch.focus(); saveSoon();
            });
            byId('mv-store-name').addEventListener('input', function (e) {
                state.store_name = e.target.value; clearInvalid('mv-section-visit'); saveSoon();
            });

            // ---------- Products ----------
            var filter = { division: '', line: '', q: '', limit: PAGE };
            var catalogEl = byId('mv-catalog');
            var moreBtn = byId('mv-catalog-more');

            function selectedIndex(productId) {
                return state.items.findIndex(function (it) { return it.product_id === productId; });
            }

            function matching() {
                var q = filter.q.toLowerCase();
                return CATALOG.filter(function (p) {
                    return (!filter.division || p.division === filter.division)
                        && (!filter.line || p.line === filter.line)
                        && (!q || p.name.toLowerCase().indexOf(q) !== -1 || String(p.sku).indexOf(q) === 0);
                });
            }

            var lineSelect = byId('mv-line');

            function renderLines() {
                var lines = {};
                CATALOG.forEach(function (p) { if (!filter.division || p.division === filter.division) { lines[p.line] = (lines[p.line] || 0) + 1; } });
                lineSelect.textContent = '';
                var all = el('option', null, T.products_all_lines_option);
                all.value = '';
                lineSelect.appendChild(all);
                Object.keys(lines).sort().forEach(function (line) {
                    var option = el('option', null, line + ' (' + lines[line] + ')');
                    option.value = line;
                    lineSelect.appendChild(option);
                });
                lineSelect.value = lines[filter.line] ? filter.line : '';
                filter.line = lineSelect.value;
            }

            lineSelect.addEventListener('change', function () { filter.line = lineSelect.value; filter.limit = PAGE; renderCatalog(); });

            function renderCatalog() {
                var list = matching();
                catalogEl.textContent = '';
                if (!list.length) {
                    catalogEl.appendChild(el('p', 'mv-empty', T.products_no_match));
                }
                list.slice(0, filter.limit).forEach(function (p) {
                    var card = el('button', 'mv-product');
                    card.type = 'button';
                    card.dataset.id = p.id;
                    card.setAttribute('aria-pressed', String(selectedIndex(p.id) !== -1));
                    var img = el('span', 'mv-product__img');
                    if (p.image) {
                        var image = el('img'); image.src = p.image; image.alt = ''; image.loading = 'lazy';
                        img.appendChild(image);
                    } else {
                        img.appendChild(icon('ri-shopping-bag-3-line'));
                    }
                    card.appendChild(img);
                    card.appendChild(el('span', 'mv-product__name', p.name));
                    card.appendChild(el('span', 'mv-product__sku', p.sku));
                    var check = el('span', 'mv-product__check'); check.appendChild(icon('ri-check-line'));
                    card.appendChild(check);
                    card.addEventListener('click', function () { toggleProduct(p); });
                    catalogEl.appendChild(card);
                });
                var shown = Math.min(filter.limit, list.length);
                var rest = list.length - shown;
                byId('mv-catalog-count').textContent = list.length ? T.products_showing.replace(':shown', shown).replace(':total', list.length) : '';
                moreBtn.classList.toggle('d-none', rest <= 0);
                moreBtn.textContent = T.products_show_more.replace(':count', Math.min(PAGE, rest));
            }

            moreBtn.addEventListener('click', function () { filter.limit += PAGE; renderCatalog(); });
            byId('mv-product-search').addEventListener('input', function (e) { filter.q = e.target.value.trim(); filter.limit = PAGE; renderCatalog(); });
            Array.prototype.forEach.call(document.querySelectorAll('#mv-division-tabs button'), function (b) {
                b.addEventListener('click', function () {
                    document.querySelectorAll('#mv-division-tabs button').forEach(function (x) { x.classList.toggle('active', x === b); });
                    filter.division = b.dataset.division; filter.line = ''; filter.limit = PAGE;
                    renderLines(); renderCatalog();
                });
            });

            function newItem(fields) {
                return Object.assign({ key: uid(), product_id: null, product_name: '', division: null, stock_status: null, shelf_position: null, price_tag: null, issues: [], needs_order: false, order_touched: false, expanded: false }, fields);
            }

            function toggleProduct(p) {
                var index = selectedIndex(p.id);
                if (index !== -1) {
                    state.items.splice(index, 1);
                } else {
                    state.items.unshift(newItem({ product_id: p.id, division: p.division }));
                }
                renderCatalogMarks(); renderItems(); renderOrder(); saveSoon();
            }

            function renderCatalogMarks() {
                catalogEl.querySelectorAll('.mv-product').forEach(function (card) {
                    card.setAttribute('aria-pressed', String(selectedIndex(Number(card.dataset.id)) !== -1));
                });
            }

            byId('mv-custom-add').addEventListener('click', addCustom);
            byId('mv-custom-name').addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); addCustom(); } });
            function addCustom() {
                var input = byId('mv-custom-name');
                var name = input.value.trim();
                if (!name) { input.focus(); return; }
                state.items.unshift(newItem({ product_name: name, division: DIVISIONS.length === 1 ? DIVISIONS[0] : null }));
                input.value = '';
                renderItems(); renderOrder(); saveSoon();
            }

            var GROUPS = [
                { field: 'stock_status', label: T.field_stock, options: [['in_stock', T.stock_in_stock, 'good'], ['low', T.stock_low, 'warn'], ['out_of_stock', T.stock_out_of_stock, 'bad'], ['not_listed', T.stock_not_listed, 'bad']], always: true },
                { field: 'shelf_position', label: T.field_shelf, options: [['eye_level', T.shelf_eye_level, 'good'], ['middle', T.shelf_middle, 'neutral'], ['bottom', T.shelf_bottom, 'warn'], ['top', T.shelf_top, 'neutral'], ['storage', T.shelf_storage, 'warn']] },
                { field: 'price_tag', label: T.field_price_tag, options: [['correct', T.price_tag_correct, 'good'], ['missing', T.price_tag_missing, 'bad'], ['wrong', T.price_tag_wrong, 'bad']] },
            ];

            function renderItems() {
                var box = byId('mv-selected');
                box.textContent = '';
                byId('mv-selected-count').textContent = state.items.length;
                byId('mv-selected-empty').classList.toggle('d-none', state.items.length > 0);

                state.items.forEach(function (item) {
                    var product = item.product_id ? productsById[item.product_id] : null;
                    var card = el('div', 'mv-item');
                    var head = el('div', 'mv-item__head');
                    var thumb = el('span', 'mv-item__thumb');
                    if (product && product.image) { var im = el('img'); im.src = product.image; im.alt = ''; thumb.appendChild(im); } else { thumb.appendChild(icon('ri-shopping-bag-3-line')); }
                    head.appendChild(thumb);
                    var text = el('div', 'min-w-0');
                    var name = el('div', 'mv-item__name', product ? product.name : item.product_name);
                    if (!product) { name.appendChild(el('span', 'mv-badge', T.product_custom_badge)); }
                    text.appendChild(name);
                    if (product) { text.appendChild(el('div', 'mv-item__meta', product.sku + ' · ' + product.line)); }
                    head.appendChild(text);
                    var remove = el('button', 'mv-item__remove'); remove.type = 'button'; remove.title = T.product_remove; remove.setAttribute('aria-label', T.product_remove);
                    remove.appendChild(icon('ri-close-line'));
                    remove.addEventListener('click', function () {
                        state.items = state.items.filter(function (x) { return x.key !== item.key; });
                        renderCatalogMarks(); renderItems(); renderOrder(); saveSoon();
                    });
                    head.appendChild(remove);
                    card.appendChild(head);

                    GROUPS.forEach(function (group) {
                        if (!group.always && !item.expanded) { return; }
                        var wrap = el('div', 'mv-item__group');
                        wrap.appendChild(el('div', 'mv-item__group-label', group.label));
                        var row = el('div', 'mv-segment');
                        group.options.forEach(function (opt) {
                            var b = el('button', 'mv-opt tone-' + opt[2] + (item[group.field] === opt[0] ? ' active' : ''), opt[1]);
                            b.type = 'button';
                            b.setAttribute('aria-pressed', String(item[group.field] === opt[0]));
                            b.addEventListener('click', function () {
                                item[group.field] = item[group.field] === opt[0] ? null : opt[0];
                                if (group.field === 'stock_status' && state.needs_order && !item.order_touched) {
                                    item.needs_order = item.stock_status === 'out_of_stock' || item.stock_status === 'low';
                                }
                                renderItems(); renderOrder(); saveSoon();
                            });
                            row.appendChild(b);
                        });
                        wrap.appendChild(row);
                        card.appendChild(wrap);
                    });

                    if (item.expanded) {
                        var issueWrap = el('div', 'mv-item__group');
                        issueWrap.appendChild(el('div', 'mv-item__group-label', T.field_issues));
                        var chips = el('div', 'mv-chips');
                        Object.keys(ISSUES).forEach(function (code) {
                            var on = item.issues.indexOf(code) !== -1;
                            var b = el('button', 'mv-chip', ISSUES[code]); b.type = 'button';
                            b.setAttribute('aria-pressed', String(on));
                            b.addEventListener('click', function () {
                                item.issues = on ? item.issues.filter(function (c) { return c !== code; }) : item.issues.concat(code);
                                renderItems(); saveSoon();
                            });
                            chips.appendChild(b);
                        });
                        issueWrap.appendChild(chips);
                        card.appendChild(issueWrap);
                    }

                    var details = [item.shelf_position, item.price_tag].filter(Boolean).length + item.issues.length;
                    var more = el('button', 'mv-link mt-12', (item.expanded ? T.product_less : T.product_more) + (!item.expanded && details ? ' (' + details + ')' : ''));
                    more.type = 'button';
                    more.addEventListener('click', function () { item.expanded = !item.expanded; renderItems(); });
                    card.appendChild(more);
                    box.appendChild(card);
                });
            }

            // ---------- Multi-select chips (actions, feedback) ----------
            document.querySelectorAll('[data-multi]').forEach(function (group) {
                var field = group.dataset.multi;
                group.querySelectorAll('.mv-chip').forEach(function (chip) {
                    chip.addEventListener('click', function () {
                        var value = chip.dataset.value;
                        var on = state[field].indexOf(value) !== -1;
                        state[field] = on ? state[field].filter(function (v) { return v !== value; }) : state[field].concat(value);
                        chip.setAttribute('aria-pressed', String(!on));
                        saveSoon();
                    });
                });
            });

            function syncChips() {
                document.querySelectorAll('[data-multi]').forEach(function (group) {
                    group.querySelectorAll('.mv-chip').forEach(function (chip) {
                        chip.setAttribute('aria-pressed', String(state[group.dataset.multi].indexOf(chip.dataset.value) !== -1));
                    });
                });
            }

            // ---------- Order ----------
            document.querySelectorAll('#mv-needs-order button').forEach(function (b) {
                b.addEventListener('click', function () {
                    var on = b.dataset.value === '1';
                    if (on && !state.needs_order) {
                        state.items.forEach(function (item) {
                            if (!item.order_touched) { item.needs_order = item.stock_status === 'out_of_stock' || item.stock_status === 'low'; }
                        });
                    }
                    state.needs_order = on;
                    renderOrder(); saveSoon();
                });
            });

            function renderOrder() {
                document.querySelectorAll('#mv-needs-order button').forEach(function (b) { b.classList.toggle('active', (b.dataset.value === '1') === state.needs_order); });
                byId('mv-order-box').classList.toggle('d-none', !state.needs_order);
                var list = byId('mv-order-list');
                list.textContent = '';
                byId('mv-order-empty').classList.toggle('d-none', state.items.length > 0);
                state.items.forEach(function (item) {
                    var product = item.product_id ? productsById[item.product_id] : null;
                    var chip = el('button', 'mv-chip', product ? product.name : item.product_name);
                    chip.type = 'button';
                    chip.setAttribute('aria-pressed', String(item.needs_order));
                    chip.addEventListener('click', function () {
                        item.needs_order = !item.needs_order; item.order_touched = true;
                        chip.setAttribute('aria-pressed', String(item.needs_order)); saveSoon();
                    });
                    list.appendChild(chip);
                });
            }

            // ---------- Note ----------
            var note = byId('mv-note');
            note.addEventListener('input', function () { state.note = note.value; byId('mv-note-count').textContent = note.value.length; saveSoon(); });

            // ---------- Uploads ----------
            var active = 0;
            var MAX_PARALLEL = 2;

            function tileFor(upload) { return document.querySelector('.mv-tile[data-id="' + upload.id + '"]'); }

            function addFiles(kind, files) {
                Array.prototype.forEach.call(files, function (file) {
                    var upload = { id: uid(), kind: kind, file: file, name: file.name, type: file.type, size: file.size, status: 'queued', progress: 0, token: null, error: '' };
                    if (file.size > MAX_BYTES) { upload.status = 'error'; upload.error = T.upload_too_large; upload.fatal = true; }
                    if (kind !== 'extra' && file.type && file.type.indexOf('image/') !== 0) { upload.status = 'error'; upload.error = T.upload_wrong_type; upload.fatal = true; }
                    if (file.lastModified && Date.now() - file.lastModified > FRESH_MS) { upload.status = 'error'; upload.error = T.upload_not_fresh; upload.fatal = true; }
                    state.uploads.push(upload);
                    renderTile(upload);
                });
                clearInvalid('mv-section-photos');
                pump();
            }

            function renderTile(upload) {
                var box = document.querySelector('.mv-uploads[data-kind="' + upload.kind + '"]');
                var tile = tileFor(upload);
                if (!tile) {
                    tile = el('div', 'mv-tile');
                    tile.dataset.id = upload.id;
                    var media;
                    if (upload.file && upload.type.indexOf('image/') === 0 && /jpe?g|png|webp|gif/i.test(upload.type)) {
                        media = el('img'); media.alt = ''; media.src = URL.createObjectURL(upload.file);
                    } else {
                        media = el('div', 'mv-tile__icon');
                        media.appendChild(icon(upload.type.indexOf('video/') === 0 ? 'ri-film-line' : 'ri-image-line'));
                        media.appendChild(el('small', null, upload.name));
                    }
                    tile.appendChild(media);
                    var done = el('span', 'mv-tile__done'); done.appendChild(icon('ri-check-line')); tile.appendChild(done);
                    var bar = el('div', 'mv-tile__bar'); bar.appendChild(el('span')); tile.appendChild(bar);
                    tile.appendChild(el('div', 'mv-tile__state'));
                    var remove = el('button', 'mv-tile__remove'); remove.type = 'button'; remove.title = T.upload_remove; remove.setAttribute('aria-label', T.upload_remove);
                    remove.appendChild(icon('ri-close-line'));
                    remove.addEventListener('click', function () { removeUpload(upload); });
                    tile.appendChild(remove);
                    box.insertBefore(tile, box.querySelector('.mv-upload-add'));
                }
                tile.classList.toggle('is-done', upload.status === 'done');
                tile.querySelector('.mv-tile__bar span').style.width = upload.progress + '%';
                var stateBox = tile.querySelector('.mv-tile__state');
                stateBox.textContent = '';
                if (upload.status === 'error') {
                    stateBox.appendChild(icon('ri-error-warning-line'));
                    stateBox.appendChild(el('span', null, upload.error || T.upload_failed));
                    if (!upload.fatal) {
                        var retry = el('button', null, T.upload_retry); retry.type = 'button';
                        retry.addEventListener('click', function () { upload.status = 'queued'; upload.error = ''; renderTile(upload); pump(); });
                        stateBox.appendChild(retry);
                    }
                } else if (upload.status !== 'done') {
                    stateBox.appendChild(el('span', null, upload.status === 'queued' ? T.upload_waiting : upload.status === 'preparing' ? T.upload_preparing : T.uploading + ' ' + upload.progress + '%'));
                }
                updateSubmitStatus();
            }

            function removeUpload(upload) {
                upload.cancelled = true;
                state.uploads = state.uploads.filter(function (u) { return u.id !== upload.id; });
                var tile = tileFor(upload);
                if (tile) { tile.remove(); }
                if (upload.token) {
                    fetch(URLS.upload.replace('__T__', upload.token), { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }, credentials: 'same-origin' }).catch(function () {});
                }
                updateSubmitStatus(); saveSoon();
            }

            function pump() {
                while (active < MAX_PARALLEL) {
                    var next = state.uploads.find(function (u) { return u.status === 'queued' && u.file; });
                    if (!next) { return; }
                    active++;
                    run(next).finally(function () { active--; pump(); });
                }
            }

            // Photos straight off a phone are often 5-10 MB; a 2560 px JPEG
            // looks the same on a shelf photo and uploads in a fraction of the time.
            function compress(file) {
                if (!/^image\/(jpeg|png|webp)$/.test(file.type) || file.size < 1.5 * 1024 * 1024 || !window.createImageBitmap) {
                    return Promise.resolve(file);
                }
                return createImageBitmap(file, { imageOrientation: 'from-image' }).then(function (bitmap) {
                    var scale = Math.min(1, 2560 / Math.max(bitmap.width, bitmap.height));
                    var canvas = document.createElement('canvas');
                    canvas.width = Math.round(bitmap.width * scale);
                    canvas.height = Math.round(bitmap.height * scale);
                    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                    return new Promise(function (resolve) {
                        canvas.toBlob(function (blob) {
                            resolve(blob && blob.size < file.size ? new File([blob], file.name.replace(/\.\w+$/, '') + '.jpg', { type: 'image/jpeg' }) : file);
                        }, 'image/jpeg', 0.85);
                    });
                }).catch(function () { return file; });
            }

            function api(url, options) {
                options = options || {};
                options.credentials = 'same-origin';
                options.headers = Object.assign({ 'X-CSRF-TOKEN': CSRF, Accept: 'application/json' }, options.headers || {});
                return fetch(url, options).then(function (r) {
                    return r.json().catch(function () { return {}; }).then(function (body) { return { status: r.status, ok: r.ok, body: body }; });
                });
            }

            function wait(ms) { return new Promise(function (resolve) { setTimeout(resolve, ms); }); }

            function run(upload) {
                upload.status = 'preparing'; renderTile(upload);
                return compress(upload.file).then(function (file) {
                    upload.file = file; upload.size = file.size; upload.type = file.type || upload.type; upload.name = file.name;
                    var start = upload.token
                        ? api(URLS.upload.replace('__T__', upload.token)).then(function (r) { if (!r.ok) { throw r; } return { token: upload.token, received: r.body.received, chunk: upload.chunk }; })
                        : api(URLS.uploadStart, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ kind: upload.kind, name: file.name, size: file.size, type: file.type }) })
                            .then(function (r) { if (!r.ok) { throw r; } return { token: r.body.token, received: r.body.received, chunk: r.body.chunk_size }; });
                    return start;
                }).then(function (info) {
                    upload.token = info.token; upload.chunk = info.chunk || 786432;
                    upload.status = 'uploading';
                    return sendFrom(upload, info.received, 0);
                }).then(function () {
                    if (upload.cancelled) { return; }
                    upload.status = 'done'; upload.progress = 100; upload.file = upload.file;
                    renderTile(upload); saveSoon();
                }).catch(function (r) {
                    if (upload.cancelled) { return; }
                    upload.status = 'error';
                    upload.error = (r && r.body && r.body.message) || T.upload_failed;
                    upload.fatal = !!(r && r.status === 422 && r.body && r.body.message);
                    renderTile(upload);
                });
            }

            function sendFrom(upload, offset, failures) {
                if (upload.cancelled) { return Promise.resolve(); }
                upload.progress = Math.floor(offset / upload.size * 100);
                renderTile(upload);
                if (offset >= upload.size) { return Promise.resolve(); }
                var blob = upload.file.slice(offset, offset + upload.chunk);
                return api(URLS.chunk.replace('__T__', upload.token) + '?offset=' + offset, { method: 'POST', headers: { 'Content-Type': 'application/octet-stream' }, body: blob })
                    .then(function (r) {
                        if (r.status === 409) { return sendFrom(upload, r.body.received, failures); }
                        if (!r.ok) { throw r; }
                        if (r.body.complete) { return; }
                        return sendFrom(upload, r.body.received, 0);
                    })
                    .catch(function (err) {
                        // Network blips: back off and resume from what the server has.
                        if (err && err.status === 422) { throw err; }
                        if (failures >= 4) { throw err; }
                        return wait(800 * Math.pow(2, failures)).then(function () {
                            return api(URLS.upload.replace('__T__', upload.token)).then(function (r) {
                                if (!r.ok) { throw r; }
                                return r.body.complete ? undefined : sendFrom(upload, r.body.received, failures + 1);
                            });
                        });
                    });
            }

            document.querySelectorAll('[data-upload-input]').forEach(function (input) {
                input.addEventListener('change', function () { addFiles(input.dataset.uploadInput, input.files); input.value = ''; });
            });
            // No drag-and-drop: photos must come from the camera, not from files.

            // ---------- Location ----------
            var geo = { status: 'pending', lat: null, lng: null, accuracy: null, at: 0 };

            function metres(lat1, lng1, lat2, lng2) {
                var rad = Math.PI / 180;
                var a = Math.pow(Math.sin((lat2 - lat1) * rad / 2), 2) + Math.cos(lat1 * rad) * Math.cos(lat2 * rad) * Math.pow(Math.sin((lng2 - lng1) * rad / 2), 2);
                return 6371000 * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
            }

            // Mirrors VisitLocation::assess() on the server.
            function locationCheck() {
                if (geo.status === 'pending') { return { state: 'pending' }; }
                if (geo.status !== 'ok') { return { state: 'off' }; }
                var store = state.store_mode === 'search' ? state.store : null;
                if (!store) { return { state: state.store_mode === 'typed' ? 'unmapped' : 'no_store' }; }
                if (store.lat === null || store.lat === undefined) { return { state: 'unmapped' }; }
                var distance = Math.round(metres(geo.lat, geo.lng, store.lat, store.lng));
                var allowed = distance <= RADIUS_M + Math.min(geo.accuracy || 0, ACCURACY_ALLOWANCE_M);
                return { state: allowed ? 'inside' : 'outside', distance: distance };
            }

            function renderLocation() {
                var check = locationCheck();
                var box = byId('mv-location');
                var view = {
                    pending: ['', 'ri-loader-4-line mv-spin', T.location_checking, ''],
                    no_store: ['is-ok', 'ri-map-pin-line', T.location_found, T.location_pick_store],
                    inside: ['is-ok', 'ri-map-pin-2-fill', T.location_ok.replace(':distance', check.distance), ''],
                    outside: ['is-bad', 'ri-map-pin-range-line', T.location_outside_title, T.location_outside.replace(':distance', check.distance).replace(':radius', RADIUS_M)],
                    unmapped: ['is-warn', 'ri-map-pin-line', T.location_found, T.location_unmapped],
                    off: ['is-warn', 'ri-map-pin-off-line', T.location_off, T.location_off_hint],
                }[check.state];
                box.className = 'mv-location ' + view[0];
                byId('mv-location-icon').className = view[1];
                byId('mv-location-title').textContent = view[2];
                byId('mv-location-detail').textContent = view[3];
                byId('mv-location-retry').classList.toggle('d-none', ['outside', 'off'].indexOf(check.state) === -1);
            }

            function locate() {
                return new Promise(function (resolve) {
                    if (!navigator.geolocation || !window.isSecureContext) {
                        geo = { status: 'unavailable', at: Date.now() };
                        renderLocation();
                        resolve();
                        return;
                    }
                    geo.status = 'pending';
                    renderLocation();
                    navigator.geolocation.getCurrentPosition(function (position) {
                        geo = { status: 'ok', lat: position.coords.latitude, lng: position.coords.longitude, accuracy: position.coords.accuracy, at: Date.now() };
                        renderLocation();
                        resolve();
                    }, function (error) {
                        geo = { status: error.code === 1 ? 'denied' : 'unavailable', at: Date.now() };
                        renderLocation();
                        resolve();
                    }, { enableHighAccuracy: true, timeout: 20000, maximumAge: 30000 });
                });
            }

            byId('mv-location-retry').addEventListener('click', function () { locate(); });

            function updateSubmitStatus() {
                var busy = state.uploads.filter(function (u) { return u.status === 'queued' || u.status === 'uploading' || u.status === 'preparing'; }).length;
                byId('mv-submit-status').textContent = busy ? T.uploading + ' (' + busy + ')' : '';
            }

            // ---------- Validation & submit ----------
            function markInvalid(sectionId) { var s = byId(sectionId); if (s) { s.classList.add('is-invalid'); } }
            function clearInvalid(sectionId) { var s = byId(sectionId); if (s) { s.classList.remove('is-invalid'); } }

            function validate() {
                var errors = [];
                document.querySelectorAll('.mv-card.is-invalid').forEach(function (s) { s.classList.remove('is-invalid'); });
                if (!state.visited_at) { errors.push([T.field_visited_at, 'mv-section-visit']); }
                if (!(state.store && state.store_mode === 'search') && !(state.store_mode === 'typed' && state.store_name.trim())) { errors.push([T.error_store, 'mv-section-visit']); }
                var done = function (kind) { return state.uploads.some(function (u) { return u.kind === kind && u.status === 'done'; }); };
                if (!done('before')) { errors.push([T.error_photo_before, 'mv-section-photos']); }
                if (!done('after')) { errors.push([T.error_photo_after, 'mv-section-photos']); }
                if (state.uploads.some(function (u) { return ['queued', 'uploading', 'preparing'].indexOf(u.status) !== -1; })) { errors.push([T.error_uploads_pending, 'mv-section-photos']); }
                if (state.uploads.some(function (u) { return u.status === 'error'; })) { errors.push([T.error_uploads_failed, 'mv-section-photos']); }
                var check = locationCheck();
                if (check.state === 'outside') { errors.push([T.location_outside.replace(':distance', check.distance).replace(':radius', RADIUS_M), 'mv-section-visit']); }
                return errors;
            }

            var submitting = false;
            var checkingLocation = false;
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (submitting || checkingLocation) { return; }

                // Re-read the position if it's stale: the check must reflect
                // where the merchandiser is when submitting.
                if (geo.status !== 'pending' && Date.now() - geo.at > 2 * 60 * 1000) {
                    checkingLocation = true;
                    byId('mv-submit').disabled = true;
                    locate().then(function () {
                        checkingLocation = false;
                        byId('mv-submit').disabled = false;
                        form.requestSubmit ? form.requestSubmit() : form.dispatchEvent(new Event('submit', { cancelable: true }));
                    });
                    return;
                }

                var errors = validate();
                var box = byId('mv-errors');
                var list = byId('mv-error-list');
                list.textContent = '';
                if (errors.length) {
                    errors.forEach(function (err) { list.appendChild(el('li', null, err[0])); markInvalid(err[1]); });
                    box.classList.remove('d-none');
                    box.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    box.focus({ preventScroll: true });
                    return;
                }
                box.classList.add('d-none');

                var payload = {
                    submission_key: state.submission_key,
                    visited_at: state.visited_at,
                    store_id: state.store_mode === 'search' && state.store ? state.store.id : null,
                    store_name: state.store_mode === 'typed' ? state.store_name.trim() : null,
                    store: state.store,
                    store_mode: state.store_mode,
                    items: state.items.map(function (it) {
                        return { product_id: it.product_id, product_name: it.product_id ? null : it.product_name, division: it.division, stock_status: it.stock_status, shelf_position: it.shelf_position, price_tag: it.price_tag, issues: it.issues, needs_order: state.needs_order && it.needs_order };
                    }),
                    actions: state.actions,
                    store_feedback: state.store_feedback,
                    needs_order: state.needs_order,
                    note: state.note.trim() || null,
                    location: geo.status === 'ok' ? { lat: geo.lat, lng: geo.lng, accuracy: geo.accuracy } : null,
                    uploads: state.uploads.filter(function (u) { return u.status === 'done'; }).map(function (u) { return u.token; }),
                    // Echoed back if the server rejects the visit, so the form restores as it was.
                    uploads_meta: state.uploads.filter(function (u) { return u.status === 'done'; }).map(function (u) { return { id: u.id, token: u.token, name: u.name, type: u.type, kind: u.kind, size: u.size }; }),
                    ui_items: state.items,
                };
                byId('mv-payload').value = JSON.stringify(payload);
                submitting = true;
                var button = byId('mv-submit');
                button.disabled = true;
                button.querySelector('span').textContent = T.submitting;
                try { localStorage.removeItem(DRAFT_KEY); } catch (err) {}
                form.submit();
            });

            // ---------- Boot ----------
            var restored = loadDraft();
            if (restored) {
                state.submission_key = restored.submission_key || state.submission_key;
                state.visited_at = restored.visited_at || state.visited_at;
                state.store = restored.store || null;
                state.store_mode = restored.store_mode || (restored.store_name ? 'typed' : 'search');
                state.store_name = restored.store_name || '';
                state.items = (restored.ui_items || restored.items || []).map(function (it) { return newItem(it); })
                    .filter(function (it) { return it.product_id ? !!productsById[it.product_id] : !!it.product_name; });
                state.actions = restored.actions || [];
                state.store_feedback = restored.store_feedback || [];
                state.needs_order = !!restored.needs_order;
                state.note = restored.note || '';
                state.uploads = (restored.uploads_meta || restored.uploads || []).filter(function (u) { return u && u.token; })
                    .map(function (u) { return Object.assign({ id: uid(), progress: 100 }, u, { status: 'done' }); });
            }

            visitedAt.value = state.visited_at;
            note.value = state.note;
            byId('mv-note-count').textContent = state.note.length;
            renderStore();
            locate();
            renderLines();
            renderCatalog();
            renderItems();
            renderOrder();
            syncChips();
            state.uploads.forEach(renderTile);
            if (OLD) { saveDraft(); }

            window.addEventListener('beforeunload', function (e) {
                if (!submitting && state.uploads.some(function (u) { return ['queued', 'uploading', 'preparing'].indexOf(u.status) !== -1; })) {
                    e.preventDefault(); e.returnValue = '';
                }
            });
        })();
    </script>
@endpush
