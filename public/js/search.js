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
const $results = document.getElementById('results');
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

function pad(n) {
    return String(n).padStart(2, '0');
}

function setActiveFilter(q) {
    document.querySelectorAll('.filter-tag').forEach(t => {
        t.classList.toggle('active', t.dataset.query === q);
    });
}

function showSkeletons() {
    if (!$grid) return;
    $empty.style.display = 'none';
    $grid.innerHTML = Array(currentLimit).fill('').map(() => `
        <div class="av av--skeleton">
            <div class="av__circle sk--circle"></div>
            <div class="sk sk--name"></div>
            <div class="sk sk--job"></div>
        </div>
    `).join('');
    if (!headerShown && $header) $header.style.display = 'none';
}

function applySort(data) {
    if (!sortOrder) return data;
    return [...data].sort((a, b) => {
        const na = `${a.firstName ?? ''} ${a.lastName ?? ''}`.trim().toLowerCase();
        const nb = `${b.firstName ?? ''} ${b.lastName ?? ''}`.trim().toLowerCase();
        return sortOrder === 'name_asc' ? na.localeCompare(nb) : nb.localeCompare(na);
    });
}

function renderEmpty(q) {
    if ($header) $header.style.display = 'flex';
    if ($queryEl) $queryEl.textContent = q || 'Recherche';
    if ($meta) $meta.textContent = `0 résultat`;
    if ($empty) $empty.style.display = 'flex';
    if ($grid) $grid.innerHTML = '';
    headerShown = true;
    if ($emptyText) $emptyText.textContent = `Aucun résultat pour « ${q} »`;
    if ($empty) gsap.from($empty, {
        opacity: 0,
        y: 10,
        duration: .35,
        ease: 'power2.out'
    });
}

let masterIntroComplete = false;

window.startCardEntrance = () => {
    masterIntroComplete = true;
    animateCards();
};

function animateCards() {
    const items = document.querySelectorAll('.av');
    if (items.length > 0) {
        gsap.to(items, {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 0.5,
            stagger: { each: 0.04, from: 'start' },
            ease: 'back.out(1.6)',
        });
    }
}

function renderCards(data) {
    if ($empty) $empty.style.display = 'none';
    if ($header) $header.style.display = 'flex';
    headerShown = true;

    if ($queryEl) $queryEl.textContent = query === '*' ? 'Tous les freelances' : query;
    if ($meta) $meta.textContent = `${totalCount} freelance${totalCount > 1 ? 's' : ''} · Page ${page}/${totalPages}`;

    if ($grid) {
        $grid.innerHTML = data.map((f) => {
            const name = [f.firstName, f.lastName].filter(Boolean).join(' ') || 'Anonyme';
            return `
            <article class="av" data-id="${f.id}">
                <div class="av__circle">${initials(f.firstName, f.lastName)}</div>
                <p class="av__name">${name}</p>
                <p class="av__job">${f.jobTitle ?? 'Freelance'}</p>
            </article>`;
        }).join('');

        gsap.set($grid.querySelectorAll('.av'), { opacity: 0, y: 24, scale: 0.85 });

        if (!initialLoad || masterIntroComplete) {
            animateCards();
        }

        $grid.querySelectorAll('.av').forEach(a => {
            a.addEventListener('click', () => openDrawer(parseInt(a.dataset.id, 10)));
        });
    }
}

