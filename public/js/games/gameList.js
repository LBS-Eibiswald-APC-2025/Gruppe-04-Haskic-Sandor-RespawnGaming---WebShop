document.addEventListener('DOMContentLoaded', () => {
    // Detail-Panel-Elemente
    const detailPanel       = document.getElementById('detailPanel');
    const detailHeader      = document.getElementById('detailHeader');
    const detailScreenshots = document.getElementById('detailScreenshots');
    const detailTags        = document.getElementById('detailTags');

    // Alle Spiele-Daten aus PHP in window
    // (kannst du per window.gamesData = ... in der index.php übergeben,
    // aber wir nehmen hier for simplicity an, dass es inlined in code
    // ODER du kannst es in der PHP generieren.
    // Machbar: window.gamesData = <?php echo json_encode($games) ?> usw.

    // Falls du es direkt aus 'data-...' Attributen holen willst,
    // kannst du es so weiterverarbeiten.
    // Im Code hier beziehen wir uns auf den HTML code -> 'data-index'.

    const listItems = document.querySelectorAll('.games-item');

    // Hardcodiertes Array, falls du willst:
    // const gamesData = [
    //   ...
    // ];

    // Wir generieren hier lieber aus dem serverseitigen PHP ->
    // Siehe index.php, wo du "json_encode($games)" ausgeben könntest
    // und hier via window.gamesData ausliest:
    const gamesData = window.gamesData || [];

    listItems.forEach(item => {
        item.addEventListener('mouseover', () => {
            const idx  = parseInt(item.getAttribute('data-index'), 10);
            const game = gamesData[idx];
            if (!game) return;

            // Title & Price
            detailHeader.textContent = game.title + ' – ' + game.price;

            // Screenshots
            detailScreenshots.innerHTML = '';
            if (Array.isArray(game.screenshots)) {
                game.screenshots.forEach(src => {
                    const img = document.createElement('img');
                    img.src = src;
                    detailScreenshots.appendChild(img);
                });
            }

            // Tags
            detailTags.innerHTML = '';
            if (Array.isArray(game.tags)) {
                game.tags.forEach(tag => {
                    const span = document.createElement('span');
                    span.classList.add('tag');
                    span.textContent = tag;
                    detailTags.appendChild(span);
                });
            }
        });
    });
});
