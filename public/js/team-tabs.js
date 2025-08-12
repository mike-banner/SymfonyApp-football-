document.addEventListener('DOMContentLoaded', () => {
    // Sélecteurs des boutons onglets
    const tabButtons = document.querySelectorAll('.tab-button');
    // Tous les conteneurs des onglets (panels)
    const tabPanels = document.querySelectorAll('.tab-panel');

    // Récupérer les infos équipe depuis un conteneur (data attributes)
    const teamInfo = document.getElementById('team-info');
    if (!teamInfo) return;

    const teamId = teamInfo.dataset.teamId;
    const season = teamInfo.dataset.season;
    const league = teamInfo.dataset.league;

    // Fonction pour charger un onglet par AJAX
    function loadTab(tab, page = 1) {
        const container = document.querySelector(`.tab-panel[data-tab="${tab}"]`);
        if (!container) return;

        container.innerHTML = '<p>Chargement...</p>';
        // Cacher tous les panels + retirer active des boutons
        tabPanels.forEach(p => p.hidden = true);
        tabButtons.forEach(b => b.classList.remove('active'));

        // Envoi POST avec fetch
        fetch('/team/load-tab', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                teamId,
                season,
                league,
                tab,
                page
            })
        })
        .then(resp => {
            if (!resp.ok) throw new Error('Erreur chargement onglet');
            return resp.text();
        })
        .then(html => {
            container.innerHTML = html;
            container.hidden = false;
            // Ajouter active sur le bouton correspondant
            document.querySelector(`.tab-button[data-tab="${tab}"]`).classList.add('active');

            // Si le contenu a une pagination, on peut ajouter gestion (optionnel)
            const paginationLinks = container.querySelectorAll('.pagination a');
            paginationLinks.forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    const pageParam = link.dataset.page;
                    if (pageParam) {
                        loadTab(tab, parseInt(pageParam));
                    }
                });
            });
        })
        .catch(() => {
            container.innerHTML = `<p>Erreur lors du chargement de l’onglet ${tab}.</p>`;
            container.hidden = false;
        });
    }

    // Initialiser le premier onglet actif (par ex. players)
    if (tabButtons.length > 0) {
        const firstTab = tabButtons[0].dataset.tab;
        loadTab(firstTab);
    }

    // Écouteurs sur les boutons pour changement d'onglet
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const tab = button.dataset.tab;
            loadTab(tab);
        });
    });
});
