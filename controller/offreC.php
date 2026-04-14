<?php
include '../../config.php';
class offreC{
    public function ajouterOffre($offre){
        $sql="INSERT INTO offreemploi (id_entreprise, titre, description, domaine, competences_requises, experience_requise, salaire, question, date_publication, date_expir, statut) VALUES (1, :titre, :description, :domaine, :competences_requises, :experience_requise, :salaire, :question, :date_publication, :date_expir, 'Actif')";
        $db = config::getConnexion();
        try{
            $query = $db->prepare($sql);
            $query->execute([
                'titre' => $offre->getTitre(),
                'description' => $offre->getDescription(),
                'domaine' => $offre->getDomaine(),
                'competences_requises' => $offre->getCompetencesRequises(),
                'experience_requise' => $offre->getExperienceRequise(),
                'salaire' => $offre->getSalaire(),
                'question' => $offre->getQuestion(),
                'date_publication' => $offre->getDatePublication(),
                'date_expir' => $offre->getDateExpir()
            ]);
        }catch (Exception $e){
            echo 'Erreur: '.$e->getMessage();
        }
    }
    public function afficherOffres($onlyActive = false){
        $db = config::getConnexion();
        try{
            // Auto-update du statut basé sur la date d'expiration pour simuler un CRON
            $db->exec("UPDATE offreemploi SET statut = 'Expiré' WHERE date_expir < CURDATE()");
            $db->exec("UPDATE offreemploi SET statut = 'Actif' WHERE date_expir >= CURDATE()");

            $sql = "SELECT * FROM offreemploi";
            if ($onlyActive) {
                $sql .= " WHERE statut = 'Actif'";
            }
            $sql .= " ORDER BY date_publication DESC, id_offre DESC";
            
            $liste = $db->query($sql);
            return $liste;
        }catch (Exception $e){
            die('Erreur: '.$e->getMessage());
        }
    }
    public function supprimerOffre($id_offre){
        $db = config::getConnexion();
        try{
            $req=$db->prepare("DELETE FROM offreemploi WHERE id_offre=:id_offre");
            $req->execute([
                'id_offre' => $id_offre
            ]);
        }
        catch (Exception $e){
            die('Erreur: '.$e->getMessage());
        }
    }
    public function modifierOffre($offre, $id_offre){
        $db = config::getConnexion();
        try{
            $query = $db->prepare("UPDATE offreemploi SET titre=:titre, description=:description, domaine=:domaine, competences_requises=:competences_requises, experience_requise=:experience_requise, salaire=:salaire, question=:question, date_publication=:date_publication, date_expir=:date_expir WHERE id_offre=:id_offre");
            $query->execute([
                'id_offre' => $id_offre,
                'titre' => $offre->getTitre(),
                'description' => $offre->getDescription(),
                'domaine' => $offre->getDomaine(),
                'competences_requises' => $offre->getCompetencesRequises(),
                'experience_requise' => $offre->getExperienceRequise(),
                'salaire' => $offre->getSalaire(),
                'question' => $offre->getQuestion(),
                'date_publication' => $offre->getDatePublication(),
                'date_expir' => $offre->getDateExpir()
            ]);
        }catch (Exception $e){
            echo 'Erreur: '.$e->getMessage();
        }
    }
    public function getOffreById($id_offre){
        $db = config::getConnexion();
        try{
            $query=$db->prepare("SELECT * from offreemploi where id_offre=:id_offre");
            $query->execute([
                'id_offre' => $id_offre
            ]);
            $offre=$query->fetch();
            return $offre;
        }
        catch (Exception $e){
            die('Erreur: '.$e->getMessage());
        }
    }
}
