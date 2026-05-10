let currentLimit = 12;
let page = 1;
let query = '*';
let sortOrder = '';
let totalCount = 0;
let totalPages = 1;
let headerShown = false;
let initialLoad = true;
let cachedData = [];
let timer;

const $input = document.getElementById('searchInput');
const $btn = document.getElementById('searchBtn');
const $grid = document.getElementById('resultsGrid');
const $header = document.getElementById('resultsHeader');
const $queryEl = document.getElementById('resultsQuery');
const $meta = document.getElementById('resultsMeta');
const $empty = document.getElementById('resultsEmpty');
const $emptyText = document.getElementById('resultsEmptyText');
const $pagination = document.getElementById('resultsPagination');
const $sort = document.getElementById('sortSelect');
const $drawer = document.getElementById('drawer');
const $drawerPanel = document.querySelector('.drawer__panel');
const $drawerBody = document.getElementById('drawerBody');
const $drawerClose = document.getElementById('drawerClose');
const $drawerOverlay = document.getElementById('drawerOverlay');
const $searchTip = document.getElementById('searchTip');
const $suggestion = document.getElementById('searchSuggestion');

let currentSuggestion = '';
let compareList = new Map();
let selectedFreelanceIds = new Set();
let selectedSkills = new Set();
let allSkillsData = [];

async function fetchSkills() {
    if (!isSearchPage) return;
    try {
        const res = await fetch('/api/freelances/skills');
        allSkillsData = await res.json();
        renderSkillsList();
    } catch (e) { console.error("Error fetching skills", e); }
}

let lastFacets = {};

function renderSkillsList(filter = '') {
    const $list = document.getElementById('skillsList');
    if (!$list) return;

    const filtered = allSkillsData.filter(s => s.name.toLowerCase().includes(filter.toLowerCase()));

    filtered.sort((a, b) => {
        const countA = lastFacets[a.name] || 0;
        const countB = lastFacets[b.name] || 0;
        if (countB !== countA) return countB - countA;
        return a.name.localeCompare(b.name);
    });
    
    $list.innerHTML = filtered.map(s => {
        const count = lastFacets[s.name] || 0;
        const isDisabled = count === 0 && !selectedSkills.has(s.name);
        
        return `
        <div class="skill-option ${selectedSkills.has(s.name) ? 'active' : ''} ${isDisabled ? 'disabled' : ''}" 
             data-skill="${s.name}" 
             ${isDisabled ? 'style="opacity:0.4;pointer-events:none;filter:grayscale(1)"' : ''}>
            <div class="skill-option__main">
                <div class="skill-option__checkbox"></div>
                <span>${s.name}</span>
            </div>
            <span class="skill-option__count">${count}</span>
        </div>
    `;}).join('');

    $list.querySelectorAll('.skill-option:not(.disabled)').forEach(opt => {
        opt.onclick = () => {
            const skill = opt.dataset.skill;
            if (selectedSkills.has(skill)) selectedSkills.delete(skill);
            else selectedSkills.add(skill);
            opt.classList.toggle('active');
            document.getElementById('selectedSkillsCount').textContent = selectedSkills.size;
        };
    });
}

const isSearchPage = !!$grid;

if (isSearchPage) {
    const $popup = document.getElementById('skillsPopup');
    const $toggle = document.getElementById('btnSkillsToggle');
    const $apply = document.getElementById('btnApplySkills');
    const $clear = document.getElementById('btnClearSkills');
    const $sInput = document.getElementById('skillSearch');

    if ($toggle) $toggle.onclick = (e) => {
        e.stopPropagation();
        const show = $popup.getAttribute('aria-hidden') === 'true';
        $popup.setAttribute('aria-hidden', !show);
    };

    if ($sInput) $sInput.oninput = (e) => renderSkillsList(e.target.value);

    if ($apply) $apply.onclick = () => {
        $popup.setAttribute('aria-hidden', 'true');
        search(query, 1);
    };

    if ($clear) $clear.onclick = () => {
        selectedSkills.clear();
        document.getElementById('selectedSkillsCount').textContent = '0';
        renderSkillsList();
        $popup.setAttribute('aria-hidden', 'true');
        search(query, 1);
    };

    document.addEventListener('click', (e) => {
        if ($popup && !$popup.contains(e.target) && e.target !== $toggle) {
            $popup.setAttribute('aria-hidden', 'true');
        }
    });
}

