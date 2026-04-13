-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: aptus
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `administrateur`
--

DROP TABLE IF EXISTS `administrateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrateur` (
  `id_admin` int(11) NOT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_admin`),
  CONSTRAINT `fk_admin` FOREIGN KEY (`id_admin`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidat`
--

DROP TABLE IF EXISTS `candidat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidat` (
  `id_candidat` int(11) NOT NULL,
  `competences` text DEFAULT NULL,
  `niveauEtudes` varchar(100) DEFAULT NULL,
  `niveau` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_candidat`),
  CONSTRAINT `fk_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `candidatures`
--

DROP TABLE IF EXISTS `candidatures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `candidatures` (
  `id_candidature` int(11) NOT NULL AUTO_INCREMENT,
  `id_candidat` int(11) DEFAULT NULL,
  `id_offre` int(11) DEFAULT NULL,
  `reponses_ques` longtext DEFAULT NULL,
  `cv__cand` mediumblob DEFAULT NULL,
  `note` float DEFAULT NULL,
  `statut` enum('en_attente','accepte','refuse') DEFAULT NULL,
  PRIMARY KEY (`id_candidature`),
  UNIQUE KEY `unique_candidature` (`id_candidat`,`id_offre`),
  KEY `fk_candidature_offre` (`id_offre`),
  CONSTRAINT `fk_candidature_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  CONSTRAINT `fk_candidature_offre` FOREIGN KEY (`id_offre`) REFERENCES `offreemploi` (`id_offre`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cv`
--

DROP TABLE IF EXISTS `cv`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cv` (
  `id_cv` int(11) NOT NULL AUTO_INCREMENT,
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
  `dateMiseAJour` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cv`),
  KEY `fk_cv_candidat` (`id_candidat`),
  KEY `fk_cv_template` (`id_template`),
  CONSTRAINT `fk_cv_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  CONSTRAINT `fk_cv_template` FOREIGN KEY (`id_template`) REFERENCES `template` (`id_template`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `donnee_marche`
--

DROP TABLE IF EXISTS `donnee_marche`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `donnee_marche` (
  `id_donnee` int(11) NOT NULL AUTO_INCREMENT,
  `id_rapport_marche` int(11) DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `competence` varchar(255) DEFAULT NULL,
  `salaire_min` float DEFAULT NULL,
  `salaire_max` float DEFAULT NULL,
  `salaire_moyen` float DEFAULT NULL,
  `demande` int(11) DEFAULT NULL,
  `date_collecte` date DEFAULT NULL,
  PRIMARY KEY (`id_donnee`),
  KEY `fk_donnee_rapport` (`id_rapport_marche`),
  CONSTRAINT `fk_donnee_rapport` FOREIGN KEY (`id_rapport_marche`) REFERENCES `rapport_marche` (`id_rapport_marche`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `entreprise`
--

DROP TABLE IF EXISTS `entreprise`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `entreprise` (
  `id_entreprise` int(11) NOT NULL,
  `secteur` varchar(100) DEFAULT NULL,
  `siret` varchar(14) DEFAULT NULL,
  `raisonSociale` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_entreprise`),
  CONSTRAINT `fk_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `formation`
--

DROP TABLE IF EXISTS `formation`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `formation` (
  `id_formation` int(11) NOT NULL AUTO_INCREMENT,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `niveau` enum('D├®butant','Interm├®diaire','Avanc├®') DEFAULT NULL,
  `date_formation` datetime DEFAULT NULL,
  `image_base64` longtext DEFAULT NULL,
  `id_tuteur` int(11) DEFAULT NULL,
  `is_online` tinyint(1) DEFAULT 0,
  `lien_api_room` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_formation`),
  KEY `fk_formation_tuteur` (`id_tuteur`),
  CONSTRAINT `fk_formation_tuteur` FOREIGN KEY (`id_tuteur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `inscription`
--

DROP TABLE IF EXISTS `inscription`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `inscription` (
  `id_inscription` int(11) NOT NULL AUTO_INCREMENT,
  `id_candidat` int(11) DEFAULT NULL,
  `id_formation` int(11) DEFAULT NULL,
  `date_inscription` datetime DEFAULT current_timestamp(),
  `statut` enum('En attente','En cours','Termin├®e') DEFAULT 'En attente',
  `progression` int(11) DEFAULT 0,
  PRIMARY KEY (`id_inscription`),
  KEY `fk_inscription_candidat` (`id_candidat`),
  KEY `fk_inscription_formation` (`id_formation`),
  CONSTRAINT `fk_inscription_candidat` FOREIGN KEY (`id_candidat`) REFERENCES `candidat` (`id_candidat`) ON DELETE CASCADE,
  CONSTRAINT `fk_inscription_formation` FOREIGN KEY (`id_formation`) REFERENCES `formation` (`id_formation`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `offreemploi`
--

DROP TABLE IF EXISTS `offreemploi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `offreemploi` (
  `id_offre` int(11) NOT NULL AUTO_INCREMENT,
  `id_entreprise` int(11) NOT NULL,
  `titre` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `competences_requises` text DEFAULT NULL,
  `salaire` float DEFAULT NULL,
  `date_expir` date DEFAULT NULL,
  `domaine` varchar(255) DEFAULT NULL,
  `experience_requise` varchar(255) DEFAULT NULL,
  `date_publication` date DEFAULT NULL,
  `question` varchar(200) DEFAULT NULL,
  PRIMARY KEY (`id_offre`),
  KEY `fk_offre_entreprise` (`id_entreprise`),
  CONSTRAINT `fk_offre_entreprise` FOREIGN KEY (`id_entreprise`) REFERENCES `entreprise` (`id_entreprise`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `profil`
--

DROP TABLE IF EXISTS `profil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `profil` (
  `id_profil` int(11) NOT NULL AUTO_INCREMENT,
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
  `dateMiseAJour` datetime DEFAULT NULL,
  PRIMARY KEY (`id_profil`),
  KEY `fk_profil` (`id_utilisateur`),
  CONSTRAINT `fk_profil` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapport_ia`
--

DROP TABLE IF EXISTS `rapport_ia`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rapport_ia` (
  `id_rapport_ia` int(11) NOT NULL AUTO_INCREMENT,
  `id_cv` int(11) DEFAULT NULL,
  `scoreGlobal` int(11) DEFAULT NULL,
  `pointsForts` longtext DEFAULT NULL,
  `pointsFaibles` longtext DEFAULT NULL,
  `sectionsManquantes` longtext DEFAULT NULL,
  `suggestions` longtext DEFAULT NULL,
  `dateAnalyse` datetime DEFAULT NULL,
  PRIMARY KEY (`id_rapport_ia`),
  KEY `fk_rapport_ia_cv` (`id_cv`),
  CONSTRAINT `fk_rapport_ia_cv` FOREIGN KEY (`id_cv`) REFERENCES `cv` (`id_cv`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `rapport_marche`
--

DROP TABLE IF EXISTS `rapport_marche`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `rapport_marche` (
  `id_rapport_marche` int(11) NOT NULL AUTO_INCREMENT,
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
  `auteur` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_rapport_marche`),
  KEY `fk_rapport_admin` (`id_admin`),
  CONSTRAINT `fk_rapport_admin` FOREIGN KEY (`id_admin`) REFERENCES `administrateur` (`id_admin`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `template`
--

DROP TABLE IF EXISTS `template`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `template` (
  `id_template` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `urlMiniature` varchar(512) DEFAULT NULL,
  `structureHtml` text DEFAULT NULL,
  `estPremium` tinyint(1) DEFAULT NULL,
  `dateCreation` datetime DEFAULT NULL,
  PRIMARY KEY (`id_template`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `utilisateur`
--

DROP TABLE IF EXISTS `utilisateur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `utilisateur` (
  `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) DEFAULT NULL,
  `prenom` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `motDePasse` varchar(255) DEFAULT NULL,
  `role` enum('Candidat','Entreprise','Admin','Tuteur') DEFAULT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_utilisateur`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-04-12 23:24:00
