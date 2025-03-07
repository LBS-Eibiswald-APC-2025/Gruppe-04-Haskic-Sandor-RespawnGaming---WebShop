document.addEventListener('DOMContentLoaded', () => {
    // Element-Referenzen im Detail-Panel
    const detailHeader      = document.getElementById('detailHeader');
    const detailScreenshots = document.getElementById('detailScreenshots');
    const detailTags        = document.getElementById('detailTags');
    const detailRating      = document.getElementById('detailRating');

    // Container der Spieleliste
    const gamesListEl = document.getElementById('gamesListItems');
    // Alle Spiele-Items
    const listItems = document.querySelectorAll('.game-item');
    // Spiele-Daten aus PHP (als JSON übergeben)
    const gamesData = window.gamesData || [];

    // Filter-Logik: Beim Klicken auf einen Navigationslink wird gefiltert
    document.querySelectorAll('.games-nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            // Entferne active-Link von allen
            document.querySelectorAll('.games-nav a').forEach(el => el.classList.remove('active-link'));
            link.classList.add('active-link');
            const filter = link.getAttribute('data-filter');

            // Filtere die Liste: Zeige nur Spiele, deren 'category' zum Filter passt
            listItems.forEach(item => {
                const category = item.getAttribute('data-category');
                if (filter === category) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Hover-Logik: Beim Überfahren eines Spiele-Items Detail-Panel aktualisieren
    listItems.forEach(item => {
        item.addEventListener('mouseover', () => {
            const gameId = parseInt(item.getAttribute('data-id'), 10);
            // Finde das passende Spielobjekt
            const game = gamesData.find(g => parseInt(g.id, 10) === gameId);
            if (!game) return;

            // Titel & Preis
            detailHeader.textContent = `${game.title} – ${game.price}`;

            // Mini-Screenshot: Zeige nur das erste Bild
            detailScreenshots.innerHTML = '';
            if (game.screenshots && Array.isArray(game.screenshots) && game.screenshots.length > 0) {
                const img = document.createElement('img');
                img.src = game.screenshots[0];
                img.alt = `${game.title} Screenshot`;
                img.classList.add('mini-screenshot');
                detailScreenshots.appendChild(img);
            } else {
                detailScreenshots.textContent = 'Keine Screenshots verfügbar';
            }

            // Tags: Alle Tags anzeigen
            detailTags.innerHTML = '';
            if (game.tags && Array.isArray(game.tags)) {
                game.tags.forEach(tag => {
                    const span = document.createElement('span');
                    span.classList.add('tag');
                    span.textContent = tag;
                    detailTags.appendChild(span);
                });
            }

            // Bewertung anzeigen
            detailRating.textContent = game.rating ? `Bewertung: ${game.rating}` : 'Keine Bewertung verfügbar';
        });
    });
});
