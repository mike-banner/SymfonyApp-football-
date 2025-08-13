# Football Team Viewer - README
# 1. Quick Overview
Symfony 6.4 project + Football API (RapidAPI) that displays team rosters, including player photos, positions, age, nationality, and local JSON cache management.

# 2. Installation

git clone https://github.com/michaelbanicles/football-team-viewer.git
cd football-team-viewer
composer install
cp .env .env.local

# Add your API key in .env.local
RAPIDAPI_KEY=your_api_key

symfony server:start
# or
docker compose up -d