async function fetchSelectedIds() {
    if (!window.APP_USER || !window.APP_USER.is_logged_in) return;
    try {
        const res = await fetch('/api/selections/ids');
        const ids = await res.json();
        selectedFreelanceIds = new Set(ids);
    } catch (e) { console.error("Error fetching selected IDs", e); }
}

async function renderProfileSelection() {
    const $pGrid = document.querySelector('.selection-box__grid');
    const $pCount = document.querySelector('.selection-box__count');
    if (!$pGrid) return;

    const res = await fetch('/api/selections/default');
    const data = await res.json();
    
    if ($pCount) $pCount.textContent = `${data.freelances.length} ${trans('profil(s) enregistré(s)')}`;

    if (data.freelances.length === 0) {
        $pGrid.innerHTML = `<div class="selection-empty"><p>${trans('Votre liste est encore vide.')}</p></div>`;
        return;
    }

    $pGrid.innerHTML = data.freelances.map(f => `
        <div class="mini-talent-card" onclick="openDrawer(${f.id})">
            <div class="mini-talent-card__initials">${f.initials}</div>
            <div class="mini-talent-card__body">
                <p class="mini-talent-card__name">${f.firstName} ${f.lastName}</p>
                <p class="mini-talent-card__job">${f.jobTitle}</p>
            </div>
        </div>
    `).join('');
}

function calculateLimit() {
    const scrollZone = document.querySelector('.main-view__scroll-zone');
    if (!scrollZone) return 12;

    const style = window.getComputedStyle(scrollZone);
    const paddingLeft = parseFloat(style.paddingLeft) || 0;
    const paddingRight = parseFloat(style.paddingRight) || 0;
    const availableWidth = scrollZone.clientWidth - paddingLeft - paddingRight;

    let minCardW, gapCol, rows;

    if (window.innerWidth <= 640) {
        return 3 * 4;
    } else if (window.innerWidth <= 1100) {
        minCardW = 85;
        gapCol = 12;
        rows = 4;
    } else {
        minCardW = 95;
        gapCol = 16;
        const paddingTop = parseFloat(style.paddingTop) || 0;
        const paddingBottom = parseFloat(style.paddingBottom) || 0;
        const availableHeight = scrollZone.clientHeight - paddingTop - paddingBottom;
        const cardH = 180;
        const gapRow = 32;
        rows = Math.floor((availableHeight + gapRow) / (cardH + gapRow));
        rows = Math.max(rows, 1);
    }

    const cols = Math.floor((availableWidth + gapCol) / (minCardW + gapCol));
    const calculated = cols * rows;
    return Math.max(calculated, 4);
}

function initials(first, last) {
    return ((first?.[0] ?? '') + (last?.[0] ?? '')).toUpperCase() || '?';
}

function setActiveFilter(q) {
    document.querySelectorAll('.filter-tag').forEach(t => {
        t.classList.toggle('active', t.dataset.query === q);
    });
}

function showSkeletons() {
    if (!isSearchPage || !$grid) return;
    if ($empty) $empty.style.display = 'none';
    $grid.innerHTML = Array(currentLimit).fill('').map(() => `
        <div class="av av--skeleton">
            <div class="av__circle sk--circle"></div>
            <div class="sk sk--name"></div>
            <div class="sk sk--job"></div>
        </div>
    `).join('');
    if (!headerShown && $header) $header.style.display = 'none';
}

function renderEmpty(q) {
    if (!isSearchPage) return;
    if ($header) $header.style.display = 'flex';
    if ($queryEl) $queryEl.textContent = q || trans('generic.search');
    if ($meta) $meta.textContent = trans('generic.results', {count: 0, plural: ''});
    if ($empty) $empty.style.display = 'flex';
    if ($grid) $grid.innerHTML = '';
    headerShown = true;
    if ($emptyText) $emptyText.textContent = trans('search.no_results', {query: q});
    if ($empty) gsap.from($empty, { opacity: 0, y: 10, duration: .35, ease: 'power2.out' });
}

