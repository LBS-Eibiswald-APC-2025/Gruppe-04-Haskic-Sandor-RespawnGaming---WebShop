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

        this.$detailPanel = document.querySelector(this.detailPanelSelector);
        this.$header = document.querySelector(this.selectors.header);
        this.$screenshots = document.querySelector(this.selectors.screenshots);
        this.$tags = document.querySelector(this.selectors.tags);
        this.$rating = document.querySelector(this.selectors.rating);
        this.$price = document.querySelector(this.selectors.price);
        this.$description = document.querySelector(this.selectors.description);
        this.$misc = document.querySelector(this.selectors.misc);

        this._attachGameItemHoverEvents();
        this._subscribeToEvents();
    }

    _subscribeToEvents() {
        this.eventBus.on('filter:changed', () => {
            this.resetPanel();
        });
    }

    _attachGameItemHoverEvents() {
        const gameItems = document.querySelectorAll('.game-item');
        const gamesListContainer = document.getElementById('gamesListItems');

        gameItems.forEach(item => {
            item.addEventListener('mouseover', () => {
                if (this.hoverDelay > 0) {
                    clearTimeout(this.hoverTimer);
                    this.hoverTimer = setTimeout(() => {
                        this._handleHover(item);
                    }, this.hoverDelay);
                } else {
                    this._handleHover(item);
                }
            });

            item.addEventListener('mouseleave', () => {
                if (this.hoverDelay > 0) {
                    clearTimeout(this.hoverTimer);
                }
            });
        });

        if (gamesListContainer) {
            gamesListContainer.addEventListener('mouseleave', () => {
                this.resetPanel();
            });
        }
    }

    _handleHover(item) {
        const gameId = parseInt(item.getAttribute('data-id'), 10);
        if (isNaN(gameId)) return;

        const gameData = this.gamesData.find(g => parseInt(g.id, 10) === gameId);
        if (!gameData) return;

        // Panel zurücksetzen bevor neue Daten geladen werden
        this.resetPanel();
        this._fillDetailPanel(gameData);

        requestAnimationFrame(() => {
            const itemRect = item.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const navbarHeight = 70;
            const footerHeight = 100; // Erhöht für mehr Abstand

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

    document.querySelectorAll('.game-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.stopPropagation();
            const gameId = item.getAttribute('data-id');
            window.location.href = `/games/detail/${gameId}`;
        });
    });
});