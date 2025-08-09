

document.addEventListener('DOMContentLoaded', () => {
 if (typeof initSeasonSelect === 'function') {
    initSeasonSelect();
  }
  if (typeof initTeamTabs === 'function') {
    initTeamTabs(); // <--- important pour tes tabs
  }
});
