# Football Team Viewer - README

## 1. Présentation rapide

Projet Symfony 6.4 + API Football (RapidAPI) qui affiche l’effectif des équipes, avec photos, postes, âge, nationalité, et gestion du cache JSON local.

---

## 2. Installation

```bash
git clone https://github.com/michaelbanicles/football-team-viewer.git
cd football-team-viewer
composer install
cp .env .env.local


RAPIDAPI_KEY=ta_clef_api


symfony server:start
# ou
docker compose up -d
