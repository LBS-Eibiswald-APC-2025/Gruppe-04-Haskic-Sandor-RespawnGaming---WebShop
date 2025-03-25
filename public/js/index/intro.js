$(document).ready(function() {
    const allGames = window.allgames || [];
    const suggestedGamesContainer = $('.row');

    // Hilfsfunktion, um die Basis-URL aus dem Layout zu extrahieren
    function getBaseUrl() {
        // Wir können die Basis-URL aus einem meta-Tag oder vom ersten Link extrahieren
        // Alternativ können wir window.location.origin + "/" verwenden
        return window.location.origin + "/";
    }

    // IDs der Spiele im Carousel erhalten
    function getCarouselGameIds() {
        return allGames.map(game => game.id);
    }

    // AJAX-Anfrage um weitere Spiele zu laden
    $.ajax({
        url: getBaseUrl() + 'games/getRandomGames',
        type: 'POST',
        data: {
            exclude_ids: JSON.stringify(getCarouselGameIds()) // Übergebe die IDs zum Ausschließen
        },
        dataType: 'json',
        success: function(response) {
            if (response && response.success && response.games && response.games.length > 0) {
                renderSuggestedGames(response.games);
            } else {
                // Fallback: Bei fehlgeschlagener Antwort zeige gemischte Carousel-Spiele
                const shuffledGames = shuffleArray(allGames);
                const suggestedGames = shuffledGames.slice(0, 6); // Nimm die ersten 4
                renderSuggestedGames(suggestedGames);
            }
        },
        error: function(xhr, status, error) {
            console.error("AJAX-Fehler:", error);
            // Fallback: Bei Fehler zeige gemischte Carousel-Spiele
            const shuffledGames = shuffleArray(allGames);
            const suggestedGames = shuffledGames.slice(0, 4); // Nimm die ersten 4
            renderSuggestedGames(suggestedGames);
        }
    });

    // Funktion zum Zufälligen Mischen eines Arrays (Fisher-Yates Shuffle)
    function shuffleArray(array) {
        const newArray = [...array];
        for (let i = newArray.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [newArray[i], newArray[j]] = [newArray[j], newArray[i]];
        }
        return newArray;
    }

    // Rendere die vorgeschlagenen Spiele
    function renderSuggestedGames(games) {
        suggestedGamesContainer.empty();

        games.forEach(game => {
            const gameUrl = game.url || game.game_url || getBaseUrl() + 'games/show/' + game.id;
            const gameCard = `
                <div class="col-md-3 col-sm-6 mb-4">
                    <div class="game-card">
                        <img src="${game.image}" alt="${game.title}" class="card-img-top">
                        <div class="card-body">
                            <h5 class="card-title">${game.title}</h5>
                            <p class="card-text">${game.description.length > 100 ? game.description.slice(0, 100) + '...' : game.description}</p>
                            <p class="card-text">${game.price}</p>
                            <a href="${gameUrl}" class="btn btn-outline-primary">Details</a>
                        </div>
                    </div>
                </div>
            `;

            suggestedGamesContainer.append(gameCard);
        });
    }
});