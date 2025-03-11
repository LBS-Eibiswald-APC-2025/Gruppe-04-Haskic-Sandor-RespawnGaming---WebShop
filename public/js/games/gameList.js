/**************************************************************
 *                      RESPAWN-GAMING CODE
 * Ein hochgradig modularer Ansatz mit EventBus & Klassen,
 * für Filter und Hover-Detail-Panel auf einer Game-Übersicht.
 **************************************************************/

/**
 * Ein globaler EventBus für lockere Kopplung.
 * Komponenten können sich anmelden und Events empfangen oder versenden,
 * ohne direkt voneinander abhängig zu sein.
 */
class RGGlobalEventBus {
    constructor() {
        this.events = {};
    }

    /**
     * Registriert einen Callback für ein bestimmtes Event.
     * @param {string} eventName
     * @param {Function} callback
     */
    on(eventName, callback) {
        if (!this.events[eventName]) {
            this.events[eventName] = [];
        }
        this.events[eventName].push(callback);
    }

    /**
     * Löst ein Event mit beliebigen Daten aus.
     * @param {string} eventName
     * @param {*} data
     */
    emit(eventName, data) {
        const listeners = this.events[eventName] || [];
        listeners.forEach(callback => callback(data));
    }
}

document.querySelectorAll('.game-item').forEach(item => {
    item.addEventListener('click', () => {
        const gameId = item.getAttribute('data-id');
        // Leite auf die Detail-Seite weiter, z.B. per GET-Parameter
        window.location.href = `/games/detail.php?id=${gameId}`;
    });
});

/**
 * RGFilter: Verwaltet Filterfunktionen über die Spiele-Liste.
 * Sorgt dafür, dass nur die Items einer bestimmten Kategorie sichtbar sind.
 */
class RGFilter {
    /**
     * @param {Object} config
     * @param {RGGlobalEventBus} config.eventBus
     * @param {string} config.filterLinksSelector - CSS-Selektor für die Links
     * @param {string} config.gameItemSelector - CSS-Selektor für die Spiele-Items (z.B. ".game-item")
     */
    constructor(config) {
        this.eventBus = config.eventBus;
        this.filterLinksSelector = config.filterLinksSelector;
        this.gameItemSelector = config.gameItemSelector;

        // DOM-Elemente ermitteln
        this.$filterLinks = document.querySelectorAll(this.filterLinksSelector);
        this.$gameItems = document.querySelectorAll(this.gameItemSelector);

        this._initFilterEvents();
    }

