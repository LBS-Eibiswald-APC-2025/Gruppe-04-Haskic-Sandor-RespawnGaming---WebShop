document.addEventListener('DOMContentLoaded', () => {
    const featuredGames = window.featuredGames || [];

    // Selektoren
    const slideEl            = document.getElementById('carouselSlide');
    const carouselImg        = document.getElementById('carouselImg');
    const carouselTitle      = document.getElementById('carouselTitle');
    const carouselDesc       = document.getElementById('carouselDescription');
    const carouselPrice      = document.getElementById('carouselPrice');
    const carouselPrev       = document.getElementById('carouselPrev');
    const carouselNext       = document.getElementById('carouselNext');
    const carouselIndicators = document.getElementById('carouselIndicators');

    let currentIndex = 0;
    let autoSwitchInterval;

    // 1) Indikatoren erzeugen
    function createIndicators() {
        carouselIndicators.innerHTML = '';
        featuredGames.forEach((game, index) => {
            const dot = document.createElement('div');
            dot.classList.add('indicator');
            if (index === currentIndex) dot.classList.add('active');

            dot.addEventListener('click', () => {
                const direction = (index > currentIndex) ? 'right' : 'left';
                goToSlide(index, direction);
                startAutoSwitch();
            });
            carouselIndicators.appendChild(dot);
        });
    }

    // 2) Carousel aktualisieren
    function updateCarousel() {
        const game = featuredGames[currentIndex];
        if (!game) return;

        // Bild & Infos
        carouselImg.src = game.image;
        carouselImg.alt = game.title;
        carouselTitle.textContent = game.title;
        carouselDesc.textContent = game.description;
        carouselPrice.textContent = game.price;

        // Indicator aktualisieren
        const dots = carouselIndicators.querySelectorAll('.indicator');
        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === currentIndex);
        });
    }

    // 3) Weiche Animation
    function goToSlide(newIndex, direction = 'right') {
        // Entfernen aller Animationsklassen
        slideEl.classList.remove(
            'slide-in-right',
            'slide-out-left',
            'slide-in-left',
            'slide-out-right'
        );

        // Ausblend-Animation abhängig von Richtung
        if (direction === 'right') {
            slideEl.classList.add('slide-out-left');
            setTimeout(() => {
                currentIndex = newIndex;
                updateCarousel();
                slideEl.classList.remove('slide-out-left');
                slideEl.classList.add('slide-in-right');
            }, 700); // Dieser Wert sollte mit der SCSS-Animation übereinstimmen (0.7s -> 700ms)
        } else {
            slideEl.classList.add('slide-out-right');
            setTimeout(() => {
                currentIndex = newIndex;
                updateCarousel();
                slideEl.classList.remove('slide-out-right');
                slideEl.classList.add('slide-in-left');
            }, 700);
        }
    }

    // 4) Automatischer Wechsel
    function startAutoSwitch() {
        if (autoSwitchInterval) clearInterval(autoSwitchInterval);
        autoSwitchInterval = setInterval(() => {
            goToSlide((currentIndex + 1) % featuredGames.length, 'right');
        }, 7000); // Alle 7s ein Wechsel
    }

    // 5) Button-Events
    carouselPrev.addEventListener('click', () => {
        const newIndex = (currentIndex - 1 + featuredGames.length) % featuredGames.length;
        goToSlide(newIndex, 'left');
        startAutoSwitch();
    });

    carouselNext.addEventListener('click', () => {
        const newIndex = (currentIndex + 1) % featuredGames.length;
        goToSlide(newIndex, 'right');
        startAutoSwitch();
    });

    // 6) Init
    if (featuredGames.length > 0) {
        createIndicators();
        updateCarousel();
        startAutoSwitch();
    }
});
