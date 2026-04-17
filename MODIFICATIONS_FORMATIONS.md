# 📅 Modifications - Gestion des Formations Admin

## 📋 Résumé
Ajout d'une vue alternative permettant à l'administrateur de basculer entre:
1. **Liste** - Tableau des formations actuel
2. **Planning Global** - Calendrier synchronisé (style Microsoft Teams)

---

## 🎯 Objectifs Réalisés

### ✅ Header d'Action avec Tabs
- Groupe de boutons de basculement juste sous le titre "Gestion des Formations"
- Bouton "Liste" (actif par défaut) avec icône `list`
- Bouton "Planning Global" avec icône `calendar`
- Style glassmorphism épuré correspondant au design Aptus
- Icônes Lucide optimisées

### ✅ Structure des Conteneurs
- Tableau actuel encapsulé dans `<div id="view-list">`
- Nouvelle vue dans `<div id="view-calendar">` (masquée par défaut)
- Conteneur FullCalendar dans `.calendar-container` (glass-card)
- Légende expliquant les codes couleurs

### ✅ Logique de Basculement
- Fonction `switchView(viewName)` gère l'affichage/masquage
- **Crucial:** `window.dispatchEvent(new Event('resize'))` force FullCalendar à recalculer sa taille
- Transitions fluides CSS avec `display` et classes d'état

