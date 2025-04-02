document.addEventListener('DOMContentLoaded', () => {
    const featuredGames = window.allgames || [];
    const slideEl = document.getElementById('carouselSlide');
    const carouselImg = document.getElementById('carouselImg');
    const carouselTitle = document.getElementById('carouselTitle');
    const carouselDesc = document.getElementById('carouselDescription');
    const carouselPrice = document.getElementById('carouselPrice');
    const carouselPrev = document.getElementById('carouselPrev');
    const carouselNext = document.getElementById('carouselNext');
    const carouselIndicators = document.getElementById('carouselIndicators');
    const carouselThumbnails = document.getElementById('carouselThumbnails');

    let currentIndex = 0;
    let autoSwitchInterval;
    let isAnimating = false;

    function createIndicators() {
        carouselIndicators.innerHTML = '';
        featuredGames.forEach((game, index) => {
            const dot = document.createElement('div');
            dot.classList.add('indicator');
            if (index === currentIndex) dot.classList.add('active');

            dot.addEventListener('click', (e) => {
                e.stopPropagation();
                if (isAnimating || index === currentIndex) return;
                const direction = (index > currentIndex) ? 'right' : 'left';
                goToSlide(index, direction);
                startAutoSwitch();
            });
            carouselIndicators.appendChild(dot);
        });
    }

    function updateCarousel() {
        const game = featuredGames[currentIndex];
        if (!game) return;

        carouselImg.src = game.image;
        carouselImg.alt = game.title;
        carouselTitle.textContent = game.title;
        carouselDesc.textContent = 'Bald Verfügbar';
        carouselPrice.textContent = game.price;

        // Aktiviere den richtigen Indikator
        const dots = carouselIndicators.querySelectorAll('.indicator');
        dots.forEach((dot, idx) => {
            dot.classList.toggle('active', idx === currentIndex);
        });

        // Klick-Logik: Nutze entweder game.url oder game.game_url
        const redirectURL = game.url || game.game_url;
        if (redirectURL) {
            slideEl.style.cursor = 'pointer';
            slideEl.onclick = () => {
                window.location.href = redirectURL;
            };
        } else {
            slideEl.style.cursor = 'default';
            slideEl.onclick = null;
        }

        updateThumbnails(game);
    }

    function updateThumbnails(game) {
        carouselThumbnails.innerHTML = '';
        if (game.tinyImageArray) {
            let thumbUrls = game.tinyImageArray.split("; ");
            thumbUrls.forEach((url) => {
                const img = document.createElement('img');
                img.src = url;
                img.alt = game.title + ' Screenshot';
                carouselThumbnails.appendChild(img);
            });
        }
    }

    function goToSlide(newIndex, direction = 'right') {
        if (isAnimating) return;
        isAnimating = true;


        slideEl.classList.remove('slide-in-right', 'slide-out-left', 'slide-in-left', 'slide-out-right');

        if (direction === 'right') {
            slideEl.classList.add('slide-out-left');
            setTimeout(() => {
                currentIndex = newIndex;
                updateCarousel();
                slideEl.classList.remove('slide-out-left');
                slideEl.classList.add('slide-in-right');
                setTimeout(() => {
                    slideEl.classList.remove('slide-in-right');

                    isAnimating = false;
                }, 700);
            }, 700);
        } else {
            slideEl.classList.add('slide-out-right');
            setTimeout(() => {
                currentIndex = newIndex;
                updateCarousel();
                slideEl.classList.remove('slide-out-right');
                slideEl.classList.add('slide-in-left');
                setTimeout(() => {
                    slideEl.classList.remove('slide-in-left');

                    isAnimating = false;
                }, 700);
            }, 700);
        }
    }

    function startAutoSwitch() {
        if (autoSwitchInterval) clearInterval(autoSwitchInterval);
        autoSwitchInterval = setInterval(() => {
            goToSlide((currentIndex + 1) % featuredGames.length, 'right');
        }, 7000);
    }

    carouselPrev.addEventListener('click', (e) => {
        e.stopPropagation();
        if (isAnimating) return;
        const newIndex = (currentIndex - 1 + featuredGames.length) % featuredGames.length;
        goToSlide(newIndex, 'left');
        startAutoSwitch();
    });

    carouselNext.addEventListener('click', (e) => {
        e.stopPropagation();
        if (isAnimating) return;
        const newIndex = (currentIndex + 1) % featuredGames.length;
        goToSlide(newIndex, 'right');
        startAutoSwitch();
    });

    if (featuredGames.length > 0) {
        createIndicators();
        updateCarousel();
        startAutoSwitch();
    }
});