    /**
     * Hängt Klick-Listener an die Filterlinks an und setzt standardmäßig Kategorie 1.
     */
    _initFilterEvents() {
        this.$filterLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();

                // Alle Links als inaktiv markieren
                this.$filterLinks.forEach(l => l.classList.remove('active-link'));

                // Den geklickten Link hervorheben (Glow-Effekt)
                link.classList.add('active-link');

                // data-category auslesen
                const filterCategory = link.dataset.category || '1';

                // Filter anwenden
                this._applyFilter(filterCategory);

                // Event auslösen (z.B. damit das DetailPanel reagieren kann)
                this.eventBus.emit('filter:changed', filterCategory);
            });
        });

        // Standardmäßig Spiele der Kategorie 1 anzeigen
        const defaultLink = Array.from(this.$filterLinks).find(link => link.dataset.category === '1');
        if (defaultLink) {
            defaultLink.classList.add('active-link');
            this._applyFilter('1');
            this.eventBus.emit('filter:changed', '1');
        }
    }

    /**
     * Wendet den gewählten Filter an und blendet die .game-item-Elemente entsprechend ein/aus.
     * @param {string} filterCategory
     */
    _applyFilter(filterCategory) {
        this.$gameItems.forEach(item => {
            const cat = item.getAttribute('data-category') || '';

            // console.log('1: ' + cat);
            // console.log('2: ' + filterCategory);
            // console.log(item);
            // console.log("====================================");

            // Nur Items der gewählten Kategorie anzeigen
            if (cat === filterCategory) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
}

/**
 * RGDetailPanel: Kümmert sich darum, bei Hover auf einem Spiel
 * die Detailinfos (Screenshots, Titel, Tags, Bewertung etc.) anzuzeigen.
 */
class RGDetailPanel {
    /**
     * @param {Object} config
     * @param {RGGlobalEventBus} config.eventBus
     * @param {string} config.detailPanelSelector - Selektor für das gesamte Detail-Panel
     * @param {Object} config.selectors
     * @param {string} config.selectors.header
     * @param {string} config.selectors.screenshots
     * @param {string} config.selectors.tags
     * @param {string} config.selectors.rating
     * @param {string} config.selectors.price
     * @param {string} config.selectors.description
     * @param {string} config.selectors.misc
     * @param {Array} config.gamesData - Array mit allen Spiele-Objekten
     * @param {number} [config.hoverDelay=0] - Optional, Verzögerung in ms beim Hover
     */
    constructor(config) {
        this.eventBus = config.eventBus;
        this.detailPanelSelector = config.detailPanelSelector;
        this.selectors = config.selectors;
        this.gamesData = config.gamesData || [];
        this.hoverDelay = config.hoverDelay || 0;

        // Panel-Elemente
        this.$detailPanel = document.querySelector(this.detailPanelSelector);
        this.$header = document.querySelector(this.selectors.header);
        this.$screenshots = document.querySelector(this.selectors.screenshots);
        this.$tags = document.querySelector(this.selectors.tags);
        this.$rating = document.querySelector(this.selectors.rating);
        this.$price = document.querySelector(this.selectors.price);
        this.$description = document.querySelector(this.selectors.description);
        this.$misc = document.querySelector(this.selectors.misc);

        // Timer für verzögertes Hover
        this.hoverTimer = null;

        this._attachGameItemHoverEvents();
        this._subscribeToEvents();
    }

    /**
     * Registriert sich bei EventBus-Events.
     */
    _subscribeToEvents() {
        // Wenn sich der Filter ändert, Panel zurücksetzen
        this.eventBus.on('filter:changed', () => {
            this.resetPanel();
        });
    }

    /**
     * Fügt allen .game-item-Elementen Mouseover-Events hinzu,
     * um das DetailPanel zu aktualisieren.
     */
    _attachGameItemHoverEvents() {
        const gameItems = document.querySelectorAll('.game-item');
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

            // Verlässt die Maus das Item, brechen wir den Timer ab
            item.addEventListener('mouseleave', () => {
                if (this.hoverDelay > 0) {
                    clearTimeout(this.hoverTimer);
                }
            });
        });

        // Optional: Panel zurücksetzen, wenn die Maus den Container verlässt
        const gamesListContainer = document.getElementById('gamesListItems');
        if (gamesListContainer) {
            gamesListContainer.addEventListener('mouseleave', () => {
                this.resetPanel();
            });
        }
    }

    /**
     * Ermittelt aus data-id das richtige Spielobjekt
     * und füllt das DetailPanel.
     * @param {HTMLElement} item
     */

    _handleHover(item) {
        const gameId = parseInt(item.getAttribute('data-id'), 10);
        if (isNaN(gameId)) return;

        // Hole die Spieldaten
        const gameData = this.gamesData.find(g => parseInt(g.id, 10) === gameId);
        if (!gameData) return;

        // Fülle das Panel mit den Spieldaten
        this._fillDetailPanel(gameData);

        // Entferne den aktiven Zustand, um den Transition-Effekt neu starten zu können
        this.$detailPanel.classList.remove('rg-panel-active');
        // Animation zurücksetzen: Inline-Animation auf 'none' setzen und dann reflow erzwingen
        this.$detailPanel.style.animation = 'none';
        void this.$detailPanel.offsetHeight; // Erzwingt Reflow
        this.$detailPanel.style.animation = '';

        // Berechne die neue Position im Container
        requestAnimationFrame(() => {
            // Hier wird der Container als Referenz genutzt – passe den Selektor an, falls nötig
            const wrapper = document.getElementById('gamesListItems');
            if (!wrapper) return;
            const wrapperRect = wrapper.getBoundingClientRect();
            const itemRect = item.getBoundingClientRect();
            const panelRect = this.$detailPanel.getBoundingClientRect();

            const navHeight = 70; // Passe diesen Wert an deine Navbar-Höhe an
            let offset = (itemRect.top + (itemRect.height / 2)) - (panelRect.height / 2) - wrapperRect.top;
            if (offset < navHeight) offset = navHeight;
            const maxOffset = wrapperRect.height - panelRect.height;
            if (offset > maxOffset) offset = maxOffset;

            // Setze die Position – hier verwenden wir 'top'
            this.$detailPanel.style.top = offset + 'px';

            // Füge die aktive Klasse wieder hinzu – dadurch startet der Transition-Effekt erneut
            this.$detailPanel.classList.add('rg-panel-active');
        });
    }

    /**
     * DetailPanel mit allen Feldern befüllen.
     * @param {Object} game
     */
    _fillDetailPanel(game) {
        // 1) Header: Title (+ optional Price)
        if (this.$header) {
            let headerText = game.title || 'Kein Titel';
            if (game.price) {
                headerText += ` - ${game.price}`;
            }
            this.$header.textContent = headerText;
        }

        // 2) Screenshots
        if (this.$screenshots) {
            this.$screenshots.innerHTML = '';
            const screenshotUrls = game.tinyImageArray
                ? game.tinyImageArray.split(';').map(s => s.trim())
                : [];
            if (screenshotUrls.length > 0) {
                screenshotUrls.forEach(src => {
                    const img = document.createElement('img');
                    img.src = src;
                    img.alt = `${game.title} Screenshot`;
                    img.classList.add('mini-screenshot');
                    this.$screenshots.appendChild(img);
                });
            } else {
                this.$screenshots.textContent = 'Keine Screenshots verfügbar';
            }
        }

        // 3) Tags
        if (this.$tags) {
            this.$tags.innerHTML = '';
            if (Array.isArray(game.tags)) {
                game.tags.forEach(tag => {
                    const span = document.createElement('span');
                    span.classList.add('tag');
                    span.textContent = tag;
                    this.$tags.appendChild(span);
                });
            }
        }

        // 4) Rating
        if (this.$rating) {
            if (game.rating && !isNaN(parseFloat(game.rating))) {
                this.$rating.textContent = `Bewertung: ${game.rating} / 5`;
            } else {
                this.$rating.textContent = 'Keine Bewertung verfügbar';
            }
        }

        // 5) Price
        if (this.$price) {
            this.$price.textContent = game.price || '';
        }

        // 6) Description
        if (this.$description) {
            this.$description.textContent = game.description || 'Keine Beschreibung hinterlegt.';
        }

        // 7) Misc (z.B. Release-Date)
        if (this.$misc) {
            let infoText = '';
            if (game.release_date) {
                infoText += `Veröffentlichung: ${game.release_date}`;
            }
            this.$misc.textContent = infoText;
        }

        // 8) Animation / Glow hinzufügen (falls gewünscht)
        if (this.$detailPanel) {
            this.$detailPanel.classList.add('rg-panel-active');
        }
    }

    /**
     * Setzt alle Felder im DetailPanel auf Standard zurück.
     */
    resetPanel() {
        if (this.$header) {
            this.$header.textContent = 'Bitte mit der Maus über ein Spiel fahren';
        }
        if (this.$screenshots) {
            this.$screenshots.innerHTML = '';
        }
        if (this.$tags) {
            this.$tags.innerHTML = '';
        }
        if (this.$rating) {
            this.$rating.textContent = '';
        }
        if (this.$price) {
            this.$price.textContent = '';
        }
        if (this.$description) {
            this.$description.textContent = '';
        }
        if (this.$misc) {
            this.$misc.textContent = '';
        }
        if (this.$detailPanel) {
            this.$detailPanel.classList.remove('rg-panel-active');
        }
    }
}

