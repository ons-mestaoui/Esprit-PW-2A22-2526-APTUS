<?php
include_once __DIR__ . '/../config.php';

class FormationController
{

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
                die('Erreur: ' . $e2->getMessage());
            }
        }
    }

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

    private function validateFormation($formation)
    {
        if (empty($formation->getTitre())) {
            throw new Exception("Le titre est obligatoire.");
        }
        if (empty($formation->getDescription())) {
            throw new Exception("La description est obligatoire.");
        }
        if (strtotime($formation->getDateFormation()) < strtotime(date('Y-m-d'))) {
            throw new Exception("La date de formation ne peut pas être dans le passé.");
        }
    }

    private function generateJitsiLink($titre)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($titre));
        return "https://meet.jit.si/Aptus_" . $slug . "_" . uniqid();
    }

    public function addFormation($formation)
    {
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
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteFormation($id)
    {
        $db = config::getConnexion();
        try {
            // Vérifier s'il y a des inscrits
            try {
                $check = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = :id");
                $check->execute(['id' => $id]);
                if ($check->fetchColumn() > 0) {
                    return false; // Impossible de supprimer
                }
            } catch (Exception $e) {
                // Table might be named Inscription with capital I
                $check = $db->prepare("SELECT COUNT(*) FROM Inscription WHERE id_formation = :id");
                $check->execute(['id' => $id]);
                if ($check->fetchColumn() > 0) {
                    return false;
                }
            }

            $query = $db->prepare("DELETE FROM Formation WHERE id_formation = :id");
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
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
            die('Erreur: ' . $e->getMessage());
        }
    }

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
                die('Erreur: ' . $e2->getMessage());
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
            die('Erreur: ' . $e->getMessage());
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
            die('Erreur: ' . $e->getMessage());
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
}