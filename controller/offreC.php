<?php
require_once __DIR__ . '/../config.php';
class offreC{
    public function ajouterOffre($offre){
        $db = config::getConnexion();
        try{
            $query = $db->prepare("INSERT INTO offreemploi (id_entreprise, titre, description, domaine, competences_requises, experience_requise, salaire, question, date_publication, date_expir, statut, img_post, type, lieu) VALUES (1, :titre, :description, :domaine, :competences_requises, :experience_requise, :salaire, :question, :date_publication, :date_expir, 'Actif', :img_post, :type, :lieu)");
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
                'img_post' => $offre->getImgPost(),
                'type' => $offre->getType(),
                'lieu' => $offre->getLieu()
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
                $query = $db->prepare("UPDATE offreemploi SET titre=:titre, description=:description, domaine=:domaine, competences_requises=:competences_requises, experience_requise=:experience_requise, salaire=:salaire, question=:question, date_publication=:date_publication, date_expir=:date_expir, img_post=:img_post, type=:type, lieu=:lieu WHERE id_offre=:id_offre");
            } else {
                $query = $db->prepare("UPDATE offreemploi SET titre=:titre, description=:description, domaine=:domaine, competences_requises=:competences_requises, experience_requise=:experience_requise, salaire=:salaire, question=:question, date_publication=:date_publication, date_expir=:date_expir, type=:type, lieu=:lieu WHERE id_offre=:id_offre");
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
                'date_expir' => $offre->getDateExpir(),
                'type' => $offre->getType(),
                'lieu' => $offre->getLieu()
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
            $query=$db->prepare("SELECT o.*, u.nom as nom_entreprise 
                                FROM offreemploi o 
                                LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur 
                                WHERE o.id_offre=:id_offre");
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
    public function rechercherOffresAjax(string $keyword, bool $onlyActive = false, string $filterStatus = '', string $filterType = '', ?int $salaryMin = null, ?int $salaryMax = null, string $sortDate = ''): array {
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
            if (!empty($filterType) && $filterType !== 'all') {
                $sql .= " AND o.type = :type";
                $params['type'] = $filterType;
            }
            if ($salaryMin !== null) {
                $sql .= " AND CAST(o.salaire AS UNSIGNED) >= :salary_min";
                $params['salary_min'] = $salaryMin;
            }
            if ($salaryMax !== null) {
                $sql .= " AND CAST(o.salaire AS UNSIGNED) <= :salary_max";
                $params['salary_max'] = $salaryMax;
            }
            $dateOrder = ($sortDate === 'ASC') ? 'ASC' : 'DESC';
            $sql .= " ORDER BY o.date_publication $dateOrder";
            
            $req = $db->prepare($sql);
            $req->execute($params);
            return $req->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
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
            $filterType = $_POST['filter_type'] ?? '';
            $salaryMin = isset($_POST['salary_min']) && $_POST['salary_min'] !== '' ? (int)$_POST['salary_min'] : null;
            $salaryMax = isset($_POST['salary_max']) && $_POST['salary_max'] !== '' ? (int)$_POST['salary_max'] : null;
            // Reset salary filter if full range
            if ($salaryMin === 0 && $salaryMax === 10000) {
                $salaryMin = null;
                $salaryMax = null;
            }
            $sortDate = $_POST['sort_date'] ?? '';
            
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
                if (!empty($filterType) && $filterType !== 'all') {
                    $sql .= " AND o.type = :type";
                    $params['type'] = $filterType;
                }
                if ($salaryMin !== null) {
                    $sql .= " AND CAST(o.salaire AS UNSIGNED) >= :salary_min";
                    $params['salary_min'] = $salaryMin;
                }
                if ($salaryMax !== null) {
                    $sql .= " AND CAST(o.salaire AS UNSIGNED) <= :salary_max";
                    $params['salary_max'] = $salaryMax;
                }
                $dateOrder = ($sortDate === 'ASC') ? 'ASC' : 'DESC';
                $sql .= " ORDER BY o.date_publication $dateOrder, o.id_offre DESC";
                $req = $db->prepare($sql);
                $req->execute($params);
                $results = $req->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $results = $this->rechercherOffresAjax($query, $onlyActive, $filterStatus, $filterType, $salaryMin, $salaryMax, $sortDate);
            }
            
            echo json_encode(['success' => true, 'results' => $results]);
            exit;
        }

        // Action non reconnue
        echo json_encode(['success' => false, 'message' => 'Action non reconnue']);
        exit;
    }

    // ----- NOUVELLE METHODE POUR LES STATISTIQUES -----
    public function getOffresStatsMensuel() {
        $db = config::getConnexion();
        try {
            $sql = "SELECT MONTH(date_publication) as mois, COUNT(*) as nb_offres 
                    FROM offreemploi 
                    WHERE date_publication >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
                    GROUP BY MONTH(date_publication)
                    ORDER BY MONTH(date_publication)";
            $stmt = $db->query($sql);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Map the months to their names
            $monthsMap = [
                1 => 'Jan', 2 => 'Fév', 3 => 'Mar', 4 => 'Avr', 
                5 => 'Mai', 6 => 'Jun', 7 => 'Jul', 8 => 'Aoû', 
                9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Déc'
            ];
            
            // Initialize the last 6 months with 0
            $stats = [];
            for ($i = 5; $i >= 0; $i--) {
                $monthNum = (int)date('n', strtotime("-$i months"));
                $stats[$monthNum] = [
                    'label' => $monthsMap[$monthNum],
                    'value' => 0
                ];
            }
            
            // Fill with real data
            foreach ($results as $row) {
                $m = (int)$row['mois'];
                if (isset($stats[$m])) {
                    $stats[$m]['value'] = (int)$row['nb_offres'];
                }
            }
            
            return array_values($stats);
            
        } catch (Exception $e) {
            return [];
        }
    }
    // ═══ GESTION DES FAVORIS ═══
    public function toggleFavori($id_candidat, $id_offre) {
        $db = config::getConnexion();
        try {
            // Vérifier si déjà en favori
            $check = $db->prepare("SELECT id_favori FROM favoris WHERE id_candidat = :c AND id_offre = :o");
            $check->execute(['c' => $id_candidat, 'o' => $id_offre]);
            
            if ($check->rowCount() > 0) {
                // Supprimer
                $del = $db->prepare("DELETE FROM favoris WHERE id_candidat = :c AND id_offre = :o");
                $del->execute(['c' => $id_candidat, 'o' => $id_offre]);
                return ['action' => 'removed'];
            } else {
                // Ajouter
                $ins = $db->prepare("INSERT INTO favoris (id_candidat, id_offre) VALUES (:c, :o)");
                $ins->execute(['c' => $id_candidat, 'o' => $id_offre]);
                return ['action' => 'added'];
            }
        } catch (Exception $e) {
            // Création automatique de la table si elle n'existe pas (pour le premier test)
            $db->exec("CREATE TABLE IF NOT EXISTS favoris (
                id_favori INT AUTO_INCREMENT PRIMARY KEY,
                id_candidat INT NOT NULL,
                id_offre INT NOT NULL,
                date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(id_candidat, id_offre)
            )");
            return $this->toggleFavori($id_candidat, $id_offre);
        }
    }

    public function isFavori($id_candidat, $id_offre) {
        $db = config::getConnexion();
        try {
            $req = $db->prepare("SELECT 1 FROM favoris WHERE id_candidat = :c AND id_offre = :o");
            $req->execute(['c' => $id_candidat, 'o' => $id_offre]);
            return $req->rowCount() > 0;
        } catch (Exception $e) { return false; }
    }

    public function getFavorisByUser($id_candidat) {
        $db = config::getConnexion();
        try {
            $sql = "SELECT o.*, u.nom as nom_entreprise 
                    FROM offreemploi o 
                    JOIN favoris f ON o.id_offre = f.id_offre 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur 
                    WHERE f.id_candidat = :id";
            $req = $db->prepare($sql);
            $req->execute(['id' => $id_candidat]);
            return $req;
        } catch (Exception $e) { return null; }
    }

    public function getOffresAvecLieu() {
        $db = config::getConnexion();
        try { 
            $sql = "SELECT o.*, u.nom as nom_entreprise 
                    FROM offreemploi o 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur 
                    WHERE o.lieu IS NOT NULL AND o.lieu != '' AND o.statut = 'Actif'";
            return $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }
}
?>