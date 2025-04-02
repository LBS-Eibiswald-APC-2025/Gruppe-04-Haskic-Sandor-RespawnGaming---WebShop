class RGGlobalEventBus {
    constructor() {
        this.events = {};
    }

    on(eventName, callback) {
        if (!this.events[eventName]) {
            this.events[eventName] = [];
        }
        this.events[eventName].push(callback);
    }

    emit(eventName, data) {
        const listeners = this.events[eventName] || [];
        listeners.forEach(callback => callback(data));
    }
}

class RGFilter {
    constructor(config) {
        this.eventBus = config.eventBus;
        this.filterLinksSelector = config.filterLinksSelector;
        this.gameItemSelector = config.gameItemSelector;

        this.$filterLinks = document.querySelectorAll(this.filterLinksSelector);
        this.$gameItems = document.querySelectorAll(this.gameItemSelector);

        this._initFilterEvents();
    }

    _initFilterEvents() {
        this.$filterLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();

                this.$filterLinks.forEach(l => l.classList.remove('active-link'));
                link.classList.add('active-link');

                const filterCategory = link.dataset.category || '1';
                this._applyFilter(filterCategory);
                this.eventBus.emit('filter:changed', filterCategory);
            });
        });

        const defaultLink = Array.from(this.$filterLinks).find(link => link.dataset.category === '1');
        if (defaultLink) {
            defaultLink.classList.add('active-link');
            this._applyFilter('1');
            this.eventBus.emit('filter:changed', '1');
        }
    }

    _applyFilter(filterCategory) {
        this.$gameItems.forEach(item => {
            const cat = item.getAttribute('data-category') || '';
            item.style.display = cat === filterCategory ? 'flex' : 'none';
        });
    }
}

class RGDetailPanel {
    constructor(config) {
        this.eventBus = config.eventBus;
        this.detailPanelSelector = config.detailPanelSelector;
        this.selectors = config.selectors;
        this.gamesData = config.gamesData || [];
        this.hoverDelay = config.hoverDelay || 0;
        this.hoverTimer = null;
        this.isMobile = window.innerWidth < 992; // Check initial viewport

        this.$detailPanel = document.querySelector(this.detailPanelSelector);
        this.$header = document.querySelector(this.selectors.header);
        this.$screenshots = document.querySelector(this.selectors.screenshots);
        this.$tags = document.querySelector(this.selectors.tags);
        this.$rating = document.querySelector(this.selectors.rating);
        this.$price = document.querySelector(this.selectors.price);
        this.$description = document.querySelector(this.selectors.description);
        this.$misc = document.querySelector(this.selectors.misc);

        // Füge einen Schließen-Button für mobile Geräte hinzu
        this._addCloseButton();

        // Event-Handler
        this._attachGameItemEvents();
        this._subscribeToEvents();
        this._handleResize();
    }

    _addCloseButton() {
        if (this.$detailPanel && !this.$detailPanel.querySelector('.close-detail-panel')) {
            const closeButton = document.createElement('button');
            closeButton.className = 'close-detail-panel';
            closeButton.innerHTML = '&times;';
            closeButton.addEventListener('click', () => this.resetPanel());
            this.$detailPanel.appendChild(closeButton);
        }
    }

    _subscribeToEvents() {
        this.eventBus.on('filter:changed', () => {
            this.resetPanel();
        });

        // Responsiveness-Handler für Fenstergrößenänderungen
        window.addEventListener('resize', () => this._handleResize());
    }

    _handleResize() {
        const wasMobile = this.isMobile;
        this.isMobile = window.innerWidth < 992;

        // Wenn sich der Device-Typ geändert hat
        if (wasMobile !== this.isMobile) {
            this.resetPanel();
            // Aktualisiere Event-Listener auf Game-Items
            this._attachGameItemEvents();
        }
    }

