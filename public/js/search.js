const LIMIT = 12;
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
    $grid.innerHTML = Array(LIMIT).fill('').map(() => `
        <div class="card card--skeleton" style="opacity: 1;">
            <div class="sk sk--xs"></div>
            <div class="sk sk--circle"></div>
            <div class="sk sk--lg"></div>
            <div class="sk sk--md"></div>
            <div class="sk sk--arrow"></div>
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
    if ($header) $header.style.display = 'none';
    if ($empty) $empty.style.display = 'flex';
    if ($grid) $grid.innerHTML = '';
    headerShown = false;
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
    const cards = document.querySelectorAll('.card');
    if (cards.length > 0) {
        gsap.to(cards, {
            y: 0,
            opacity: 1,
            scale: 1,
            duration: 1.2,
            stagger: {
                each: 0.08,
                grid: 'auto',
                from: 'start'
            },
            ease: 'back.out(1.2)'
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
        $grid.innerHTML = data.map((f, i) => `
            <article class="card" data-id="${f.id}" style="opacity: 0; transform: translateY(40px) scale(0.95);">
                <div class="card__initials">${initials(f.firstName, f.lastName)}</div>
                <p class="card__num">ID #${f.id}</p>
                <p class="card__name">${[f.firstName, f.lastName].filter(Boolean).join(' ') || 'Anonyme'}</p>
                <p class="card__job">${f.jobTitle ?? 'Freelance'}</p>
                <div class="card__arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </div>
            </article>
        `).join('');

        if (!initialLoad || masterIntroComplete) {
            animateCards();
        }

        $grid.querySelectorAll('.card').forEach(card => {
            card.addEventListener('click', () => openDrawer(parseInt(card.dataset.id, 10)));
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
        b.disabled = active || disabled;
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
        <div class="drawer__initials sk"></div>
        <div class="drawer__name sk sk--lg"></div>
        <div class="drawer__job sk sk--md"></div>
        <div class="drawer__content sk sk--lg"></div>
    `;
    $drawer.setAttribute('aria-hidden', 'false');

    if ($drawerOverlay) gsap.to($drawerOverlay, {
        opacity: 1,
        duration: 0.5
    });
    if ($drawerPanel) gsap.to($drawerPanel, {
        x: 0,
        duration: 0.8,
        ease: 'expo.out'
    });

    fetch(`/api/freelances/${id}`)
        .then(r => r.json())
        .then(data => {
            const f = Array.isArray(data) ? data[0] : data;
            if (!f) {
                closeDrawer();
                return;
            }
            const name = [f.firstName, f.lastName].filter(Boolean).join(' ') || 'Anonyme';
            $drawerBody.innerHTML = `
                <div class="drawer__initials">${initials(f.firstName, f.lastName)}</div>
                <p class="drawer__name">${name}</p>
                <p class="drawer__job">${f.jobTitle ?? 'Freelance'}</p>
                
                <div class="drawer__content">
                    <p>Expert passionné avec plus de 5 ans d'expérience dans l'écosystème tech. Spécialisé dans la résolution de problèmes complexes et l'optimisation de performances, ${f.firstName || 'ce profil'} accompagne les entreprises dans leur transformation digitale avec une approche orientée résultats et qualité logicielle.</p>
                    <br>
                    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed non risus. Suspendisse lectus tortor, dignissim sit amet, adipiscing nec, ultricies sed, dolor. Cras elementum ultrices diam. Maecenas ligula massa, varius a, semper congue, euismod non, mi.</p>
                </div>

                <div class="drawer__actions">
                    ${f.linkedInUrl ? `
                    <a class="drawer__link" href="${f.linkedInUrl.startsWith('http') ? f.linkedInUrl : `https://${f.linkedInUrl}`}" target="_blank" rel="noopener noreferrer">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" style="width:20px;height:20px;">
                            <path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6z"/>
                            <rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>
                        </svg>
                        PROFIL LINKEDIN
                    </a>` : ''}
                    <button class="btn-recruit">Recruter ce talent</button>
                </div>
            `;

            gsap.from('.drawer__panel > *', {
                y: 30,
                opacity: 0,
                stagger: 0.1,
                duration: 0.8,
                ease: 'expo.out'
            });
        })
        .catch(() => closeDrawer());
}

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
        const params = new URLSearchParams({ query, page, limit: LIMIT, ...(sortOrder && { sort: sortOrder }) });
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

if ($btn && $input) {
    $btn.addEventListener('click', () => {
        setActiveFilter($input.value.trim());
        search($input.value.trim());
    });

    $input.addEventListener('keydown', e => {
        if (e.key === 'Enter') {
            setActiveFilter($input.value.trim());
            search($input.value.trim());
        }
    });

    $input.addEventListener('input', () => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            if ($input.value.length >= 2 || $input.value === '') {
                setActiveFilter($input.value.trim());
                search($input.value.trim());
            }
        }, 380);
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

search('*');
