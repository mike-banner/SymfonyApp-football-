function initTeamTabs() {
  const tabButtons = document.querySelectorAll('.tab-button');
  const tabPanels = document.querySelectorAll('.tab-panel');

  tabButtons.forEach((btn) => {
    btn.addEventListener('click', async () => {
      const target = btn.dataset.tab;

      // Toggle active button
      tabButtons.forEach((b) => b.classList.remove('active'));
      btn.classList.add('active');

      // Toggle panel visibility
      tabPanels.forEach((panel) => {
        if (panel.dataset.tab === target) {
          panel.classList.add('active');
          panel.removeAttribute('hidden');
        } else {
          panel.classList.remove('active');
          panel.setAttribute('hidden', 'true');
        }
      });

      // Charger dynamiquement le contenu via AJAX
      const panel = document.querySelector(`.tab-panel[data-tab="${target}"]`);
      if (!panel) return;

      // Ne charge qu'une seule fois
      if (panel.dataset.loaded === 'true') return;

      // Récupérer infos équipe
      const info = document.getElementById('team-info');
      if (!info) {
        panel.innerHTML = '<p>Informations de l’équipe non trouvées.</p>';
        return;
      }

      const teamId = info.dataset.teamId;
      const season = info.dataset.season;
      const league = info.dataset.league || '39'; // par défaut Premier League

      if (!teamId || !season) {
        panel.innerHTML = '<p>Paramètres manquants pour charger les données.</p>';
        return;
      }

      // Log debug
      console.log('Chargement onglet:', target, { teamId, season, league });

      // Préparer les données POST
      const formData = new FormData();
      formData.append('teamId', teamId);
      formData.append('season', season);
      formData.append('league', league);
      formData.append('tab', target);

      // Log contenu FormData
      for (const [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
      }

      // Requête AJAX
      try {
        const response = await fetch('/team/load-tab', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: formData,
        });

        if (!response.ok) {
          const errorText = await response.text();
          throw new Error(`Erreur réseau: ${response.status} - ${errorText}`);
        }

        const html = await response.text();
        panel.innerHTML = html;
        panel.dataset.loaded = 'true';
      } catch (error) {
        panel.innerHTML = `<p>Erreur lors du chargement de l’onglet ${target}.</p>`;
        console.error(error);
      }
    });
  });

  // Activer le premier onglet au chargement
  if (tabButtons.length > 0) {
    tabButtons[0].click();
  }
}

document.addEventListener('DOMContentLoaded', initTeamTabs);
