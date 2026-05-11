<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Entreprise.php';

class EntrepriseC {
    public function afficherEntreprises() {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM entreprise");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getEntrepriseById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM entreprise WHERE id_entreprise = :id");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addEntreprise($entreprise) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("INSERT INTO entreprise (id_entreprise, secteur, siret, raisonSociale, taille, anneeFondation) 
                                   VALUES (:id, :secteur, :siret, :rs, :taille, :annee)");
            $query->execute([
                'id' => $entreprise->getIdEntreprise(),
                'secteur' => $entreprise->getSecteur(),
                'siret' => $entreprise->getSiret(),
                'rs' => $entreprise->getRaisonSociale(),
                'taille' => $entreprise->getTaille(),
                'annee' => $entreprise->getAnneeFondation()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateEntreprise($entreprise, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE entreprise SET 
                secteur = :secteur, siret = :siret, raisonSociale = :rs, taille = :taille, anneeFondation = :annee 
                WHERE id_entreprise = :id");
            $query->execute([
                'secteur' => $entreprise->getSecteur(),
                'siret' => $entreprise->getSiret(),
                'rs' => $entreprise->getRaisonSociale(),
                'taille' => $entreprise->getTaille(),
                'annee' => $entreprise->getAnneeFondation(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteEntreprise($id) {
        $db = config::getConnexion();
        try {
            $db->prepare("DELETE FROM entreprise WHERE id_entreprise = :id")->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
