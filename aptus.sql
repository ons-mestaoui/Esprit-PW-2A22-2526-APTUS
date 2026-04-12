-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : dim. 12 avr. 2026 à 19:36
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `aptus`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateur`
--

CREATE TABLE `administrateur` (
  `id_admin` int(11) NOT NULL,
  `niveau` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `candidat`
--

CREATE TABLE `candidat` (
  `id_candidat` int(11) NOT NULL,
  `competences` text DEFAULT NULL,
  `niveauEtudes` varchar(100) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `candidatures`
--

CREATE TABLE `candidatures` (
  `id_candidature` int(11) NOT NULL,
  `id_candidat` int(11) DEFAULT NULL,
  `id_offre` int(11) DEFAULT NULL,
  `reponses_ques` longtext DEFAULT NULL,
  `cv__cand` mediumblob DEFAULT NULL,
  `note` float DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `cv`
--

CREATE TABLE `cv` (
  `id_cv` int(11) NOT NULL,
  `id_candidat` int(11) DEFAULT NULL,
  `id_template` int(11) DEFAULT NULL,
  `nomDocument` varchar(255) DEFAULT NULL,
  `nomComplet` varchar(255) DEFAULT NULL,
  `titrePoste` varchar(255) DEFAULT NULL,
  `resume` text DEFAULT NULL,
  `infoContact` longtext DEFAULT NULL,
  `experience` longtext DEFAULT NULL,
  `formation` longtext DEFAULT NULL,
  `competences` longtext DEFAULT NULL,
  `langues` longtext DEFAULT NULL,
  `urlPhoto` varchar(512) DEFAULT NULL,
  `couleurTheme` varchar(50) DEFAULT NULL,
  `statut` varchar(50) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL,
  `dateMiseAJour` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `donnee_marche`
--

CREATE TABLE `donnee_marche` (
  `id_donnee` int(11) NOT NULL,
  `id_rapport_marche` int(11) DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `competence` varchar(255) DEFAULT NULL,
  `salaire_min` float DEFAULT NULL,
  `salaire_max` float DEFAULT NULL,
  `salaire_moyen` float DEFAULT NULL,
  `demande` int(11) DEFAULT NULL,
  `date_collecte` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `entreprise`
--

CREATE TABLE `entreprise` (
  `id_entreprise` int(11) NOT NULL,
  `secteur` varchar(100) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `raisonSociale` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `formation`
--

CREATE TABLE `formation` (
  `id_formation` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `niveau` enum('Débutant','Intermédiaire','Avancé') DEFAULT NULL,
  `date_formation` datetime DEFAULT NULL,
  `image_base64` longtext DEFAULT NULL,
  `id_tuteur` int(11) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `lien_api_room` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `inscription`
--

CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL,
  `id_candidat` int(11) DEFAULT NULL,
  `id_formation` int(11) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `statut` enum('En attente','En cours','Terminée') DEFAULT 'En attente',
  `progression` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `offreemploi`
--

CREATE TABLE `offreemploi` (
  `id_offre` int(11) NOT NULL,
  `id_entreprise` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `competences_requises` text DEFAULT NULL,
  `salaire` float DEFAULT NULL,
  `date_expir` date DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `experience_requise` varchar(255) DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  `question` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `profil`
--

CREATE TABLE `profil` (
  `id_profil` int(11) NOT NULL,
  `id_utilisateur` int(11) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `adresse` varchar(255) DEFAULT NULL,
  `ville` varchar(100) DEFAULT NULL,
  `pays` varchar(100) DEFAULT NULL,
  `dateNaissance` date DEFAULT NULL,
  `linkedin` varchar(255) DEFAULT NULL,
  `siteWeb` varchar(255) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL,
  `dateMiseAJour` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_ia`
--