let masterIntroComplete = false;
window.startCardEntrance = () => {
    masterIntroComplete = true;
    animateCards();
};

function animateCards() {
    const items = document.querySelectorAll('.av');
    if (items.length > 0) {
        gsap.to(items, { y: 0, opacity: 1, scale: 1, duration: 0.5, stagger: { each: 0.04, from: 'start' }, ease: 'back.out(1.6)' });
    }
}

function renderCards(data) {
    if (!isSearchPage) return;
    if ($empty) $empty.style.display = 'none';
    if ($header) $header.style.display = 'flex';
    headerShown = true;

    if ($queryEl) $queryEl.textContent = query === '*' ? trans('generic.all_freelances') : query;
    if ($meta) $meta.textContent = trans('search.total_count', { count: totalCount, plural: totalCount > 1 ? 's' : '', page: page, total: totalPages });

    if ($grid) {
        $grid.innerHTML = data.map((f) => {
            const name = [f.firstName, f.lastName].filter(Boolean).join(' ') || trans('generic.anonymous');
            const isBookmarked = selectedFreelanceIds.has(f.id);
            return `
            <article class="av" data-id="${f.id}">
                <div class="av__circle">
                    ${initials(f.firstName, f.lastName)}
                    ${isBookmarked ? `<div class="av__bookmark"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg></div>` : ''}
                </div>
                <p class="av__name">${name}</p>
                <p class="av__job">${f.jobTitle ?? trans('generic.freelance')}</p>
            </article>`;
        }).join('');

        gsap.set($grid.querySelectorAll('.av'), { opacity: 0, y: 24, scale: 0.85 });
        if (!initialLoad || masterIntroComplete) animateCards();

        $grid.querySelectorAll('.av').forEach(a => {
            a.addEventListener('click', () => openDrawer(parseInt(a.dataset.id, 10)));
        });
    }
}

function renderPagination() {
    if (!isSearchPage || !$pagination) return;
    $pagination.innerHTML = '';
    if (totalPages <= 1) return;

    const makeBtn = (label, p, active, disabled = false) => {
        const b = document.createElement('button');
        b.className = 'pag-btn' + (active ? ' pag-btn--active' : '');
        b.textContent = label;
        b.disabled = disabled;
        if (!active && !disabled) b.addEventListener('click', () => search(query, p));
        return b;
    };

    const makeEllipsis = () => {
        const s = document.createElement('span');
        s.className = 'pag-ellipsis';
        s.textContent = '…';
        return s;
    };

    const visible = new Set([1, totalPages]);
    for (let p = Math.max(1, page - 1); p <= Math.min(totalPages, page + 1); p++) visible.add(p);
    const sorted = [...visible].sort((a, b) => a - b);

    if (page > 1) $pagination.appendChild(makeBtn('←', page - 1, false));

    let prev = 0;
    for (const p of sorted) {
        if (p - prev > 1) $pagination.appendChild(makeEllipsis());
        $pagination.appendChild(makeBtn(p, p, p === page));
        prev = p;
    }

    if (page < totalPages) $pagination.appendChild(makeBtn('→', page + 1, false));
}

