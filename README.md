# APTUS
Un site web qui peut t'accompagner dans chaque pas de ta carrière developé par ARCHIPEL

## Table des matières
- [Présentation du projet](#présentation-du-projet)
- [Objectifs](#objectifs)
- [Modules du projet](#modules-du-projet)
- [ODD visés](#odd-visés)
- [Technologies utilisées](#technologies-utilisées)
- [Architecture](#architecture)
- [Membres du groupe](#membres-du-groupe)
- [Contexte académique](#contexte-académique)
- [Démarrage](#démarrage)

## Présentation du projet 
Aptus est une plateforme tout-en-un qui aide les candidats à accélérer leur insertion professionnelle en combinant un éditeur de CV optimisé, un moteur de recherche d’offres ciblées et un catalogue de formations pertinentes.
## Objectifs
- Permettre aux jobhunters de créer, éditer et gérer leur profil (bio, portfolio, CV, compétences)
- Permettre aux entreprises de créer et gérer leur profil (offres, formulaires)
- Permettre aux jobhunters de postuler à un job en remlissant un formulaire
- Permettre aux entreprises de consulter les candidatures des jobhunters et de sélectionner les candidats pour leurs offres
- Fournir des notifications et messages (email, in‑app) pour candidatures, validations ou refus
- Fournir un espace administratif pour consulter, gérer les comptes et prendre des décisions ( ajout,modification,suppressions de contenu , de formation,...)
- Fournir au admin des statistiques et rapports sur les entreprises et jobhunters (performances de campagne, taux de réponses, engagement)
- Intégrer des recommandations basées sur l'intelligence artificielle (matching formation ↔ jobhunter, suggestions d'offres, correction des formulaires ,optimisation de profil et CV)
- Implémenter des mesures de sécurité avancées (authentification forte, gestion des rôles)
## Modules du projet
### 1. Utilisateurs & Accès
- S’authentifier (inscription / connexion / déconnexion)
- Gérer le profil utilisateur (infos, paramètres)
- Administration de la plateforme (rôles, supervision, gestion globale)
### 2. CV & Analyse IA
- Créer / importer / modifier le CV
- Analyser le CV (extraction de compétences, points forts)
- Calculer le matching CV ↔ offre (score de compatibilité, recommandations)
### 3. Offres & Candidatures (Posts & Formulaires)
- Publier, modifier et supprimer des offres/posts (côté entreprise)
- Postuler via formulaires (côté job hunter)
- Gérer les candidatures (suivi statut, consultation, traitement)
### 4. Formations & Recommandations
- Accéder au catalogue de formations
- Recommandations de formations (basées sur l’analyse CV / matching)
- Suivi des formations (progression / complétion si disponible)
### 5. Portfolio & Réalisations
- Créer et mettre à jour le portfolio (projets, preuves, liens)
- Consulter un portfolio (entreprise / recruteur)
- Valoriser les candidatures via les réalisations (support au recrutement)
## ODD visés
- ODD 8 : Travail décent et croissance économique
- ODD 9 : Industrie, innovation et infrastructure
- ODD 10 : Réduction des inégalités
- ODD 12 : Consommation et production responsables
- ODD 17 : Partenariats pour la réalisation des objectifs
## Technologies utilisées
### Frontend
- HTML5
- CSS3
- JavaScript
### Backend
- PHP
### Base de données
- MySQL
### Outils de développement
- Git
- GitHub
- Visual Studio Code
- XAMPP / Apache Server
## Architecture
Notre application suit une architecture Client-Serveur basée sur le modèle MVC (Model-View-Controller).
### Flux de travail
1. L’utilisateur interagit avec l’interface.
2. Une requête est envoyée au Contrôleur.
3. Le Contrôleur traite la logique métier
4. Le Modèle accède à la base de données pour lire ou écrire les données.
5. Une réponse est renvoyée à l’utilisateur via la Vue
## Membres du groupe
Les personnes suivantes ont contribué à ce projet en ajoutant des fonctionnalités et en améliorant la documentation :
- [Ons Mestaoui](https://github.com/ons-mestaoui) : Responsable des offres et candidatures
- [Outheila Taamali](https://github.com/outh17) : Responsable des formations et recommandations
- [Imen Ben Jbara](https://github.com/imeeeeen) : Responsable des CV et analyse IA
- Rayen Taiba : Responsable des utilisateurs et accès sécurisé
- [Med Amine Belloumi](https://github.com/AmineBelloumi) : Responsable du portfolio et réalisations
## Contexte académique
 Developed as part of Web Technologies Project at **Esprit School of Engineering – Tunisia** PW – 2A | 2025–2026
## Démarrage
### installation
[https://github.com/ons-mestaoui/Esprit-PW-2A22-2526-APTUS.git](https://github.com/ons-mestaoui/Esprit-PW-2A22-2526-APTUS.git)
```bash 
git clone https://github.com/ons-mestaoui/Esprit-PW-2A22-2526-APTUS.git
cd Esprit-PW-2A22-2526-APTUS

