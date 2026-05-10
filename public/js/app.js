document.addEventListener('DOMContentLoaded', () => {
    const navEntries = performance.getEntriesByType('navigation');
    const navType = navEntries.length > 0 ? navEntries[0].type : 'navigate';
    const isNavigation = (navType === 'navigate' || navType === 'back_forward') && sessionStorage.getItem('has_loaded_once');
    sessionStorage.setItem('has_loaded_once', 'true');

    const isProfilePage = !!document.querySelector('.profile-card');

    const getTargetProps = () => {
        const targetEl = document.querySelector('.error-card') || document.querySelector('.sidebar');
        if (!targetEl) return { x: 0, y: 0, width: 380, height: 500, borderRadius: '24px' };

        const rect = targetEl.getBoundingClientRect();
        const winW = window.innerWidth;
        const winH = window.innerHeight;
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;

        return {
            x: centerX - (winW / 2),
            y: centerY - (winH / 2),
            width: rect.width,
            height: rect.height,
            borderRadius: window.getComputedStyle(targetEl).borderRadius || '24px'
        };
    };

    const target = getTargetProps();
    const master = gsap.timeline({ defaults: { ease: 'expo.inOut' } });

    master.set('.app-shell', { opacity: 1 });
    master.set('.sidebar, .error-card, .profile-card', { opacity: 0 });
    master.set('.main-view', { opacity: 1, scale: 0.98 });
    master.set('.sidebar__inner, .error-card > *, .profile-card__header', { opacity: 0, y: 15 });
    master.set('.main-view__header, .main-view__scroll-zone, .main-view__footer, .profile-card__content', { opacity: 0, y: 16 });

    if (!isNavigation) {
        master.fromTo('.intro-shape', { scale: 0, borderRadius: '60px' }, { scale: 1, duration: 0.8, ease: 'back.out(1.7)' })
        .to('.intro-shape', { width: 'calc(100vw - 24px)', height: 'calc(100vh - 24px)', borderRadius: '24px', duration: 1 }, '-=0.1')
        .to('.intro-content', { opacity: 1, duration: 0.6, ease: 'power2.out' }, '-=0.6')
        .to('.intro-content', { opacity: 0, duration: 0.4, delay: 0.6 });

        if (isProfilePage) {
            master.to('.intro-shape', { scale: 0, opacity: 0, duration: 0.8, ease: 'expo.in' });
        } else {
            master.to('.intro-shape', { x: target.x, y: target.y, width: target.width, height: target.height, borderRadius: target.borderRadius, duration: 1.4 });
        }

        master.set('.sidebar, .error-card, .profile-card', { opacity: 1 })
        .to('.intro-overlay', { autoAlpha: 0, duration: 0.3, ease: 'power2.in' })
        .to('.sidebar__inner, .error-card > *, .profile-card__header', { y: 0, opacity: 1, stagger: 0.08, duration: 1, ease: 'expo.out' }, '-=0.15')
        .to('.main-view', { scale: 1, duration: 1, ease: 'expo.out' }, '<')
        .to('.main-view__header, .main-view__scroll-zone, .main-view__footer, .profile-card__content', { opacity: 1, y: 0, stagger: 0.1, duration: 0.9, ease: 'expo.out' }, '<')
        .add(() => { if (window.startCardEntrance) window.startCardEntrance(); }, '<+0.15');
    } else {
        master.set('.intro-overlay', { autoAlpha: 1 });
        master.set('.intro-shape', { x: 0, y: 0, width: 'calc(100vw - 24px)', height: 'calc(100vh - 24px)', borderRadius: '24px' });
        master.set('.intro-content', { opacity: 0 });

        if (isProfilePage) {
            master.to('.intro-shape', { scale: 0, opacity: 0, duration: 0.8, ease: 'expo.in' });
        } else {
            master.to('.intro-shape', { x: target.x, y: target.y, width: target.width, height: target.height, borderRadius: target.borderRadius, duration: 1.2 });
        }

        master.set('.sidebar, .error-card, .profile-card', { opacity: 1 })
        .to('.intro-overlay', { autoAlpha: 0, duration: 0.3 })
        .to('.sidebar__inner, .error-card > *, .profile-card__header', { y: 0, opacity: 1, stagger: 0.05, duration: 0.8, ease: 'expo.out' }, '-=0.2')
        .to('.main-view', { scale: 1, duration: 0.8, ease: 'expo.out' }, '<')
        .to('.main-view__header, .main-view__scroll-zone, .main-view__footer, .profile-card__content', { opacity: 1, y: 0, stagger: 0.05, duration: 0.7, ease: 'expo.out' }, '<')
        .add(() => { if (window.startCardEntrance) window.startCardEntrance(); }, '<+0.1');
    }

    document.addEventListener('click', (e) => {
        const link = e.target.closest('a');
        if (!link) return;
        
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:') || link.target === '_blank') return;
        if (link.hostname !== window.location.hostname) return;
        
        e.preventDefault();
        
        const tl = gsap.timeline({ onComplete: () => { window.location.href = href; } });

        tl.to('.sidebar__inner, .main-view, .error-card > *, .profile-card > *', { opacity: 0, duration: 0.4, ease: 'power2.inOut' })
        .set('.intro-overlay', { autoAlpha: 1 });

        if (isProfilePage) {
            tl.fromTo('.intro-shape', { scale: 0, opacity: 0, x: 0, y: 0 }, { scale: 1, opacity: 1, width: 'calc(100vw - 24px)', height: 'calc(100vh - 24px)', borderRadius: '24px', duration: 0.8, ease: 'expo.out' });
        } else {
            tl.fromTo('.intro-shape', { x: target.x, y: target.y, width: target.width, height: target.height, borderRadius: target.borderRadius }, { x: 0, y: 0, width: 'calc(100vw - 24px)', height: 'calc(100vh - 24px)', borderRadius: '24px', duration: 0.8, ease: 'expo.inOut' });
        }

        tl.fromTo('.intro-content', { opacity: 0, scale: 0.8 }, { opacity: 1, scale: 1, duration: 0.4, ease: 'back.out(1.7)' }, '-=0.3')
        .to('.intro-content', { opacity: 0, scale: 1.1, duration: 0.3, ease: 'power2.in' }, '+=0.2');
    });

    // MARQUEE
    const track = document.getElementById('marquee-track');
    if (track) {
        gsap.to(track, { xPercent: -25, ease: 'none', duration: 10, repeat: -1 });
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
    if (contextMenu) {
        gsap.set(contextMenu, { autoAlpha: 0, scale: 0.95 });
    }

    function hideContextMenu() {
        if (!contextMenu) return;
        gsap.to(contextMenu, {
            autoAlpha: 0, scale: 0.95, duration: 0.2
        });
    }

    window.addEventListener('contextmenu', (e) => {
        if (!contextMenu) return;
        e.preventDefault();
        const menuWidth  = contextMenu.offsetWidth  || 220;
        const menuHeight = contextMenu.offsetHeight || 200;
        const posX = e.clientX + menuWidth  > window.innerWidth  ? e.clientX - menuWidth  : e.clientX;
        const posY = e.clientY + menuHeight > window.innerHeight ? e.clientY - menuHeight : e.clientY;
        contextMenu.style.left = `${posX}px`;
        contextMenu.style.top = `${posY}px`;
        gsap.to(contextMenu, { autoAlpha: 1, scale: 1, duration: 0.25, ease: 'power2.out' });
    });

    window.addEventListener('click', () => { if (contextMenu && contextMenu.style.visibility !== 'hidden') hideContextMenu(); });

    const actions = {
        'inspecteur': () => {
            fetch(location.href).then(r => r.text()).then(html => {
                const lines = html.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').split('\n').map((l, i) => `<span class="sv-ln">${String(i + 1).padStart(4, ' ')}</span>${l}`).join('\n');
                const el = document.createElement('div');
                el.className = 'source-viewer';
                el.innerHTML = `<div class=\"source-viewer__bar\"><span class=\"source-viewer__url\">${location.href}</span><button class=\"source-viewer__close\">${trans('generic.close')} <kbd>Esc</kbd></button></div><pre class=\"source-viewer__pre\"><code>${lines}</code></pre>`;
                document.body.appendChild(el);
                gsap.from(el, { opacity: 0, duration: 0.2, ease: 'power2.out' });
                const close = () => { gsap.to(el, { opacity: 0, duration: 0.15, onComplete: () => el.remove() }); };
                el.querySelector('.source-viewer__close').addEventListener('click', close);
                document.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { close(); document.removeEventListener('keydown', esc); } });
            });
        },
        'recruteur': () => {
            const subject = encodeURIComponent(trans('email.subject'));
            const body = encodeURIComponent(trans('email.body'));
            window.location.href = `mailto:killianherzer@gmail.com?subject=${subject}&body=${body}`;
        },
        'partageur': () => {
            const url = window.location.href;
            if (navigator.share) { navigator.share({ title: 'jean-michel.io', text: trans('generic.meta_text'), url }); }
            else { navigator.clipboard.writeText(url).then(() => showToast(trans('toast.link_copied'))); }
        },
        'quitter': () => { window.close(); setTimeout(() => showToast(trans('toast.exit_refused')), 400); },
    };

    if (contextMenu) {
        contextMenu.querySelectorAll('.context-menu__item[data-action]').forEach(item => {
            item.addEventListener('click', (e) => { e.stopPropagation(); hideContextMenu(); const fn = actions[item.dataset.action]; if (fn) fn(); });
        });
    }
});
