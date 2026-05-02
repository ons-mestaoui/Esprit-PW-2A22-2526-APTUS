<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Tuteur.php';

class TuteurC {
    public function listerTuteurs() {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM tuteur");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getTuteurById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM tuteur WHERE id_tuteur = :id");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addTuteur($tuteur) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("INSERT INTO tuteur (id_tuteur, specialite, experience, biographie) 
                                   VALUES (:id, :spec, :exp, :bio)");
            $query->execute([
                'id' => $tuteur->getIdTuteur(),
                'spec' => $tuteur->getSpecialite(),
                'exp' => $tuteur->getExperience(),
                'bio' => $tuteur->getBiographie()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateTuteur($tuteur, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE tuteur SET 
                specialite = :spec, experience = :exp, biographie = :bio 
                WHERE id_tuteur = :id");
            $query->execute([
                'spec' => $tuteur->getSpecialite(),
                'exp' => $tuteur->getExperience(),
                'bio' => $tuteur->getBiographie(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateTuteurInfo($id, $spec, $exp, $bio) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE tuteur SET 
                specialite = :spec, experience = :exp, biographie = :bio 
                WHERE id_tuteur = :id");
            $query->execute([
                'spec' => $spec,
                'exp' => $exp,
                'bio' => $bio,
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteTuteur($id) {
        $db = config::getConnexion();
        try {
            $db->prepare("DELETE FROM tuteur WHERE id_tuteur = :id")->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
