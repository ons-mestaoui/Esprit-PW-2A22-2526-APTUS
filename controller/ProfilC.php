<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Profil.php';

class ProfilC {
    public function afficherProfils() {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM profil");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getProfilByIdUtilisateur($id_utilisateur) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM profil WHERE id_utilisateur = :id");
            $query->execute(['id' => $id_utilisateur]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addProfil($profil) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("INSERT INTO profil (id_utilisateur, photo, bio, adresse, ville, pays, dateNaissance, linkedin, siteWeb, dateCreation, dateMiseAJour) 
                                   VALUES (:id, :photo, :bio, :adresse, :ville, :pays, :dn, :linkedin, :site, NOW(), NOW())");
            $query->execute([
                'id' => $profil->getIdUtilisateur(),
                'photo' => $profil->getPhoto(),
                'bio' => $profil->getBio(),
                'adresse' => $profil->getAdresse(),
                'ville' => $profil->getVille(),
                'pays' => $profil->getPays(),
                'dn' => $profil->getDateNaissance(),
                'linkedin' => $profil->getLinkedin(),
                'site' => $profil->getSiteWeb()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateProfil($profil, $id_utilisateur) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE profil SET 
                photo = :photo, bio = :bio, adresse = :adresse, ville = :ville, pays = :pays, 
                dateNaissance = :dn, linkedin = :linkedin, siteWeb = :site, dateMiseAJour = NOW() 
                WHERE id_utilisateur = :id");
            $query->execute([
                'photo' => $profil->getPhoto(),
                'bio' => $profil->getBio(),
                'adresse' => $profil->getAdresse(),
                'ville' => $profil->getVille(),
                'pays' => $profil->getPays(),
                'dn' => $profil->getDateNaissance(),
                'linkedin' => $profil->getLinkedin(),
                'site' => $profil->getSiteWeb(),
                'id' => $id_utilisateur
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteProfil($id_utilisateur) {
        $db = config::getConnexion();
        try {
            $db->prepare("DELETE FROM profil WHERE id_utilisateur = :id")->execute(['id' => $id_utilisateur]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