function openDrawer(id) {
    if (!$drawerBody || !$drawer) return;
    $drawerBody.innerHTML = `
        <div class="drawer__sk drawer__sk--circle"></div>
        <div class="drawer__sk drawer__sk--h1"></div>
        <div class="drawer__sk drawer__sk--h2"></div>
        <div class="drawer__sk drawer__sk--line"></div>
        <div class="drawer__sk drawer__sk--sm"></div>
    `;
    $drawer.setAttribute('aria-hidden', 'false');

    if ($drawerOverlay) gsap.to($drawerOverlay, { opacity: 1, duration: 0.4 });
    if ($drawerPanel) gsap.to($drawerPanel, { x: 0, duration: 0.7, ease: 'expo.out' });

    fetch(`/api/freelances/${id}`)
        .then(r => r.json())
        .then(data => {
            const f = Array.isArray(data) ? data[0] : data;
            if (!f) { closeDrawer(); return; }

            const name = [f.firstName, f.lastName].filter(Boolean).join(' ') || trans('generic.anonymous');
            const href = f.linkedInUrl ? (f.linkedInUrl.startsWith('http') ? f.linkedInUrl : `https://${f.linkedInUrl}`) : null;
            const isComparing = compareList.has(f.id);
            const isSelected = selectedFreelanceIds.has(f.id);
            const userInitials = initials(f.firstName, f.lastName);

            gsap.to($drawerBody, {
                opacity: 0, duration: 0.15,
                onComplete: () => {
                    $drawerBody.innerHTML = `
                        <div class="drawer__initials">${userInitials}</div>
                        <p class="drawer__name">${name}</p>
                        <p class="drawer__job">${f.jobTitle ?? trans('generic.freelance')}</p>
                        <div class="drawer__skills">${(f.skills || []).map(s => `<span class="skill-tag">${s}</span>`).join('')}</div>
                        <div class="drawer__actions">
                            <button class="btn-action btn-action-primary">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M19 8v6M16 11h6"/></svg>
                                ${trans('drawer.recruit')}
                            </button>

                            ${href ? `
                            <a class="btn-action btn-action-secondary btn-action-linkedin" href="${href}" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
                                    <rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>
                                </svg>
                                LinkedIn
                            </a>` : ''}

                            <button class="btn-action btn-action-secondary btn-compare ${isComparing ? 'active' : ''}" id="btnCompare">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M16 3h5v5M4 20L21 3M21 16v5h-5M15 15l6 6M4 4l5 5"/></svg>
                                ${isComparing ? trans('drawer.compare_remove') : trans('drawer.compare_add')}
                            </button>

                            ${window.APP_USER.is_logged_in ? `
                                <button class="btn-action btn-action-secondary btn-selection ${isSelected ? 'active btn-action--remove' : ''}" id="btnAddToSelection">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
                                    ${isSelected ? trans('selection.remove') : trans('selection.add')}
                                </button>` : ''}
                            
                            <div class="compare-tip" id="compareTip">${trans('compare.limit_reached')}</div>
                        </div>
                        <div class="drawer__matching">
                            <p class="matching-label">${trans('drawer.matching_label')}</p>
                            <div class="matching-grid" id="matchingGrid"><div class="sk sk--name" style="grid-column: 1/-1; height: 40px;"></div></div>
                        </div>
                    `;
                    gsap.fromTo($drawerBody, { opacity: 0 }, { opacity: 1, duration: 0.2 });

                    document.getElementById('btnCompare').onclick = () => toggleCompare(f);

                    if (window.APP_USER.is_logged_in) {
                        const $selBtn = document.getElementById('btnAddToSelection');
                        $selBtn.onclick = async () => {
                            const endpoint = isSelected ? 'remove-from-default' : 'add-to-default';
                            const res = await fetch(`/api/selections/${endpoint}/${f.id}`, { method: 'POST' });
                            if (res.ok) {
                                if (isSelected) selectedFreelanceIds.delete(f.id);
                                else selectedFreelanceIds.add(f.id);
                                if (isSearchPage) search(query, page);
                                else renderProfileSelection();
                                openDrawer(f.id);
                            }
                        };
                    }

                    fetch(`/api/freelances/${f.id}/matching`)
                        .then(r => r.json())
                        .then(matches => {
                            const $mGrid = document.getElementById('matchingGrid');
                            if (!$mGrid) return;
                            $mGrid.innerHTML = (matches.error ? [] : matches).map(m => `
                                <div class="match-card" onclick="openDrawer(${m.id})">
                                    <div class="match-card__circle">${initials(m.firstName, m.lastName)}</div>
                                    <p class="match-card__name">${m.firstName} ${m.lastName}</p>
                                </div>
                            `).join('');
                        });
                }
            });
        })
        .catch(() => closeDrawer());
}

let compareTipTimer;
function showCompareTip() {
    const $tip = document.getElementById('compareTip');
    if (!$tip) return;
    $tip.classList.add('compare-tip--show');
    clearTimeout(compareTipTimer);
    compareTipTimer = setTimeout(() => $tip.classList.remove('compare-tip--show'), 2800);
}

