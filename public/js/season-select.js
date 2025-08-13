function initSeasonSelect() {
  const select = document.getElementById('season-select');
  if (select) {
    const leagueId = select.dataset.leagueId;
    select.addEventListener('change', function () {
      const year = this.value;
      if (leagueId && year) {
        window.location.href = `/standing/${leagueId}/${year}`;
      }
    });
  }
}
