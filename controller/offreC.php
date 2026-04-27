<?php
include '../../config.php';
class offreC{
    public function ajouterOffre($offre){
        $db = config::getConnexion();
        try{
            $query = $db->prepare("INSERT INTO offreemploi (id_entreprise, titre, description, domaine, competences_requises, experience_requise, salaire, question, date_publication, date_expir, statut, img_post) VALUES (1, :titre, :description, :domaine, :competences_requises, :experience_requise, :salaire, :question, :date_publication, :date_expir, 'Actif', :img_post)");
            $query->execute([
                'titre' => $offre->getTitre(),
                'description' => $offre->getDescription(),
                'domaine' => $offre->getDomaine(),
                'competences_requises' => $offre->getCompetencesRequises(),
                'experience_requise' => $offre->getExperienceRequise(),
                'salaire' => $offre->getSalaire(),
                'question' => $offre->getQuestion(),
                'date_publication' => $offre->getDatePublication(),
                'date_expir' => $offre->getDateExpir(),
                'img_post' => $offre->getImgPost()
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

            $sql = "SELECT o.*, u.nom as nom_entreprise,
                    (SELECT COUNT(*) FROM candidatures c WHERE c.id_offre = o.id_offre) as nb_candidats
                    FROM offreemploi o 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur";
            if ($onlyActive) {
                $sql .= " WHERE o.statut = 'Actif'";
            }
            $sql .= " ORDER BY o.date_publication DESC, o.id_offre DESC";
            
            $liste = $db->query($sql);
            return $liste;
        }catch (Exception $e){
            die('Erreur: '.$e->getMessage());
        }
    }



    public function filtrerOffres($criteres = []){
        $db = config::getConnexion();
        try {
            // Mise à jour automatique des expirations
            $db->exec("UPDATE offreemploi SET statut = 'Expiré' WHERE date_expir < CURDATE()");
            $db->exec("UPDATE offreemploi SET statut = 'Actif' WHERE date_expir >= CURDATE()");

            $sql = "SELECT o.*, u.nom as nom_entreprise,
                    (SELECT COUNT(*) FROM candidatures c WHERE c.id_offre = o.id_offre) as nb_candidats
                    FROM offreemploi o 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur
                    WHERE 1=1";
            $params = [];

            if (!empty($criteres['statut']) && $criteres['statut'] !== 'Tous statuts') {
                $sql .= " AND o.statut = :statut";
                $params['statut'] = $criteres['statut'];
            }

            // Gestion du tri (sort_date prioritaire sur sort_salaire)
            if (!empty($criteres['sort_date'])) {
                $ord = strtoupper($criteres['sort_date']) === 'ASC' ? 'ASC' : 'DESC';
                $sql .= " ORDER BY o.date_publication $ord, o.id_offre $ord";
            } elseif (!empty($criteres['sort_salaire'])) {
                $ord = strtoupper($criteres['sort_salaire']) === 'ASC' ? 'ASC' : 'DESC';
                $sql .= " ORDER BY o.salaire $ord, o.date_publication DESC";
            } else {
                $sql .= " ORDER BY o.date_publication DESC, o.id_offre DESC";
            }
                    
            $req = $db->prepare($sql);
            $req->execute($params);
            return $req;
        } catch (Exception $e) {
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
            // img_post est inclus s'il n'est pas null, sinon on garde l'ancien (à gérer côté vue ou ici, plus propre ici)
            if ($offre->getImgPost() !== null) {
                $query = $db->prepare("UPDATE offreemploi SET titre=:titre, description=:description, domaine=:domaine, competences_requises=:competences_requises, experience_requise=:experience_requise, salaire=:salaire, question=:question, date_publication=:date_publication, date_expir=:date_expir, img_post=:img_post WHERE id_offre=:id_offre");
            } else {
                $query = $db->prepare("UPDATE offreemploi SET titre=:titre, description=:description, domaine=:domaine, competences_requises=:competences_requises, experience_requise=:experience_requise, salaire=:salaire, question=:question, date_publication=:date_publication, date_expir=:date_expir WHERE id_offre=:id_offre");
            }
            
            $params = [
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
            ];

            if ($offre->getImgPost() !== null) {
                $params['img_post'] = $offre->getImgPost();
            }

            $query->execute($params);
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

    // ═══ RECHERCHE DYNAMIQUE AJAX (RETOURNE UN ARRAY POUR JSON) ═══
    public function rechercherOffresAjax(string $keyword, bool $onlyActive = false, string $filterStatus = ''): array {
        $db = config::getConnexion();
        try {
            $sql = "SELECT o.*, u.nom as nom_entreprise,
                    (SELECT COUNT(*) FROM candidatures c WHERE c.id_offre = o.id_offre) as nb_candidats
                    FROM offreemploi o 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur
                    WHERE o.titre LIKE :kw";
            $params = ['kw' => '%' . $keyword . '%'];
            
            if ($onlyActive) {
                $sql .= " AND o.statut = 'Actif'";
            } elseif (!empty($filterStatus) && $filterStatus !== 'Tous statuts') {
                $sql .= " AND o.statut = :statut";
                $params['statut'] = $filterStatus;
            }
            $sql .= " ORDER BY o.date_publication DESC";
            
            $req = $db->prepare($sql);
            $req->execute($params);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    // ═══ HANDLER AJAX (POINT D'ENTRÉE UNIQUE) ═══
    public function handleAjax(): void {
        header('Content-Type: application/json; charset=utf-8');
        
        $action = $_POST['action'] ?? '';

        if ($action === 'search_offres') {
            $query = trim($_POST['query'] ?? '');
            $onlyActive = isset($_POST['only_active']) && $_POST['only_active'] === '1';
            $filterStatus = $_POST['filter_status'] ?? '';
            
            if ($query === '') {
                // Si vide, retourner toutes les offres
                $db = config::getConnexion();
                $sql = "SELECT o.*, u.nom as nom_entreprise,
                        (SELECT COUNT(*) FROM candidatures c WHERE c.id_offre = o.id_offre) as nb_candidats
                        FROM offreemploi o 
                        LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur
                        WHERE 1=1";
                $params = [];
                if ($onlyActive) {
                    $sql .= " AND o.statut = 'Actif'";
                } elseif (!empty($filterStatus) && $filterStatus !== 'Tous statuts') {
                    $sql .= " AND o.statut = :statut";
                    $params['statut'] = $filterStatus;
                }
                $sql .= " ORDER BY o.date_publication DESC, o.id_offre DESC";
                $req = $db->prepare($sql);
                $req->execute($params);
                $results = $req->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $results = $this->rechercherOffresAjax($query, $onlyActive, $filterStatus);
            }
            
            echo json_encode(['success' => true, 'results' => $results]);
            exit;
        }

        // Action non reconnue
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
        exit;
    }

}
