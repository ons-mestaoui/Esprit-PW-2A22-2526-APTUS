<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Candidat.php';

class CandidatC {
    public function afficherCandidats() {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM candidat");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getCandidatById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM candidat WHERE id_candidat = :id");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addCandidat($candidat) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("INSERT INTO candidat (id_candidat, competences, niveauEtudes, niveau) 
                                   VALUES (:id, :comp, :etudes, :niveau)");
            $query->execute([
                'id' => $candidat->getIdCandidat(),
                'comp' => $candidat->getCompetences(),
                'etudes' => $candidat->getNiveauEtudes(),
                'niveau' => $candidat->getNiveau()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateCandidat($candidat, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE candidat SET 
                competences = :comp, niveauEtudes = :etudes, niveau = :niveau 
                WHERE id_candidat = :id");
            $query->execute([
                'comp' => $candidat->getCompetences(),
                'etudes' => $candidat->getNiveauEtudes(),
                'niveau' => $candidat->getNiveau(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteCandidat($id) {
        $db = config::getConnexion();
        try {
            $db->prepare("DELETE FROM candidat WHERE id_candidat = :id")->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