    _attachGameItemEvents() {
        const gameItems = document.querySelectorAll('.game-item');
        const gamesListContainer = document.getElementById('gamesListItems');

        // Entferne keine bestehenden Elemente mehr, arbeite mit den vorhandenen
        // Füge neue Event-Listener basierend auf dem Gerätetyp hinzu
        gameItems.forEach(item => {
            if (this.isMobile) {
                // Für Mobile: Tap-Event für Detail-Panel
                item.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    // Prüfe, ob das Panel bereits aktiv ist
                    const isPanelActive = this.$detailPanel &&
                        this.$detailPanel.classList.contains('rg-panel-active');

                    // Wenn das Panel aktiv ist, navigiere zur Detailseite
                    if (isPanelActive) {
                        const gameId = item.getAttribute('data-id');
                        window.location.href = `/games/detail/${gameId}`;
                        return;
                    }

                    // Andernfalls zeige das Detail-Panel an
                    this._handleItemInteraction(item);
                });

                // Schließe das Panel, wenn außerhalb geklickt wird
                document.addEventListener('click', (e) => {
                    if (this.$detailPanel &&
                        this.$detailPanel.classList.contains('rg-panel-active') &&
                        !this.$detailPanel.contains(e.target) &&
                        !e.target.closest('.game-item')) {
                        this.resetPanel();
                    }
                });
            } else {
                // Für Desktop: Hover-Events
                item.addEventListener('mouseover', () => {
                    if (this.hoverDelay > 0) {
                        clearTimeout(this.hoverTimer);
                        this.hoverTimer = setTimeout(() => {
                            this._handleItemInteraction(item);
                        }, this.hoverDelay);
                    } else {
                        this._handleItemInteraction(item);
                    }
                });

                item.addEventListener('mouseleave', () => {
                    if (this.hoverDelay > 0) {
                        clearTimeout(this.hoverTimer);
                    }
                });

                // Klick führt zur Detailseite
                item.addEventListener('click', (e) => {
                    e.stopPropagation();
                    const gameId = item.getAttribute('data-id');
                    window.location.href = `/games/detail/${gameId}`;
                });
            }
        });

        if (gamesListContainer && !this.isMobile) {
            gamesListContainer.addEventListener('mouseleave', () => {
                this.resetPanel();
            });
        }
    }

    _handleItemInteraction(item) {
        const gameId = parseInt(item.getAttribute('data-id'), 10);
        if (isNaN(gameId)) return;

        const gameData = this.gamesData.find(g => parseInt(g.id, 10) === gameId);
        if (!gameData) return;

        // Panel zurücksetzen bevor neue Daten geladen werden
        this.resetPanel();
        this._fillDetailPanel(gameData);

        // Panel positionieren und anzeigen
        requestAnimationFrame(() => {
            if (this.isMobile) {
                // Für mobile Geräte: Panel mittig auf dem Bildschirm (CSS übernimmt)
                this.$detailPanel.classList.add('rg-panel-active');
            } else {
                // Für Desktop: neben dem Hover-Element
                const itemRect = item.getBoundingClientRect();
                const viewportHeight = window.innerHeight;
                const navbarHeight = 70;
                const footerHeight = 100;

                const panel = this.$detailPanel;
                panel.style.position = 'fixed';
                panel.style.left = `${itemRect.right + 20}px`;

                // Panel-Höhe vor der Positionierung berechnen
                const panelHeight = panel.offsetHeight;

                // Berechnung der verfügbaren Höhe
                const availableHeight = viewportHeight - navbarHeight - footerHeight;

                // Wenn Panel größer als verfügbare Höhe, dann von oben beginnen
                let proposedTop;
                if (panelHeight > availableHeight) {
                    proposedTop = navbarHeight;
                } else {
                    // Sonst mittig zum gehoverten Element ausrichten
                    proposedTop = Math.min(
                        Math.max(navbarHeight, itemRect.top),
                        viewportHeight - panelHeight - footerHeight
                    );
                }

                panel.style.top = `${proposedTop}px`;
                panel.classList.add('rg-panel-active');
            }
        });
    }

    _fillDetailPanel(game) {
        if (this.$header) {
            this.$header.textContent = game.title;
        }

        if (this.$screenshots && game.tinyImageArray) {
            this.$screenshots.innerHTML = '';
            const images = game.tinyImageArray.split(';')
                .slice(0, 4)
                .map(src => src.trim())
                .filter(src => src);

            images.forEach(src => {
                const img = document.createElement('img');
                img.src = src;
                img.alt = `${game.title} Screenshot`;
                img.classList.add('mini-screenshot');
                img.loading = 'lazy';
                this.$screenshots.appendChild(img);
            });
        }

        if (this.$tags && game.tags) {
            this.$tags.innerHTML = game.tags
                .slice(0, 5)
                .map(tag => `<span class="tag">${tag}</span>`)
                .join('');
        }

        if (this.$rating && game.rating) {
            const stars = '★'.repeat(Math.round(game.rating)) + '☆'.repeat(5 - Math.round(game.rating));
            this.$rating.innerHTML = `${stars} (${game.rating}/5)`;
        }

        if (this.$price) {
            this.$price.innerHTML = game.price ? `<strong>${game.price}€</strong>` : 'Kostenlos';
        }

        if (this.$description) {
            const desc = game.description || 'Keine Beschreibung verfügbar.';
            this.$description.textContent = desc.length > 200 ?
                desc.substring(0, 200) + '...' : desc;
        }

        if (this.$rating) {
            if (game.positive_ratings || game.negative_ratings) {
                const total = (game.positive_ratings || 0) + (game.negative_ratings || 0);
                const positivePercent = total > 0 ?
                    Math.round((game.positive_ratings / total) * 100) : 0;

                this.$rating.innerHTML = `
                <div class="rating-mini">
                    <span class="positive">${positivePercent}% Positiv</span>
                    <span class="total">(${total} Bewertungen)</span>
                </div>
            `;
            } else {
                this.$rating.innerHTML = 'Noch keine Bewertungen';
            }
        }
    }

    resetPanel() {
        if (this.$detailPanel) {
            this.$detailPanel.classList.remove('rg-panel-active');
        }
    }
}

class RGManager {
    constructor(config) {
        this.eventBus = new RGGlobalEventBus();

        this.filter = new RGFilter({
            eventBus: this.eventBus,
            filterLinksSelector: '.games-nav a',
            gameItemSelector: '.game-item'
        });

        this.detailPanel = new RGDetailPanel({
            eventBus: this.eventBus,
            detailPanelSelector: '#detailPanel',
            selectors: {
                header: '#detailHeader',
                screenshots: '#detailScreenshots',
                tags: '#detailTags',
                rating: '#detailRating',
                price: '#detailPrice',
                description: '#detailDesc',
                misc: '#detailMisc'
            },
            gamesData: config.gamesData || [],
            hoverDelay: config.hoverDelay || 0
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const manager = new RGManager({
        gamesData: window.gamesData || [],
        hoverDelay: 200
    });

    // Stelle sicher, dass die Klick-Events auf den Game-Items
    // nicht die Tab-Filter-Events beeinträchtigen
    document.querySelectorAll('.games-nav a').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            // Entferne active-link von allen Tabs
            document.querySelectorAll('.games-nav a').forEach(l => {
                l.classList.remove('active-link');
            });

            // Setze active-link auf den geklickten Tab
            link.classList.add('active-link');

            // Wende den Filter an
            const category = link.getAttribute('data-category');
            document.querySelectorAll('.game-item').forEach(item => {
                const itemCategory = item.getAttribute('data-category') || '';
                item.style.display = (itemCategory === category) ? 'flex' : 'none';
            });
        });
    });
});