function toggleCompare(freelance) {
    if (compareList.has(freelance.id)) {
        compareList.delete(freelance.id);
    } else {
        if (compareList.size >= 3) {
            showCompareTip();
            return;
        }
        compareList.set(freelance.id, { id: freelance.id, initials: initials(freelance.firstName, freelance.lastName) });
        closeDrawer(); 
    }
    updateCompareBar();
    const $btn = document.getElementById('btnCompare');
    if ($btn) {
        const active = compareList.has(freelance.id);
        $btn.classList.toggle('active', active);
        $btn.textContent = active ? trans('drawer.compare_remove') : trans('drawer.compare_add');
    }
}

function updateCompareBar() {
    const $bar = document.getElementById('compareBar');
    const $items = document.getElementById('compareItems');
    const $count = document.getElementById('compareCount');
    if (!$bar) return;
    if (compareList.size === 0) {
        $bar.classList.remove('compare-bar--show');
        return;
    }
    $bar.classList.add('compare-bar--show');
    if ($count) $count.textContent = compareList.size;
    if ($items) $items.innerHTML = Array.from(compareList.values()).map(f => `<div class="compare-item">${f.initials}</div>`).join('');
}

if (document.getElementById('btnClearCompare')) {
    document.getElementById('btnClearCompare').onclick = () => {
        compareList.clear();
        updateCompareBar();
        const $btn = document.getElementById('btnCompare');
        if ($btn) {
            $btn.classList.remove('active');
            $btn.textContent = trans('drawer.compare_add');
        }
    };
}

