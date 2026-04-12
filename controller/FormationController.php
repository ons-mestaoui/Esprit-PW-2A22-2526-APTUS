<?php
include_once __DIR__ . '/../config.php';

class FormationController
{

    public function listerFormations()
    {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM Formation");
            return $liste;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
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

    public function addFormation($formation)
    {
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
                'lien_api_room' => $formation->getLienApiRoom()
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
                'lien_api_room' => $formation->getLienApiRoom()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getFormationById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM Formation WHERE id_formation = :id");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
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
}