### ✅ Configuration FullCalendar
- **Vue par défaut:** `timeGridWeek` (semaine, comme Teams)
- **Titre des événements:** Format `[Tuteur] - [Titre Formation]`
- **Code couleur:**
  - 🔵 **Bleu (#3b82f6)** → En ligne (Jitsi)
  - 🟢 **Vert (#10b981)** → Présentiel
- **Locale:** Français
- **Interactions:** Click sur un événement affiche un modal avec détails

### ✅ Filtres Intégrés (Légende)
- Zone de légende à droite du calendrier
- Affiche les deux types de formation avec couleurs
- Responsive: 2 colonnes sur mobile

---

## 📁 Fichiers Modifiés

### 1. **layout_back.php**
```diff
+ <!-- FullCalendar CDN (v6.1.8) -->
+ <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet" />
+ <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
```
- Ajout des ressources FullCalendar

### 2. **formations.css** (NOUVEAU CONTENU)
```css
/* Tabs Glassmorphism */
.view-tabs { backdrop-filter: blur(10px); border-radius: 12px; }
.view-tab { transition: all 0.3s ease; }
.view-tab.active { background: rgba(107, 52, 163, 0.15); }

/* Calendar Layout */
.calendar-wrapper { display: grid; grid-template-columns: 1fr 200px; }
.calendar-legend { background: var(--bg-surface); }
.legend-item { display: flex; align-items: center; gap: 12px; }

/* FullCalendar Customization */
.fc .fc-button-primary { background-color: var(--accent-primary); }
.fc-event-online { background-color: #3b82f6; }
.fc-event-presentiel { background-color: #10b981; }
```
- ~180 lignes de styles pour les tabs, calendrier et légende

### 3. **FormationController.php**
```php
public function getFormationsForCalendar()
{
    // Retourne un tableau d'objets événement FullCalendar
    // Structure: id, title, start, end, backgroundColor, extendedProps
    // extendedProps: tuteur, type, lieu, domaine, niveau
}
```
- Nouvelle méthode pour formater les données au format JSON
- Compatible avec FullCalendar v6

### 4. **formations_admin.php** (MODIFICATIONS PRINCIPALES)

#### a) Récupération des données (ligne ~28)
```php
$calendarEvents = json_encode($formationC->getFormationsForCalendar());
```

#### b) Tabs de basculement (après titre)
```html
<div class="view-tabs">
  <button class="view-tab active" data-view="list" onclick="switchView('list')">
    <i data-lucide="list"></i> Liste
  </button>
  <button class="view-tab" data-view="calendar" onclick="switchView('calendar')">
    <i data-lucide="calendar"></i> Planning Global
  </button>
</div>
```

#### c) Deux conteneurs de vue
```html
<div id="view-list" class="active"> ... tableau formations ... </div>
<div id="view-calendar"> ... calendrier + légende ... </div>
```

#### d) JavaScript (fin du fichier)
```javascript
function switchView(viewName) {
    // Gère l'affichage/masquage des deux div
    // Dispatch 'resize' au passage en calendrier
}

// Initialisation FullCalendar avec configuration Teams
window.formationCalendar = new FullCalendar.Calendar(...)
```

---

## 🎨 Design & UX

### Glassmorphism Tabs
- Fond semi-transparent avec flou
- Border subtile
- Transition fluide au survol
- État actif avec couleur accent

### Calendrier (Style Teams)
- Vue semaine par défaut
- Heures 09:00-10:00 pour chaque formation
- Codes couleur clairs (Bleu/Vert)
- Responsive design

### Légende
- Positions à droite du calendrier
- Texte + indicateur de couleur
- Explique les deux types de formation

---

## 🔧 Détails Techniques

### MVC Architecture ✅
```
Model (Formation.php)
  ↓
Controller (FormationController.php) → getFormationsForCalendar()
  ↓
View (formations_admin.php) → JSON encoding + FullCalendar init
```

### Données Calendrier (JSON)
```json
{
  "id": 1,
  "title": "Jean Dupont - Masterclass IA",
  "start": "2026-04-15T09:00:00",
  "end": "2026-04-15T10:00:00",
  "backgroundColor": "#3b82f6",
  "extendedProps": {
    "tuteur": "Jean Dupont",
    "type": "online",
    "lieu": "En ligne",
    "domaine": "Intelligence Artificielle",
    "niveau": "Avancé"
  }
}
```

### Événement Resize (CRUCIAL)
```javascript
window.dispatchEvent(new Event('resize'));
```
Force FullCalendar à recalculer sa taille quand l'élément devient visible.
**Sans cette ligne:** Le calendrier s'affiche mal initially hidden.

---

## 📱 Responsive Design

### Desktop (> 1024px)
- Calendrier sur 2 colonnes (cal + légende)
- Tabs sur une ligne

### Tablet/Mobile (≤ 768px)
- Calendrier full width
- Légende sur 2 colonnes
- Tabs scrollable si nécessaire

---

## 🚀 Comment Ça Fonctionne

1. **Admin clique sur "Planning Global"**
   - Fonction `switchView('calendar')` s'exécute
   - Vue liste se masque (`display: none`)
   - Vue calendrier s'affiche (`display: block`)
   - FullCalendar reçoit un événement `resize`

2. **FullCalendar initialise**
   - Récupère les événements du JSON PHP
   - Affiche la semaine en cours par défaut
   - Coulore les événements selon le type

3. **Admin clique sur un événement**
   - Modal alert affiche les détails
   - [Optionnel: Peut être remplacé par un vrai modal]

4. **Admin retourne à la Liste**
   - Vue calendrier se masque
   - Vue liste réapparaît

---

## 📝 Notes Importantes

- ✅ Les formations existantes s'importent automatiquement dans le calendrier
- ✅ Pas d'élément dans la sidebar (comme demandé)
- ✅ Responsif sur tous les appareils
- ✅ Design cohérent avec Aptus
- ⚠️ Le calendrier ne modifie pas les formations (affichage uniquement)
- ⚠️ Peut être étendu avec drag-drop ou édition future

---

## 🔮 Extensions Futures Possibles

- Drag-drop des formations sur le calendrier
- Modal améché au lieu d'alert
- Filtrage par tuteur/domaine
- Synchronisation avec les inscriptions
- Export ICS/Google Calendar
- Notification des présentations à venir

---

**Version:** 1.0 | **Date:** April 2026 | **Developer:** Admin Panel
