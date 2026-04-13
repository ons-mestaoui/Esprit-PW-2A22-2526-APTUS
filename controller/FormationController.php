<?php
// FormationController : gère toute la logique métier liée aux formations
// C'est ici qu'on fait les contrôles de saisie (pas dans la vue)
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Formation.php';

class FormationController
{

    // Récupère toutes les formations avec le nom du tuteur (jointure)
    public function listerFormations()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query("
                SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                FROM Formation f 
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id
            ");
            return $liste;
        } catch (Exception $e) {
            try {
                $liste = $db->query("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN User u ON f.id_tuteur = u.id
                ");
                return $liste;
            } catch (Exception $e2) {
                throw new Exception('Erreur: ' . $e2->getMessage());
            }
        }
    }

    // Stats pour le dashboard admin (nb formations, inscrits, certificats, taux)
    public function getStatsGlobales()
    {
        $db = config::getConnexion();
        $stats = [
            'total_formations' => 0,
            'total_inscrits' => 0,
            'certificats' => 0,
            'taux_completion' => 0
        ];
        
        try {
            // Formations
            $resF = $db->query("SELECT COUNT(*) as c FROM Formation");
            $stats['total_formations'] = $resF->fetch()['c'];

            // Inscriptions
            $resI = $db->query("SELECT COUNT(*) as c FROM inscription");
            $stats['total_inscrits'] = $resI->fetch()['c'];

            // Certificats (Terminées)
            $resC = $db->query("SELECT COUNT(*) as c FROM inscription WHERE statut = 'Terminée' OR progression >= 100");
            $stats['certificats'] = $resC->fetch()['c'];

            // Taux de complétion
            if ($stats['total_inscrits'] > 0) {
                $stats['taux_completion'] = round(($stats['certificats'] / $stats['total_inscrits']) * 100);
            }
        } catch (Exception $e) {
            // En cas d'erreur de table (ex: Inscription au lieu de inscription)
            try {
                $resI = $db->query("SELECT COUNT(*) as c FROM Inscription");
                $stats['total_inscrits'] = $resI->fetch()['c'];
                
                $resC = $db->query("SELECT COUNT(*) as c FROM Inscription WHERE statut = 'Terminée' OR progression >= 100");
                $stats['certificats'] = $resC->fetch()['c'];

                if ($stats['total_inscrits'] > 0) {
                    $stats['taux_completion'] = round(($stats['certificats'] / $stats['total_inscrits']) * 100);
                }
            } catch (Exception $e2) {
                // Ignore if tables don't exist yet
            }
        }
        
        return $stats;
    }

