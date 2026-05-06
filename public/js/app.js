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
        opacity: 0,
        scale: 0.98
    });
    master.set('.sidebar__inner > *', {
        opacity: 0,
        y: 15
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
        width: '94vw',
        height: '94vh',
        borderRadius: '32px',
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
    .set('.intro-overlay', {
        display: 'none'
    })
    .to('.sidebar__inner > *', {
        y: 0,
        opacity: 1,
        stagger: 0.12,
        duration: 1,
        ease: 'expo.out'
    }, '-=0.2')
    .to('.main-view', {
        opacity: 1,
        scale: 1,
        duration: 1.2,
        ease: 'expo.out'
    }, '-=1')
    .add(() => {
        if (window.startCardEntrance) {
            window.startCardEntrance();
        }
    }, '-=0.8');

    const track = document.getElementById('marquee-track');
    if (track) {
        gsap.to('.marquee-track', {
            xPercent: -50,
            ease: 'none',
            duration: 20,
            repeat: -1
        });
    }
});
