document.addEventListener('DOMContentLoaded', () => {
    const ratingForm = document.getElementById('ratingForm');
    if (!ratingForm) return;

    const ratingButtons = ratingForm.querySelectorAll('.btn-rating');
    ratingButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const gameId = ratingForm.querySelector('[name="game_id"]').value;
            const rating = button.dataset.rating;

            // Debug-Ausgabe
            console.log('Sende Bewertung:', {
                gameId: gameId,
                rating: rating
            });

            try {
                const response = await fetch('/games/rate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        game_id: gameId,
                        rating: rating
                    })
                });

                const result = await response.json();
                console.log('Server-Antwort:', result); // Debug-Ausgabe

                if (result.success) {
                    updateRatingDisplay(result.positive_percent, result.negative_percent);
                    showFeedback('Bewertung erfolgreich gespeichert', 'success');
                } else {
                    throw new Error(result.message || 'Bewertung fehlgeschlagen');
                }
            } catch (error) {
                console.error('Fehler:', error); // Debug-Ausgabe
                showFeedback(error.message || 'Fehler beim Speichern der Bewertung', 'error');
            }
        });
    });
});

function updateRatingDisplay(positive, negative) {
    const positiveBar = document.querySelector('.rating-bar.positive');
    const negativeBar = document.querySelector('.rating-bar.negative');

    if (positiveBar && negativeBar) {
        positiveBar.style.width = `${positive}%`;
        negativeBar.style.width = `${negative}%`;
        positiveBar.textContent = `${positive}% Positiv`;
        negativeBar.textContent = `${negative}% Negativ`;

        // Debug-Ausgabe
        console.log('Aktualisiere Anzeige:', {
            positive: positive,
            negative: negative
        });
    }
}

function showFeedback(message, type) {
    console.log('Feedback:', message, type); // Debug-Ausgabe
    if (type === 'success') {
        swal('Erfolg', message, 'success');
    } else {
        swal('Fehler', message, 'error');
    }
}