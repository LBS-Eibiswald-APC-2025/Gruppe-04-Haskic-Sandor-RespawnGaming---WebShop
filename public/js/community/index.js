document.addEventListener('DOMContentLoaded', function() {
    // Kategorie-Filterung
    const navLinks = document.querySelectorAll('.forum-categories ul.nav-tabs li.nav-item a.nav-link');
    const threadItems = document.querySelectorAll('.thread-item');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            // Alle Tabs deaktivieren
            navLinks.forEach(l => l.classList.remove('active'));
            this.classList.add('active');

            const category = this.getAttribute('data-category');
            threadItems.forEach(item => {
                if (category === 'all' || item.getAttribute('data-category') === category) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Neues Thema erstellen – Öffnen des Bootstrap-Modals
    const newThreadBtn = document.getElementById('newThreadBtn');
    if (newThreadBtn) {
        newThreadBtn.addEventListener('click', function() {
            const newThreadModal = new bootstrap.Modal(document.getElementById('newThreadModal'));
            newThreadModal.show();
        });
    }
});