CREATE TABLE `rapport_ia` (
  `id_rapport_ia` int(11) NOT NULL,
  `id_cv` int(11) DEFAULT NULL,
  `scoreGlobal` int(11) DEFAULT NULL,
  `pointsForts` longtext DEFAULT NULL,
  `pointsFaibles` longtext DEFAULT NULL,
  `sectionsManquantes` longtext DEFAULT NULL,
  `suggestions` longtext DEFAULT NULL,
  `dateAnalyse` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rapport_marche`
--

CREATE TABLE `rapport_marche` (
  `id_rapport_marche` int(11) NOT NULL,
  `id_admin` int(11) DEFAULT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  `region` varchar(255) DEFAULT NULL,
  `secteur_principal` varchar(255) DEFAULT NULL,
  `salaire_moyen_global` float DEFAULT NULL,
  `salaire_min_global` float DEFAULT NULL,
  `salaire_max_global` float DEFAULT NULL,
  `tendance_generale` varchar(255) DEFAULT NULL,
  `niveau_demande_global` int(11) DEFAULT NULL,
  `nombre_donnees` int(11) DEFAULT NULL,
  `auteur` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `template`
--

CREATE TABLE `template` (
  `id_template` int(11) NOT NULL,
  `nom` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `urlMiniature` varchar(512) DEFAULT NULL,
  `structureHtml` text DEFAULT NULL,
  `estPremium` tinyint(1) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `motDePasse` varchar(255) DEFAULT NULL,
  `role` enum('Candidat','Entreprise','Admin','Tuteur') DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD PRIMARY KEY (`id_admin`);

--
-- Index pour la table `candidat`
--
ALTER TABLE `candidat`
  ADD PRIMARY KEY (`id_candidat`);

--
-- Index pour la table `candidatures`
--
ALTER TABLE `candidatures`
  ADD PRIMARY KEY (`id_candidature`),
  ADD UNIQUE KEY `unique_candidature` (`id_candidat`,`id_offre`),
  ADD KEY `fk_candidature_offre` (`id_offre`);

--
-- Index pour la table `cv`
--
ALTER TABLE `cv`
  ADD PRIMARY KEY (`id_cv`),
  ADD KEY `fk_cv_candidat` (`id_candidat`),
  ADD KEY `fk_cv_template` (`id_template`);

--
-- Index pour la table `donnee_marche`
--
ALTER TABLE `donnee_marche`
  ADD PRIMARY KEY (`id_donnee`),
  ADD KEY `fk_donnee_rapport` (`id_rapport_marche`);

--
-- Index pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD PRIMARY KEY (`id_entreprise`);

--
-- Index pour la table `formation`
--
ALTER TABLE `formation`
  ADD PRIMARY KEY (`id_formation`),
  ADD KEY `fk_formation_tuteur` (`id_tuteur`);

--
-- Index pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD PRIMARY KEY (`id_inscription`),
  ADD KEY `fk_inscription_candidat` (`id_candidat`),
  ADD KEY `fk_inscription_formation` (`id_formation`);

--
-- Index pour la table `offreemploi`
--
ALTER TABLE `offreemploi`
  ADD PRIMARY KEY (`id_offre`),
  ADD KEY `fk_offre_entreprise` (`id_entreprise`);

--
-- Index pour la table `profil`
--
ALTER TABLE `profil`
  ADD PRIMARY KEY (`id_profil`),
  ADD KEY `fk_profil` (`id_utilisateur`);

--
-- Index pour la table `rapport_ia`
--
ALTER TABLE `rapport_ia`
  ADD PRIMARY KEY (`id_rapport_ia`),
  ADD KEY `fk_rapport_ia_cv` (`id_cv`);

--
-- Index pour la table `rapport_marche`
--
ALTER TABLE `rapport_marche`
  ADD PRIMARY KEY (`id_rapport_marche`),
  ADD KEY `fk_rapport_admin` (`id_admin`);

--
-- Index pour la table `template`
--
ALTER TABLE `template`
  ADD PRIMARY KEY (`id_template`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `candidatures`
--
ALTER TABLE `candidatures`
  MODIFY `id_candidature` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `cv`
--
ALTER TABLE `cv`
  MODIFY `id_cv` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `donnee_marche`
--
ALTER TABLE `donnee_marche`
  MODIFY `id_donnee` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `formation`
--
ALTER TABLE `formation`
  MODIFY `id_formation` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `inscription`
--
ALTER TABLE `inscription`
  MODIFY `id_inscription` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `offreemploi`
--
ALTER TABLE `offreemploi`
  MODIFY `id_offre` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `profil`
--
ALTER TABLE `profil`
  MODIFY `id_profil` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rapport_ia`
--
ALTER TABLE `rapport_ia`
  MODIFY `id_rapport_ia` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rapport_marche`
--
ALTER TABLE `rapport_marche`
  MODIFY `id_rapport_marche` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `template`
--
ALTER TABLE `template`
  MODIFY `id_template` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `administrateur`
--
ALTER TABLE `administrateur`
  ADD CONSTRAINT `fk_admin` FOREIGN KEY (`id_admin`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `candidat`
--
ALTER TABLE `candidat`
  ADD CONSTRAINT `fk_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `candidatures`
--
ALTER TABLE `candidatures`
  ADD CONSTRAINT `fk_candidature_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_candidature_offre` FOREIGN KEY (`id_offre`) REFERENCES `offreemploi` (`id_offre`) ON DELETE CASCADE;

--
-- Contraintes pour la table `cv`
--
ALTER TABLE `cv`
  ADD CONSTRAINT `fk_cv_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_cv_template` FOREIGN KEY (`id_template`) REFERENCES `template` (`id_template`) ON DELETE SET NULL;

--
-- Contraintes pour la table `donnee_marche`
--
ALTER TABLE `donnee_marche`
  ADD CONSTRAINT `fk_donnee_rapport` FOREIGN KEY (`id_rapport_marche`) REFERENCES `rapport_marche` (`id_rapport_marche`) ON DELETE CASCADE;

--
-- Contraintes pour la table `entreprise`
--
ALTER TABLE `entreprise`
  ADD CONSTRAINT `fk_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `formation`
--
ALTER TABLE `formation`
  ADD CONSTRAINT `fk_formation_tuteur` FOREIGN KEY (`id_tuteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL;

--
-- Contraintes pour la table `inscription`
--
ALTER TABLE `inscription`
  ADD CONSTRAINT `fk_inscription_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_inscription_formation` FOREIGN KEY (`id_formation`) REFERENCES `formation` (`id_formation`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offreemploi`
--
ALTER TABLE `offreemploi`
  ADD CONSTRAINT `fk_offre_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`) ON DELETE CASCADE;

--
-- Contraintes pour la table `profil`
--
ALTER TABLE `profil`
  ADD CONSTRAINT `fk_profil` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rapport_ia`
--
ALTER TABLE `rapport_ia`
  ADD CONSTRAINT `fk_rapport_ia_cv` FOREIGN KEY (`id_cv`) REFERENCES `cv` (`id_cv`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rapport_marche`
--
ALTER TABLE `rapport_marche`
  ADD CONSTRAINT `fk_rapport_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrateur` (`id_admin`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
