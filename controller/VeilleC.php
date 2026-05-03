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
            $sql = "INSERT INTO rapport_marche (id_admin, titre, description, date_publication, region, secteur_principal, salaire_moyen_global, salaire_min_global, salaire_max_global, tendance_generale, niveau_demande_global, nombre_donnees, auteur, contenu_detaille, image_couverture, vues) 
                    VALUES (:id_admin, :titre, :description, :date_publication, :region, :secteur_principal, :salaire_moyen_global, :salaire_min_global, :salaire_max_global, :tendance_generale, :niveau_demande_global, :nombre_donnees, :auteur, :contenu_detaille, :image_couverture, :vues)";
            
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
            $req->bindValue(':contenu_detaille', $rapport->getContenuDetaille());
            $req->bindValue(':image_couverture', $rapport->getImageCouverture());
            $req->bindValue(':vues', $rapport->getVues());
            
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
                    tendance_generale = :tendance_generale, niveau_demande_global = :niveau_demande_global, nombre_donnees = :nombre_donnees, auteur = :auteur,
                    contenu_detaille = :contenu_detaille, image_couverture = :image_couverture
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
            $req->bindValue(':contenu_detaille', $rapport->getContenuDetaille());
            $req->bindValue(':image_couverture', $rapport->getImageCouverture());
            
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
            $sql = "INSERT INTO donnee_marche (domaine, competence, salaire_min, salaire_max, salaire_moyen, demande, date_collecte, description) 
                    VALUES (:domaine, :competence, :salaire_min, :salaire_max, :salaire_moyen, :demande, :date_collecte, :description)";
            
            $req = $this->db->prepare($sql);
            
            $req->bindValue(':domaine', $donnee->getDomaine());
            $req->bindValue(':competence', $donnee->getCompetence());
            $req->bindValue(':salaire_min', $donnee->getSalaireMin());
            $req->bindValue(':salaire_max', $donnee->getSalaireMax());
            $req->bindValue(':salaire_moyen', $donnee->getSalaireMoyen());
            $req->bindValue(':demande', $donnee->getDemande());
            $req->bindValue(':date_collecte', $donnee->getDateCollecte());
            $req->bindValue(':description', $donnee->getDescription());
            
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
                    salaire_max = :salaire_max, salaire_moyen = :salaire_moyen, demande = :demande, date_collecte = :date_collecte,
                    description = :description
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
            $req->bindValue(':description', $donnee->getDescription());
            
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

    public function incrementerViews($id)
    {
        try {
            $sql = "UPDATE rapport_marche SET vues = vues + 1 WHERE id_rapport_marche = :id";
            $req = $this->db->prepare($sql);
            $req->bindParam(':id', $id);
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getSidebarStats()
    {
        $stats = [];
        
        // 1. Données analysées (Total)
        $sql = "SELECT COUNT(*) as total FROM donnee_marche";
        try {
            $req = $this->db->query($sql);
            $res = $req->fetch();
            $stats['donnees_total'] = $res['total'] > 0 ? $res['total'] : 0;
        } catch (Exception $e) {
            $stats['donnees_total'] = 0;
        }

        // Sparkline for Données analysées (Simulated trend based on total for visual effect)
        $baseDonnees = $stats['donnees_total'] > 0 ? $stats['donnees_total'] : 15;
        $stats['sparkline_donnees'] = [
            max(0, $baseDonnees - 5), max(0, $baseDonnees - 3), max(0, $baseDonnees - 4), 
            $baseDonnees - 2, $baseDonnees + 1, $baseDonnees
        ];

        // 2. Salaire moyen global
        $sql = "SELECT AVG(salaire_moyen) as avg_sal FROM donnee_marche WHERE salaire_moyen IS NOT NULL AND salaire_moyen > 0";
        try {
            $req = $this->db->query($sql);
            $res = $req->fetch();
            $stats['salaire_moyen'] = $res['avg_sal'] ? round($res['avg_sal']) : 0;
        } catch (Exception $e) {
            $stats['salaire_moyen'] = 0;
        }

        // Sparkline for salaries (Simulated trend based on current avg)
        $baseSalaire = $stats['salaire_moyen'] > 0 ? $stats['salaire_moyen'] : 2500;
        $stats['sparkline_salaire'] = [
            $baseSalaire * 0.9, $baseSalaire * 0.95, $baseSalaire * 0.92, 
            $baseSalaire * 0.98, $baseSalaire * 1.02, $baseSalaire
        ];
        
        // 3. Top Secteurs (based on rapport_marche secteur_principal)
        $sql = "SELECT secteur_principal FROM rapport_marche";
        try {
            $req = $this->db->query($sql);
            $secteurs_raw = $req->fetchAll();
            $secteurs_count = [];
            $total_secteurs = 0;
            foreach($secteurs_raw as $row) {
                if (!empty($row['secteur_principal'])) {
                    $tags = explode(',', $row['secteur_principal']);
                    foreach($tags as $t) {
                        $t = trim($t);
                        if ($t) {
                            if (!isset($secteurs_count[$t])) $secteurs_count[$t] = 0;
                            $secteurs_count[$t]++;
                            $total_secteurs++;
                        }
                    }
                }
            }
            arsort($secteurs_count);
            $stats['top_secteurs'] = array_slice($secteurs_count, 0, 4, true);
            $stats['total_secteurs_tags'] = $total_secteurs;
        } catch (Exception $e) {
            $stats['top_secteurs'] = [];
            $stats['total_secteurs_tags'] = 0;
        }

        // 4. Sujets tendance (Top tags by vues)
        $sql = "SELECT secteur_principal, vues FROM rapport_marche ORDER BY vues DESC";
        try {
            $req = $this->db->query($sql);
            $reports_raw = $req->fetchAll();
            $sujets_vues = [];
            foreach($reports_raw as $row) {
                if (!empty($row['secteur_principal'])) {
                    $tags = explode(',', $row['secteur_principal']);
                    foreach($tags as $t) {
                        $t = trim($t);
                        if ($t) {
                            if (!isset($sujets_vues[$t])) $sujets_vues[$t] = 0;
                            $sujets_vues[$t] += $row['vues'];
                        }
                    }
                }
            }
            arsort($sujets_vues);
            $stats['sujets_tendance'] = array_slice($sujets_vues, 0, 5, true);
        } catch (Exception $e) {
            $stats['sujets_tendance'] = [];
        }

        return $stats;
    }

    // --- Data Aggregation for Visualizations ---
    public function getRegionalMarketStats()
    {
        // Simple aggregation of average salaries and report counts by region
        $sql = "SELECT region, AVG(salaire_moyen_global) as avg_salary, COUNT(*) as report_count 
                FROM rapport_marche 
                WHERE region IS NOT NULL AND region != '' 
                GROUP BY region";
        try {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getRegionalMarketStats: " . $e->getMessage());
            return [];
        }
    }

    public function getSkillDNA()
    {
        // Calculate co-occurrences of sectors/tags across reports to build a graph
        $sql = "SELECT secteur_principal FROM rapport_marche WHERE secteur_principal IS NOT NULL AND secteur_principal != ''";
        $nodes = [];
        $links = [];
        
        try {
            $stmt = $this->db->query($sql);
            $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $cooccurrences = [];
            $nodeCounts = [];

            foreach ($reports as $r) {
                $tags = array_map('trim', explode(',', $r['secteur_principal']));
                $tags = array_filter($tags); // Remove empty
                
                foreach ($tags as $tag) {
                    if (!isset($nodeCounts[$tag])) $nodeCounts[$tag] = 0;
                    $nodeCounts[$tag]++;
                }
                
                // Co-occurrence matrix
                for ($i = 0; $i < count($tags); $i++) {
                    for ($j = $i + 1; $j < count($tags); $j++) {
                        $t1 = $tags[$i];
                        $t2 = $tags[$j];
                        
                        // Order alphabetically to avoid duplicates
                        if (strcmp($t1, $t2) > 0) {
                            $temp = $t1;
                            $t1 = $t2;
                            $t2 = $temp;
                        }
                        
                        $key = $t1 . "|||" . $t2;
                        if (!isset($cooccurrences[$key])) $cooccurrences[$key] = 0;
                        $cooccurrences[$key]++;
                    }
                }
            }
            
            // Build Nodes
            foreach ($nodeCounts as $tag => $count) {
                $nodes[] = ["id" => $tag, "group" => 1, "value" => $count];
            }
            
            // Build Links
            foreach ($cooccurrences as $key => $weight) {
                $parts = explode("|||", $key);
                $links[] = ["source" => $parts[0], "target" => $parts[1], "value" => $weight];
            }
            
            return ["nodes" => $nodes, "links" => $links];

        } catch (Exception $e) {
            error_log("Error in getSkillDNA: " . $e->getMessage());
            return ["nodes" => [], "links" => []];
        }
    }

}
?>
