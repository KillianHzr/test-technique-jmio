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
        x: () => -(window.innerWidth / 2) + 190 + 12,
        width: '380px',
        height: 'calc(100vh - 24px)',
        borderRadius: '24px',
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

    const contextMenu = document.getElementById('contextMenu');

    window.addEventListener('contextmenu', (e) => {
        e.preventDefault();

        const x = e.clientX;
        const y = e.clientY;

        const winWidth = window.innerWidth;
        const winHeight = window.innerHeight;
        const menuWidth = contextMenu.offsetWidth || 220;
        const menuHeight = contextMenu.offsetHeight || 180;

        const posX = x + menuWidth > winWidth ? x - menuWidth : x;
        const posY = y + menuHeight > winHeight ? y - menuHeight : y;

        contextMenu.style.left = `${posX}px`;
        contextMenu.style.top = `${posY}px`;
        contextMenu.style.display = 'block';

        gsap.fromTo(contextMenu, {
            opacity: 0,
            scale: 0.95
        }, {
            opacity: 1,
            scale: 1,
            duration: 0.3,
            ease: 'power2.out'
        });
    });

    window.addEventListener('click', () => {
        if (contextMenu.style.display === 'block') {
            gsap.to(contextMenu, {
                opacity: 0,
                scale: 0.95,
                duration: 0.2,
                onComplete: () => {
                    contextMenu.style.display = 'none';
                }
            });
        }
    });

    contextMenu.querySelectorAll('.context-menu__item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            console.log('Action selected:', item.textContent);
            gsap.to(contextMenu, {
                opacity: 0,
                duration: 0.2,
                onComplete: () => {
                    contextMenu.style.display = 'none';
                }
            });
        });
    });
});
