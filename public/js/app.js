document.addEventListener('DOMContentLoaded', () => {
    const master = gsap.timeline({
        defaults: {
            ease: 'expo.inOut'
        }
    });

    master.set('.app-shell', {
        opacity: 1
    });
    master.set('.sidebar', {
        opacity: 0
    });
    master.set('.main-view', {
        opacity: 1,
        scale: 0.98
    });
    master.set('.sidebar__inner > *', {
        opacity: 0,
        y: 15
    });
    master.set('.main-view__header, .main-view__scroll-zone, .main-view__footer', {
        opacity: 0,
        y: 16
    });

    master.fromTo('.intro-shape', {
        scale: 0,
        borderRadius: '60px'
    }, {
        scale: 1,
        duration: 0.8,
        ease: 'back.out(1.7)'
    })
    .to('.intro-shape', {
        width: 'calc(100vw - 24px)',
        height: 'calc(100vh - 24px)',
        borderRadius: '24px',
        duration: 1
    }, '-=0.1')
    .to('.intro-content', {
        opacity: 1,
        duration: 0.6,
        ease: 'power2.out'
    }, '-=0.6')
    .to('.intro-content', {
        opacity: 0,
        duration: 0.4,
        delay: 0.6
    })
    .to('.intro-shape', {
        x: () => window.innerWidth <= 1100 ? 0 : -(window.innerWidth / 2) + 190 + 12,
        y: () => window.innerWidth <= 1100 ? -(window.innerHeight / 2) + (document.querySelector('.sidebar')?.offsetHeight ?? 320) / 2 : 0,
        width: () => window.innerWidth <= 1100 ? '100vw' : '380px',
        height: () => window.innerWidth <= 1100 ? (document.querySelector('.sidebar')?.offsetHeight ?? 320) + 'px' : 'calc(100vh - 24px)',
        borderRadius: () => window.innerWidth <= 1100 ? '0px' : '24px',
        duration: 1.4,
        ease: 'expo.inOut'
    })
    .set('.sidebar', {
        opacity: 1
    })
    .to('.intro-overlay', {
        autoAlpha: 0,
        duration: 0.3,
        ease: 'power2.in'
    })
    .to('.sidebar__inner > *', {
        y: 0,
        opacity: 1,
        stagger: 0.12,
        duration: 1,
        ease: 'expo.out'
    }, '-=0.15')
    .to('.main-view', {
        scale: 1,
        duration: 1,
        ease: 'expo.out'
    }, '<')
    .to('.main-view__header, .main-view__scroll-zone, .main-view__footer', {
        opacity: 1,
        y: 0,
        stagger: 0.1,
        duration: 0.9,
        ease: 'expo.out'
    }, '<')
    .add(() => {
        if (window.startCardEntrance) {
            window.startCardEntrance();
        }
    }, '<+0.15');

    const track = document.getElementById('marquee-track');
    if (track) {
        gsap.to('.marquee-track', {
            xPercent: -50,
            ease: 'none',
            duration: 20,
            repeat: -1
        });
    }

    const toast = document.createElement('div');
    toast.className = 'jm-toast';
    document.body.appendChild(toast);
    let toastTimer;

    function showToast(msg) {
        clearTimeout(toastTimer);
        toast.textContent = msg;
        toast.classList.add('jm-toast--show');
        toastTimer = setTimeout(() => toast.classList.remove('jm-toast--show'), 2800);
    }

    const contextMenu = document.getElementById('contextMenu');

    function hideContextMenu() {
        gsap.to(contextMenu, {
            opacity: 0, scale: 0.95, duration: 0.2,
            onComplete: () => { contextMenu.style.display = 'none'; }
        });
    }

    window.addEventListener('contextmenu', (e) => {
        e.preventDefault();
        const menuWidth  = contextMenu.offsetWidth  || 220;
        const menuHeight = contextMenu.offsetHeight || 200;
        const posX = e.clientX + menuWidth  > window.innerWidth  ? e.clientX - menuWidth  : e.clientX;
        const posY = e.clientY + menuHeight > window.innerHeight ? e.clientY - menuHeight : e.clientY;
        contextMenu.style.left    = `${posX}px`;
        contextMenu.style.top     = `${posY}px`;
        contextMenu.style.display = 'block';
        gsap.fromTo(contextMenu, { opacity: 0, scale: 0.95 }, { opacity: 1, scale: 1, duration: 0.25, ease: 'power2.out' });
    });

    window.addEventListener('click', () => {
        if (contextMenu.style.display === 'block') hideContextMenu();
    });

    const actions = {
        'inspecteur': () => {
            fetch(location.href)
                .then(r => r.text())
                .then(html => {
                    const lines = html
                        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                        .split('\n')
                        .map((l, i) => `<span class="sv-ln">${String(i + 1).padStart(4, ' ')}</span>${l}`)
                        .join('\n');

                    const el = document.createElement('div');
                    el.className = 'source-viewer';
                    el.innerHTML = `
                        <div class="source-viewer__bar">
                            <span class="source-viewer__url">${location.href}</span>
                            <button class="source-viewer__close">Fermer <kbd>Esc</kbd></button>
                        </div>
                        <pre class="source-viewer__pre"><code>${lines}</code></pre>
                    `;
                    document.body.appendChild(el);
                    gsap.from(el, { opacity: 0, duration: 0.2, ease: 'power2.out' });

                    const close = () => {
                        gsap.to(el, { opacity: 0, duration: 0.15, onComplete: () => el.remove() });
                    };
                    el.querySelector('.source-viewer__close').addEventListener('click', close);
                    document.addEventListener('keydown', function esc(e) {
                        if (e.key === 'Escape') { close(); document.removeEventListener('keydown', esc); }
                    });
                });
        },
        'recruteur': () => {
            const subject = encodeURIComponent('Je veux recruter un Jean-Michel');
            const body    = encodeURIComponent('Bonjour Killian,\n\nJ\'ai trouvé votre plateforme et je cherche un freelance tech de qualité.\n\nPouvez-vous m\'aider ?\n\nCordialement');
            window.location.href = `mailto:killianherzer@gmail.com?subject=${subject}&body=${body}`;
        },
        'partageur': () => {
            const url = window.location.href;
            if (navigator.share) {
                navigator.share({ title: 'jean-michel.io', text: 'La communauté freelance tech', url });
            } else {
                navigator.clipboard.writeText(url).then(() => showToast('Lien copié dans le presse-papiers'));
            }
        },
        'quitter': () => {
            window.close();
            setTimeout(() => showToast('Jean-Michel refuse de fermer.'), 400);
        },
    };

    contextMenu.querySelectorAll('.context-menu__item[data-action]').forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            hideContextMenu();
            const fn = actions[item.dataset.action];
            if (fn) fn();
        });
    });
});