if (document.getElementById('btnLaunchCompare')) {
    document.getElementById('btnLaunchCompare').onclick = async () => {
        const $modal = document.getElementById('compareModal');
        const $grid = document.getElementById('compareGrid');
        if (!$modal || !$grid) return;
        $grid.innerHTML = `<p style="padding:40px;text-align:center;grid-column:1/-1;">${trans('compare.loading')}</p>`;
        $modal.setAttribute('aria-hidden', 'false');
        
        const ids = Array.from(compareList.keys());
        const results = await Promise.all(ids.map(id => fetch(`/api/freelances/${id}`).then(r => r.json())));
        const freelances = results.map(data => Array.isArray(data) ? data[0] : data);
        const allSkills = Array.from(new Set(freelances.flatMap(f => f.skills || []))).sort();
        
        $grid.innerHTML = freelances.map(f => {
            const hasSkill = (s) => (f.skills || []).includes(s);
            return `
                <div class="compare-card ${compareList.size > 1 ? 'compare-card--highlight' : ''}">
                    <div class="compare-card__header">
                        <div class="compare-card__circle">${initials(f.firstName, f.lastName)}</div>
                        <div><p class="compare-card__name">${f.firstName} ${f.lastName}</p><p class="compare-card__job">${f.jobTitle}</p></div>
                    </div>
                    <div class="compare-section">
                        <p class="compare-section-title">${trans('compare.section_skills')}</p>
                        <div class="skill-list">
                            ${allSkills.map(s => {
                                const present = hasSkill(s);
                                return `<div class="skill-item"><div class="skill-icon ${present ? 'skill-icon--has' : 'skill-icon--miss'}">${present ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><path d="M20 6L9 17l-5-5"/></svg>' : ''}</div><span class="${present ? 'skill-text' : 'skill-text--miss'}">${s}</span></div>`;
                            }).join('')}
                        </div>
                    </div>
                </div>`;
        }).join('');
    };
}

if (document.getElementById('compareModalClose')) document.getElementById('compareModalClose').onclick = () => document.getElementById('compareModal').setAttribute('aria-hidden', 'true');
if (document.getElementById('compareModalOverlay')) document.getElementById('compareModalOverlay').onclick = () => document.getElementById('compareModal').setAttribute('aria-hidden', 'true');

function closeDrawer() {
    if (!$drawer) return;
    const tl = gsap.timeline({ onComplete: () => { $drawer.setAttribute('aria-hidden', 'true'); } });
    if ($drawerPanel) tl.to($drawerPanel, { x: '110%', duration: 0.6, ease: 'expo.in' });
    if ($drawerOverlay) tl.to($drawerOverlay, { opacity: 0, duration: 0.4 }, '-=0.4');
}

if ($drawerClose) $drawerClose.addEventListener('click', closeDrawer);
if ($drawerOverlay) $drawerOverlay.addEventListener('click', closeDrawer);
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

let warnTimer;
function warnTooShort() {
    if (!$searchTip) return;
    $searchTip.textContent = trans('search.empty_query');
    $searchTip.classList.add('search-tip--show');
    clearTimeout(warnTimer);
    warnTimer = setTimeout(() => $searchTip.classList.remove('search-tip--show'), 2800);
}

if ($btn && $input) {
    $btn.addEventListener('click', () => {
        if ($input.value.trim().length > 0 && $input.value.trim().length < 3) { warnTooShort(); return; }
        setActiveFilter($input.value.trim());
        search($input.value.trim());
        if ($suggestion) $suggestion.innerHTML = '';
        currentSuggestion = '';
    });

    $input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            if ($input.value.trim().length > 0 && $input.value.trim().length < 3) { warnTooShort(); return; }
            setActiveFilter($input.value.trim());
            search($input.value.trim());
            if ($suggestion) $suggestion.innerHTML = '';
            currentSuggestion = '';
        }
        if (e.key === 'Tab' && currentSuggestion) {
            e.preventDefault();
            $input.value = currentSuggestion;
            if ($suggestion) $suggestion.innerHTML = '';
            currentSuggestion = '';
        }
    });

    $input.addEventListener('input', () => {
        const val = $input.value;
        if ($suggestion) $suggestion.innerHTML = '';
        currentSuggestion = '';
        clearTimeout(timer);
        timer = setTimeout(async () => {
            if (val.length >= 3) {
                try {
                    const res = await fetch(`/api/freelances/autocomplete?q=${encodeURIComponent(val)}`);
                    const data = await res.json();
                    if (data.suggestion && data.suggestion.toLowerCase().startsWith(val.toLowerCase())) {
                        currentSuggestion = data.suggestion;
                        if ($suggestion) $suggestion.innerHTML = `<span style="opacity:0">${val}</span>${data.suggestion.substring(val.length)}`;
                    }
                } catch (e) { console.error('Autocomplete error:', e); }
            }
        }, 100);
    });
}

async function search(q = '*', p = 1) {
    if (!isSearchPage) return;
    query = q || '*';
    page = p;

    showSkeletons();
    initialLoad = false;

    try {
        const skillsArr = Array.from(selectedSkills);
        const params = new URLSearchParams({ query, page, limit: currentLimit, ...(sortOrder && { sort: sortOrder }) });
        skillsArr.forEach(s => params.append('skills[]', s));
        
        const res = await fetch(`/api/freelances?${params}`);
        if (!res.ok) throw new Error(res.status);

        totalCount = parseInt(res.headers.get('X-Total-Count') ?? '0', 10);
        totalPages = parseInt(res.headers.get('X-Total-Pages') ?? '1', 10);

        const data = await res.json();
        cachedData = data.results;
        lastFacets = data.aggregations || {};
        renderSkillsList(document.getElementById('skillSearch')?.value || '');

        if (!data.results || !data.results.length) {
            renderEmpty(query);
        } else {
            renderCards(data.results);
        }
        renderPagination();

        const scrollZone = document.querySelector('.main-view__scroll-zone');
        if (scrollZone && !initialLoad) scrollZone.scrollTo({ top: 0, behavior: 'smooth' });

    } catch (e) {
        console.error(e);
        if ($grid) $grid.innerHTML = `<p style="padding:60px;text-align:center;grid-column:1/-1;font-family:var(--font-main);font-weight:600;color:var(--brand-primary)">${trans('search.error')}</p>`;
    }
}

document.querySelectorAll('.filter-tag').forEach(t => {
    t.addEventListener('click', () => {
        setActiveFilter(t.dataset.query);
        if ($input) $input.value = t.dataset.query;
        search(t.dataset.query);
    });
});

if ($sort) {
    $sort.addEventListener('change', () => {
        sortOrder = $sort.value;
        search(query, 1);
    });
}

let resizeTimer;
window.addEventListener('resize', () => {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(() => {
        const newLimit = calculateLimit();
        if (newLimit !== currentLimit) {
            currentLimit = newLimit;
            search(query, 1);
        }
    }, 250);
});

if (isSearchPage) {
    currentLimit = calculateLimit();
    fetchSkills();
    fetchSelectedIds().then(() => { search('*'); });
} else {
    fetchSelectedIds();
}
