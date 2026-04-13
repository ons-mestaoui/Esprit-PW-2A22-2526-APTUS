<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/model/RapportMarche.php';
require_once dirname(__DIR__) . '/model/DonneeMarche.php';

class VeilleC
{
    private $db;

    public function __construct()
    {
        $this->db = config::getConnexion();
    }

    // --- RAPPORT MARCHE METHODS ---

    public function ajouterRapport($rapport)
    {
        try {
            $sql = "INSERT INTO rapport_marche (id_admin, titre, description, date_publication, region, secteur_principal, salaire_moyen_global, salaire_min_global, salaire_max_global, tendance_generale, niveau_demande_global, nombre_donnees, auteur) 
                    VALUES (:id_admin, :titre, :description, :date_publication, :region, :secteur_principal, :salaire_moyen_global, :salaire_min_global, :salaire_max_global, :tendance_generale, :niveau_demande_global, :nombre_donnees, :auteur)";
            
            $req = $this->db->prepare($sql);
            
            $req->bindValue(':id_admin', $rapport->getIdAdmin());
            $req->bindValue(':titre', $rapport->getTitre());
            $req->bindValue(':description', $rapport->getDescription());
            $req->bindValue(':date_publication', $rapport->getDatePublication());
            $req->bindValue(':region', $rapport->getRegion());
            $req->bindValue(':secteur_principal', $rapport->getSecteurPrincipal());
            $req->bindValue(':salaire_moyen_global', $rapport->getSalaireMoyenGlobal());
            $req->bindValue(':salaire_min_global', $rapport->getSalaireMinGlobal());
            $req->bindValue(':salaire_max_global', $rapport->getSalaireMaxGlobal());
            $req->bindValue(':tendance_generale', $rapport->getTendanceGenerale());
            $req->bindValue(':niveau_demande_global', $rapport->getNiveauDemandeGlobal());
            $req->bindValue(':nombre_donnees', $rapport->getNombreDonnees());
            $req->bindValue(':auteur', $rapport->getAuteur());
            
            $req->execute();
            return $this->db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function afficherRapports()
    {
        $sql = "SELECT * FROM rapport_marche ORDER BY date_publication DESC";
        try {
            $liste = $this->db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function recupererRapport($id)
    {
        $sql = "SELECT * FROM rapport_marche WHERE id_rapport_marche = :id";
        try {
            $req = $this->db->prepare($sql);
            $req->bindParam(':id', $id);
            $req->execute();
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function modifierRapport($rapport)
    {
        try {
            $sql = "UPDATE rapport_marche SET 
                    titre = :titre, description = :description, region = :region, secteur_principal = :secteur_principal, 
                    salaire_moyen_global = :salaire_moyen_global, salaire_min_global = :salaire_min_global, salaire_max_global = :salaire_max_global, 
                    tendance_generale = :tendance_generale, niveau_demande_global = :niveau_demande_global, nombre_donnees = :nombre_donnees, auteur = :auteur 
                    WHERE id_rapport_marche = :id_rapport_marche";
            
            $req = $this->db->prepare($sql);
            
            $req->bindValue(':id_rapport_marche', $rapport->getIdRapportMarche());
            $req->bindValue(':titre', $rapport->getTitre());
            $req->bindValue(':description', $rapport->getDescription());
            $req->bindValue(':region', $rapport->getRegion());
            $req->bindValue(':secteur_principal', $rapport->getSecteurPrincipal());
            $req->bindValue(':salaire_moyen_global', $rapport->getSalaireMoyenGlobal());
            $req->bindValue(':salaire_min_global', $rapport->getSalaireMinGlobal());
            $req->bindValue(':salaire_max_global', $rapport->getSalaireMaxGlobal());
            $req->bindValue(':tendance_generale', $rapport->getTendanceGenerale());
            $req->bindValue(':niveau_demande_global', $rapport->getNiveauDemandeGlobal());
            $req->bindValue(':nombre_donnees', $rapport->getNombreDonnees());
            $req->bindValue(':auteur', $rapport->getAuteur());
            
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerRapport($id)
    {
        try {
            $this->delierToutesDonneesDUnRapport($id);
            $sql = "DELETE FROM rapport_marche WHERE id_rapport_marche = :id";
            $req = $this->db->prepare($sql);
            $req->bindParam(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // --- DONNEE MARCHE METHODS ---

    public function ajouterDonnee($donnee)
    {
        try {
            $sql = "INSERT INTO donnee_marche (domaine, competence, salaire_min, salaire_max, salaire_moyen, demande, date_collecte) 
                    VALUES (:domaine, :competence, :salaire_min, :salaire_max, :salaire_moyen, :demande, :date_collecte)";
            
            $req = $this->db->prepare($sql);
            
            $req->bindValue(':domaine', $donnee->getDomaine());
            $req->bindValue(':competence', $donnee->getCompetence());
            $req->bindValue(':salaire_min', $donnee->getSalaireMin());
            $req->bindValue(':salaire_max', $donnee->getSalaireMax());
            $req->bindValue(':salaire_moyen', $donnee->getSalaireMoyen());
            $req->bindValue(':demande', $donnee->getDemande());
            $req->bindValue(':date_collecte', $donnee->getDateCollecte());
            
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function afficherToutesDonnees()
    {
        $sql = "SELECT * FROM donnee_marche ORDER BY date_collecte DESC";
        try {
            $liste = $this->db->query($sql);
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function recupererDonneesParRapport($id_rapport_marche)
    {
        // M2M JOIN logic
        $sql = "SELECT d.* FROM donnee_marche d
                INNER JOIN liaison_rapport_donnee l ON d.id_donnee = l.id_donnee
                WHERE l.id_rapport_marche = :id_rapport";
        try {
            $req = $this->db->prepare($sql);
            $req->bindParam(':id_rapport', $id_rapport_marche);
            $req->execute();
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
    
    public function recupererDonnee($id_donnee)
    {
        $sql = "SELECT * FROM donnee_marche WHERE id_donnee = :id_donnee";
        try {
            $req = $this->db->prepare($sql);
            $req->bindParam(':id_donnee', $id_donnee);
            $req->execute();
            return $req->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function modifierDonnee($donnee)
    {
        try {
            $sql = "UPDATE donnee_marche SET 
                    domaine = :domaine, competence = :competence, salaire_min = :salaire_min, 
                    salaire_max = :salaire_max, salaire_moyen = :salaire_moyen, demande = :demande, date_collecte = :date_collecte
                    WHERE id_donnee = :id_donnee";
            
            $req = $this->db->prepare($sql);
            
            $req->bindValue(':id_donnee', $donnee->getIdDonnee());
            $req->bindValue(':domaine', $donnee->getDomaine());
            $req->bindValue(':competence', $donnee->getCompetence());
            $req->bindValue(':salaire_min', $donnee->getSalaireMin());
            $req->bindValue(':salaire_max', $donnee->getSalaireMax());
            $req->bindValue(':salaire_moyen', $donnee->getSalaireMoyen());
            $req->bindValue(':demande', $donnee->getDemande());
            $req->bindValue(':date_collecte', $donnee->getDateCollecte());
            
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function supprimerDonnee($id)
    {
        try {
            $sql = "DELETE FROM donnee_marche WHERE id_donnee = :id";
            $req = $this->db->prepare($sql);
            $req->bindParam(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // A helper method to get raw links mapping array
    public function getMapLiaisons()
    {
        $sql = "SELECT id_rapport_marche, id_donnee FROM liaison_rapport_donnee";
        try {
            $liste = $this->db->query($sql);
            $map = []; 
            // format: map[id_donnee] = [id_rapport1, id_rapport2...]
            foreach($liste->fetchAll() as $row) {
                $map[$row['id_donnee']][] = $row['id_rapport_marche'];
            }
            return $map;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function lierDonneesAuRapport($liste_ids, $id_rapport)
    {
        if (empty($liste_ids)) return;
        
        try {
            // M2M logic
            $sql = "INSERT IGNORE INTO liaison_rapport_donnee (id_rapport_marche, id_donnee) VALUES ";
            $values = [];
            $params = [];
            foreach($liste_ids as $id_donnee) {
                $values[] = "(?, ?)";
                $params[] = $id_rapport;
                $params[] = $id_donnee;
            }
            $sql .= implode(", ", $values);
            
            $req = $this->db->prepare($sql);
            $req->execute($params);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function delierToutesDonneesDUnRapport($id_rapport)
    {
        try {
            $sql = "DELETE FROM liaison_rapport_donnee WHERE id_rapport_marche = :id_rapport";
            $req = $this->db->prepare($sql);
            $req->bindParam(':id_rapport', $id_rapport);
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