/**
 * RGManager: Fasst Filter und DetailPanel zusammen
 * und kümmert sich um Initialisierung und globale Abläufe.
 */
class RGManager {
    /**
     * @param {Object} config
     * @param {Array} config.gamesData - Array mit allen Game-Objekten
     * @param {number} [config.hoverDelay=0] - Delay in ms beim Hover
     */
    constructor(config) {
        this.eventBus = new RGGlobalEventBus();

        // Filter-Komponente
        this.filter = new RGFilter({
            eventBus: this.eventBus,
            filterLinksSelector: '.games-nav a',
            gameItemSelector: '.game-item'
        });

        // DetailPanel-Komponente
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

/****************************************************
 * Initialisierung nach DOM-Load
 ****************************************************/
document.addEventListener('DOMContentLoaded', () => {
    // Erstelle Manager-Instanz mit unseren Daten
    const manager = new RGManager({
        gamesData: window.gamesData || [],
        hoverDelay: 200 // Beispiel: 200ms Verzögerung
    });

    // Füge Klick-Event-Listener zu allen Spiel-Items hinzu
    document.querySelectorAll('.game-item').forEach(item => {
        item.addEventListener('click', (e) => {
            // Event-Bubbling stoppen, um Konflikte zu vermeiden
            e.stopPropagation();
            const gameId = item.getAttribute('data-id');
            // Weiterleiten zur Detailseite
            window.location.href = `/games/detail/${gameId}`;
        });
    });
});

