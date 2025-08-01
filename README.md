
# ⚽ Football Team Viewer

## 📁 1. Présentation du projet

**Nom du projet** : Football Team Viewer  
**Stack** : Symfony 6.4 LTS, RapidAPI (API-Football), Twig, Docker

Ce projet permet de consulter des informations détaillées sur les équipes de football :  
- noms et photos des joueurs,  
- postes, âges, nationalités,  
- classement à venir, calendrier, etc.

dans un premier temps:
v1: Les données sont récupérées depuis l’API Football de RapidAPI, puis mises en cache localement sous forme de fichiers JSON pour améliorer les performances. (POUR LA PARTI CLASSEMENT DES CHAMPIONNATS ET EFFECTIF)

---

## ⚙️ 2. Fonctionnalités

### 2.1. Récupération d'équipe
- Récupération du nom de l'équipe par ID
- Construction dynamique du nom de fichier JSON de cache

### 2.2. Liste des joueurs
- Appel de l’API pour récupérer l’effectif d’une équipe pour une saison donnée
- Tri des joueurs par poste (gardien, défenseur, etc.)
- Affichage des informations dans un tableau

### 2.3. Gestion des images
- Affichage de la photo du joueur si disponible
- Fallback sur une image neutre si aucune photo n’est fournie

### 2.4. Cache JSON
- Enregistrement des données récupérées dans le dossier `/public/api`
- Nom du fichier au format :  
  ```
  team-{pays}-{equipe}-{division}-{id}-{annee}.json
  ```

### 2.5. Tri dynamique
- Les joueurs sont triés dynamiquement selon leur poste (gardien → attaquant)

---

## 🧪 3. Installation

```bash
git clone https://github.com/michaelbanicles/football-team-viewer.git
cd football-team-viewer
composer install
cp .env .env.local
```

Configure `.env.local` :

```env
RAPIDAPI_KEY=your_api_key
```

### Si vous utilisez Docker :

```bash
docker compose up -d
```

Puis :

```bash
docker compose exec php bash
composer install
```

---

## 🚀 4. Utilisation

### 4.1. Afficher les joueurs d'une équipe

URL (exemple) :
```
/equipe/85/2024
```

Cela va :
- appeler l’API si besoin,
- stocker la réponse dans `public/api/team-france-psg-ligue-1-85-2024.json`,
- afficher les joueurs dans une vue Twig.

---

## 🧠 5. Structure du code

```
src/
├── Controller/
│   └── TeamController.php        # Contrôleur principal
├── Service/
│   └── PlayerApiService.php     # Appels API + cache JSON
templates/
└── team/
    └── effectif.html.twig       # Affichage de l’effectif
public/
└── api/                         # Cache JSON
```

---

## 🛠 6. Améliorations futures

- Ajout des onglets "Classement", "Calendrier", "Effectif"
- Affichage du détail d’un joueur
- Système de pagination pour les équipes et les saisons
- Mise en base de données (MySQL) des équipes/joueurs
- Interface admin pour mettre à jour ou supprimer les fichiers cache