function renderPagination() {
    if (!$pagination) return;
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
    if (!$drawerBody) return;
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

            const name = [f.firstName, f.lastName].filter(Boolean).join(' ') || 'Anonyme';
            const href = f.linkedInUrl
                ? (f.linkedInUrl.startsWith('http') ? f.linkedInUrl : `https://${f.linkedInUrl}`)
                : null;
            
            const isComparing = compareList.has(f.id);
            const userInitials = initials(f.firstName, f.lastName);

            gsap.to($drawerBody, {
                opacity: 0, duration: 0.15,
                onComplete: () => {
                    $drawerBody.innerHTML = `
                        <div class="drawer__initials">${userInitials}</div>
                        <p class="drawer__name">${name}</p>
                        <p class="drawer__job">${f.jobTitle ?? 'Freelance'}</p>
                        <div class="drawer__content">
                            <p>${f.bio || "Expert passionné avec plus de 5 ans d'expérience dans l'écosystème tech."}</p>
                        </div>
                        <div class="drawer__actions">
                            <div class="compare-tip" id="compareTip">Maximum 3 profils</div>
                            ${href ? `
                            <a class="drawer__link" href="${href}" target="_blank" rel="noopener noreferrer">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;">
                                    <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
                                    <rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>
                                </svg>
                                PROFIL LINKEDIN
                            </a>` : ''}
                            <button class="btn-recruit">Recruter ce talent</button>
                            <button class="btn-compare ${isComparing ? 'active' : ''}" id="btnCompare">${isComparing ? 'Retirer du comparateur' : 'Comparer ce profil'}</button>
                        </div>
                        <div class="drawer__matching">
                            <p class="matching-label">PROFILS SIMILAIRES</p>
                            <div class="matching-grid" id="matchingGrid">
                                <div class="sk sk--name" style="grid-column: 1/-1; height: 40px;"></div>
                            </div>
                        </div>
                    `;
                    gsap.fromTo($drawerBody,
                        { opacity: 0 },
                        { opacity: 1, duration: 0.2 }
                    );

                    document.getElementById('btnCompare').onclick = () => toggleCompare(f);

                    fetch(`/api/freelances/${f.id}/matching`)
                        .then(r => r.json())
                        .then(matches => {
                            const $mGrid = document.getElementById('matchingGrid');
                            if (!$mGrid) return;
                            $mGrid.innerHTML = matches.map(m => `
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
        compareList.set(freelance.id, {
            id: freelance.id,
            initials: initials(freelance.firstName, freelance.lastName)
        });
        closeDrawer(); 
    }
    updateCompareBar();
    const $btn = document.getElementById('btnCompare');
    if ($btn) {
        const active = compareList.has(freelance.id);
        $btn.classList.toggle('active', active);
        $btn.textContent = active ? 'Retirer du comparateur' : 'Comparer ce profil';
    }
}

function updateCompareBar() {
    const $bar = document.getElementById('compareBar');
    const $items = document.getElementById('compareItems');
    const $count = document.getElementById('compareCount');
    if (compareList.size === 0) {
        $bar.classList.remove('compare-bar--show');
        return;
    }
    $bar.classList.add('compare-bar--show');
    $count.textContent = compareList.size;
    $items.innerHTML = Array.from(compareList.values()).map(f => `
        <div class="compare-item">${f.initials}</div>
    `).join('');
}

document.getElementById('btnClearCompare').onclick = () => {
    compareList.clear();
    updateCompareBar();
    const $btn = document.getElementById('btnCompare');
    if ($btn) {
        $btn.classList.remove('active');
        $btn.textContent = 'Comparer ce profil';
    }
};

document.getElementById('btnLaunchCompare').onclick = async () => {
    const $modal = document.getElementById('compareModal');
    const $grid = document.getElementById('compareGrid');
    $grid.innerHTML = '<p style="padding:40px;text-align:center;grid-column:1/-1;">Chargement de l\'analyse comparative...</p>';
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
                    <div>
                        <p class="compare-card__name">${f.firstName} ${f.lastName}</p>
                        <p class="compare-card__job">${f.jobTitle}</p>
                    </div>
                </div>
                
                <div class="compare-section">
                    <p class="compare-section-title">Analyse des compétences</p>
                    <div class="skill-list">
                        ${allSkills.map(s => {
                            const present = hasSkill(s);
                            return `
                                <div class="skill-item">
                                    <div class="skill-icon ${present ? 'skill-icon--has' : 'skill-icon--miss'}">
                                        ${present ? '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><path d="M20 6L9 17l-5-5"/></svg>' : ''}
                                    </div>
                                    <span class="${present ? 'skill-text' : 'skill-text--miss'}">${s}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>

                <div class="compare-section">
                    <p class="compare-section-title">Bio & Parcours</p>
                    <p class="compare-card__bio">${f.bio || "Aucune description disponible."}</p>
                </div>
            </div>`;
    }).join('');
};

document.getElementById('compareModalClose').onclick = () => document.getElementById('compareModal').setAttribute('aria-hidden', 'true');
document.getElementById('compareModalOverlay').onclick = () => document.getElementById('compareModal').setAttribute('aria-hidden', 'true');

function closeDrawer() {
    const tl = gsap.timeline({
        onComplete: () => {
            $drawer.setAttribute('aria-hidden', 'true');
        }
    });

    if ($drawerPanel) tl.to($drawerPanel, {
        x: '110%',
        duration: 0.6,
        ease: 'expo.in'
    });
    if ($drawerOverlay) tl.to($drawerOverlay, {
        opacity: 0,
        duration: 0.4
    }, '-=0.4');
}

if ($drawerClose) $drawerClose.addEventListener('click', closeDrawer);
if ($drawerOverlay) $drawerOverlay.addEventListener('click', closeDrawer);
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') closeDrawer();
});

async function search(q = '*', p = 1) {
    query = q || '*';
    page = p;

    showSkeletons();
    initialLoad = false;

    try {
        const params = new URLSearchParams({ query, page, limit: currentLimit, ...(sortOrder && { sort: sortOrder }) });
        const res = await fetch(`/api/freelances?${params}`);
        if (!res.ok) throw new Error(res.status);

        totalCount = parseInt(res.headers.get('X-Total-Count') ?? '0', 10);
        totalPages = parseInt(res.headers.get('X-Total-Pages') ?? '1', 10);

        const data = await res.json();
        cachedData = data;

        if (!data.length) {
            renderEmpty(query);
        } else {
            renderCards(data);
        }
        renderPagination();

        const scrollZone = document.querySelector('.main-view__scroll-zone');
        if (scrollZone) scrollZone.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    } catch (e) {
        console.error(e);
        if ($grid) $grid.innerHTML = '<p style="padding:60px;text-align:center;grid-column:1/-1;font-family:var(--font-main);font-weight:600;color:var(--brand-primary)">Erreur de connexion au serveur.</p>';
    }
}

let warnTimer;
function warnTooShort() {
    if (!$searchTip) return;
    $searchTip.textContent = 'On recrute des freelances, pas des devinettes.';
    $searchTip.classList.add('search-tip--show');
    clearTimeout(warnTimer);
    warnTimer = setTimeout(() => $searchTip.classList.remove('search-tip--show'), 2800);
}

if ($btn && $input) {
    $btn.addEventListener('click', () => {
        if ($input.value.trim().length === 1) { warnTooShort(); return; }
        setActiveFilter($input.value.trim());
        search($input.value.trim());
        $suggestion.innerHTML = '';
        currentSuggestion = '';
    });

    $input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            if ($input.value.trim().length === 1) { warnTooShort(); return; }
            setActiveFilter($input.value.trim());
            search($input.value.trim());
            $suggestion.innerHTML = '';
            currentSuggestion = '';
        }

        if (e.key === 'Tab' && currentSuggestion) {
            e.preventDefault();
            $input.value = currentSuggestion;
            $suggestion.innerHTML = '';
            currentSuggestion = '';
        }
    });

    $input.addEventListener('input', () => {
        const val = $input.value;
        $suggestion.innerHTML = '';
        currentSuggestion = '';

        clearTimeout(timer);
        timer = setTimeout(async () => {
            if (val.length >= 2) {
                try {
                    const res = await fetch(`/api/freelances/autocomplete?q=${encodeURIComponent(val)}`);
                    const data = await res.json();
                    if (data.suggestion && data.suggestion.toLowerCase().startsWith(val.toLowerCase())) {
                        currentSuggestion = data.suggestion;
                        const typedPart = val;
                        const suggestionPart = data.suggestion.substring(val.length);
                        $suggestion.innerHTML = `<span style="opacity:0">${typedPart}</span>${suggestionPart}`;
                    }
                } catch (e) {
                    console.error('Autocomplete error:', e);
                }
            }
        }, 100);
    });
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

currentLimit = calculateLimit();
search('*');
