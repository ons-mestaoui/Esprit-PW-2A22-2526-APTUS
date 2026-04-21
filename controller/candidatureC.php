<?php
require_once __DIR__ . '/../config.php';

class candidatureC {

    // Ajouter une candidature
    public function addCandidature($candidature) {
        $sql = "INSERT INTO candidatures 
            (id_candidat, id_offre, nom, prenom, email, reponses_ques, cv__cand, date_candidature) 
            VALUES (:id_candidat, :id_offre, :nom, :prenom, :email, :reponses_ques, :cv__cand, :date_candidature)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_candidat' => $candidature->getIdCandidat(),
                'id_offre' => $candidature->getIdOffre(),
                'nom' => $candidature->getNom(),
                'prenom' => $candidature->getPrenom(),
                'email' => $candidature->getEmail(),
                'reponses_ques' => $candidature->getReponsesQues(),
                'cv__cand' => $candidature->getCvCand(),
                'date_candidature' => $candidature->getDateCandidature(),
              
            ]);
        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Afficher (récupérer) toutes les candidatures (avec titre de l'offre grâce à la clé étrangère)
    public function afficherCandidatures() {
        $sql = "SELECT c.*, o.titre as titre_offre 
                FROM candidatures c 
                LEFT JOIN offreemploi o ON c.id_offre = o.id_offre";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Supprimer une candidature
    public function deleteCandidature($id_candidature) {
        $sql = "DELETE FROM candidatures WHERE id_candidature = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id_candidature);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}
?>
