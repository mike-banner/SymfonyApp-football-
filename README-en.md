🔁 [Version française](README.md)



# ⚽ Football Team Viewer

## 📁 1. Project Overview

**Project Name**: Football Team Viewer  
**Stack**: Symfony 6.4 LTS, RapidAPI (API-Football), Twig, Docker

This project allows you to display detailed football team information, including:  
- player names and photos,  
- positions, ages, nationalities,  
- league standings, upcoming matches, and more.

Data is retrieved from the API-Football on RapidAPI and locally cached as JSON files to optimize performance.

---

## ⚙️ 2. Features

### 2.1. Team Information
- Retrieve team name by ID
- Dynamically build cache file names

### 2.2. Player List
- Fetch the team’s squad for a given season
- Sort players by position (goalkeeper, defender, etc.)
- Display player data in a styled table

### 2.3. Image Handling
- Show player photo if available
- Use default neutral image when photo is missing

### 2.4. JSON Caching
- Save responses in the `/public/api` directory
- Filename format:  
  ```
  team-{country}-{team}-{division}-{id}-{year}.json
  ```

### 2.5. Dynamic Sorting
- Players are sorted by position (goalkeeper → attacker)

---

## 🧪 3. Installation

```bash
git clone https://github.com/michaelbanicles/football-team-viewer.git
cd football-team-viewer
composer install
cp .env .env.local
```

Edit `.env.local` and add:

```env
RAPIDAPI_KEY=your_api_key
```

### Using Docker:

```bash
docker compose up -d
```

Then:

```bash
docker compose exec php bash
composer install
```

---

## 🚀 4. Usage

### 4.1. Display a team’s squad

Example URL:
```
/equipe/85/2024
```

This will:
- call the API if needed,
- store the response as `public/api/team-france-psg-ligue-1-85-2024.json`,
- render the player list using a Twig template.

---

## 🧠 5. Project Structure

```
src/
├── Controller/
│   └── TeamController.php        # Main controller
├── Service/
│   └── PlayerApiService.php      # API logic + JSON caching
templates/
└── team/
    └── effectif.html.twig        # Squad display template
public/
└── api/                          # Cached JSON files
```

---

## 🛠 6. Future Improvements

- Add "Standings", "Schedule", "Squad" tabs
- Show individual player details
- Pagination for seasons and teams
- Store teams/players in a MySQL database
- Admin interface to manage cache files