    public function getTuteurs()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query("SELECT * FROM utilisateur WHERE role = 'Tuteur'");
            return $query->fetchAll();
        } catch (Exception $e) {
            try {
                $query = $db->query("SELECT * FROM User WHERE role = 'Tuteur'");
                return $query->fetchAll();
            } catch (Exception $e2) {
                return [];
            }
        }
    }

    // ============================================
    // CONTRÔLES DE SAISIE (validation côté serveur)
    // ============================================
    // On fait tout ici en PHP, pas en HTML (pas de 'required' dans les inputs)
    // Si un champ est invalide -> on lance une Exception que la vue va attraper
    private function validateFormation($formation)
    {
        // Titre : obligatoire, entre 5 et 100 caractères
        $titre = trim($formation->getTitre());
        if (empty($titre)) {
            throw new Exception("Le titre est obligatoire.");
        }
        if (strlen($titre) < 5 || strlen($titre) > 100) {
            throw new Exception("Le titre doit contenir entre 5 et 100 caractères.");
        }

        // Description : obligatoire, au moins 20 caractères
        // strip_tags() pour compter le vrai texte sans les balises HTML de Quill
        $descriptionText = trim(strip_tags($formation->getDescription()));
        if (empty($descriptionText)) {
            throw new Exception("La description est obligatoire.");
        }
        if (strlen($descriptionText) < 20) {
            throw new Exception("La description doit contenir au moins 20 caractères.");
        }

        // Domaine : obligatoire
        if (empty(trim($formation->getDomaine()))) {
            throw new Exception("Le domaine est obligatoire.");
        }

        // Niveau : obligatoire
        if (empty(trim($formation->getNiveau()))) {
            throw new Exception("Le niveau est obligatoire.");
        }

        // Date : obligatoire + interdiction de mettre une date passée
        if (empty($formation->getDateFormation())) {
            throw new Exception("La date de formation est obligatoire.");
        }
        if (strtotime($formation->getDateFormation()) < strtotime(date('Y-m-d'))) {
            throw new Exception("La date de formation ne peut pas être dans le passé.");
        }

        // Durée : optionnel, mais si rempli doit respecter le format "chiffre + unité"
        // Exemples valides : 10h, 2 jours, 30min
        $duree = trim($formation->getDuree());
        if (!empty($duree)) {
            if (!preg_match('/^\d+\s*[A-Za-z]+$/', $duree)) {
                throw new Exception("La durée doit avoir un format 'numérique + unité' (ex: 10h).");
            }
        }

        // Tuteur : obligatoire (on doit choisir qui anime la formation)
        if (empty($formation->getIdTuteur())) {
            throw new Exception("Vous devez sélectionner un tuteur.");
        }
        // NB: l'image n'est pas obligatoire
    }

    // Génère un lien Jitsi Meet automatiquement pour les formations en ligne
    private function generateJitsiLink($titre)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($titre));
        return "https://meet.jit.si/Aptus_" . $slug . "_" . uniqid();
    }

    // Ajout d'une formation : on valide d'abord, puis on insère en BDD
    public function addFormation($formation)
    {
        // Appel de validateFormation() -> si erreur, elle lance une Exception
        $this->validateFormation($formation);

        // Logique métier : Génération automatique du lien si online et vide
        $lien = $formation->getLienApiRoom();
        if ($formation->getIsOnline() == 1 && empty($lien)) {
            $lien = $this->generateJitsiLink($formation->getTitre());
            // On peut soit modifier l'objet, soit gérer cela ici. 
            // Pour rester simple, on va utiliser cette valeur dans l'execute.
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                INSERT INTO Formation 
                (titre, description, domaine, niveau, duree, date_formation, image_base64, id_tuteur, is_online, lien_api_room) 
                VALUES 
                (:titre, :description, :domaine, :niveau, :duree, :date_formation, :image_base64, :id_tuteur, :is_online, :lien_api_room)
            ");
            $query->execute([
                'titre' => $formation->getTitre(),
                'description' => $formation->getDescription(),
                'domaine' => $formation->getDomaine(),
                'niveau' => $formation->getNiveau(),
                'duree' => $formation->getDuree(),
                'date_formation' => $formation->getDateFormation(),
                'image_base64' => $formation->getImageBase64(),
                'id_tuteur' => $formation->getIdTuteur(),
                'is_online' => $formation->getIsOnline(),
                'lien_api_room' => $lien
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Erreur SQL: ' . $e->getMessage());
        }
    }

    // Suppression : on vérifie d'abord qu'il n'y a pas d'inscrits actifs
    public function deleteFormation($id)
    {
        $db = config::getConnexion();
        try {
            $checkF = $db->prepare("SELECT statut FROM Formation WHERE id_formation = :id");
            $checkF->execute(['id' => $id]);
            $statut_f = $checkF->fetchColumn();

            // Vérifier s'il y a des inscrits
            $nb_inscrits = 0;
            try {
                $check = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = :id");
                $check->execute(['id' => $id]);
                $nb_inscrits = $check->fetchColumn();
            } catch (Exception $e) {
                $check = $db->prepare("SELECT COUNT(*) FROM Inscription WHERE id_formation = :id");
                $check->execute(['id' => $id]);
                $nb_inscrits = $check->fetchColumn();
            }

            if ($nb_inscrits > 0 && $statut_f !== 'annulée') {
                throw new Exception("Impossible de supprimer une formation active avec des inscrits. Veuillez d'abord l'annuler.");
            }

            $query = $db->prepare("DELETE FROM Formation WHERE id_formation = :id");
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateFormation($formation, $id)
    {
        $this->validateFormation($formation);

        $lien = $formation->getLienApiRoom();
        if ($formation->getIsOnline() == 1 && empty($lien)) {
            $lien = $this->generateJitsiLink($formation->getTitre());
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                UPDATE Formation SET 
                titre=:titre, description=:description, domaine=:domaine, niveau=:niveau, 
                duree=:duree, date_formation=:date_formation, image_base64=:image_base64, 
                id_tuteur=:id_tuteur, is_online=:is_online, lien_api_room=:lien_api_room 
                WHERE id_formation=:id
            ");
            $query->execute([
                'id' => $id,
                'titre' => $formation->getTitre(),
                'description' => $formation->getDescription(),
                'domaine' => $formation->getDomaine(),
                'niveau' => $formation->getNiveau(),
                'duree' => $formation->getDuree(),
                'date_formation' => $formation->getDateFormation(),
                'image_base64' => $formation->getImageBase64(),
                'id_tuteur' => $formation->getIdTuteur(),
                'is_online' => $formation->getIsOnline(),
                'lien_api_room' => $lien
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur SQL: ' . $e->getMessage());
        }
    }

    // Récupère une formation par son ID (pour la page d'édition)
    public function getFormationById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                FROM Formation f 
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id 
                WHERE f.id_formation = :id
            ");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            try {
                $query = $db->prepare("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN User u ON f.id_tuteur = u.id 
                    WHERE f.id_formation = :id
                ");
                $query->execute(['id' => $id]);
                return $query->fetch();
            } catch (Exception $e2) {
                throw new Exception('Erreur: ' . $e2->getMessage());
            }
        }
    }

    // Fonction spécifique pour ton scénario de Room Jitsi
    public function updateLienRoom($id, $lien)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE Formation SET lien_api_room = :lien WHERE id_formation = :id");
            $query->execute(['id' => $id, 'lien' => $lien]);
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    // 1. Pour la liste à droite du calendrier
    public function getFormationsByTuteur($id_tuteur) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                SELECT f.*, 
                (SELECT COUNT(*) FROM Inscription WHERE id_formation = f.id_formation) as nb_inscrits
                FROM Formation f 
                WHERE f.id_tuteur = :id
                ORDER BY f.date_formation ASC
            ");
            $query->execute(['id' => $id_tuteur]);
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    // 2. Pour le flux JSON du calendrier (FullCalendar)
    public function getCalendarEventsJSON($id_tuteur) {
        $formations = $this->getFormationsByTuteur($id_tuteur);
        $events = [];
        foreach($formations as $f) {
            $events[] = [
                'title' => $f['titre'],
                'start' => $f['date_formation'],
                'backgroundColor' => ($f['is_online']) ? '#3498db' : '#2ecc71',
                'extendedProps' => [
                    'is_online' => (bool)$f['is_online'],
                    'nb_inscrits' => $f['nb_inscrits'],
                    'description' => $f['description'],
                    'lien_room' => $f['lien_api_room']
                ]
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($events);
    }

    // Annulation d'une formation par l'admin
    // -> passe la formation ET toutes ses inscriptions en statut 'annulée'
    public function annuler(int $id)
    {
        // Contrainte de sécurité : vérification du rôle (simulée via session)
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
            header('Location: formations_admin.php');
            exit();
        }

        try {
            $f = $this->getFormationById($id);
            if (!$f) {
                throw new Exception("Formation introuvable.");
            }

            // Contrainte : on ne peut pas annuler une formation déjà annulée
            if ((isset($f['statut']) && $f['statut'] === 'annulée')) {
                throw new Exception("Cette formation est déjà annulée.");
            }

            // Transaction : on annule la formation ET ses inscriptions en même temps
            // Si l'une des deux requêtes échoue -> rollback complet
            $db = config::getConnexion();
            $db->beginTransaction();

            $stmtF = $db->prepare("UPDATE Formation SET statut = 'annulée' WHERE id_formation = ?");
            if (!$stmtF->execute([$id])) {
                throw new Exception("Erreur lors de l'annulation de la formation.");
            }

            try {
                $stmtI = $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_formation = ?");
                $stmtI->execute([$id]);
            } catch (Exception $e) {
                $stmtI = $db->prepare("UPDATE Inscription SET statut = 'annulée' WHERE id_formation = ?");
                $stmtI->execute([$id]);
            }

            $db->commit();
            $_SESSION['flash_success'] = "La formation et ses inscriptions ont été annulées avec succès.";
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: formations_admin.php');
        exit();
    }
}