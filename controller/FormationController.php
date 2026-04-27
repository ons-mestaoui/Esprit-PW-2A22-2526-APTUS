<?php
// FormationController : gère toute la logique métier liée aux formations
// C'est ici qu'on fait les contrôles de saisie (pas dans la vue)
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Formation.php';

class FormationController
{

    // Récupère toutes les formations avec le nom du tuteur (jointure)
    public function listerFormations()
    {
        $db = config::getConnexion();
        // Essayer d'abord la table 'utilisateur', sinon fallback sur 'User'
        $tables_utilisateurs = ['utilisateur', 'User'];
        foreach ($tables_utilisateurs as $table) {
            try {
                $liste = $db->query("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN $table u ON f.id_tuteur = u.id
                    WHERE f.statut = 'active'
                      AND (f.date_fin IS NULL OR f.date_fin >= DATE_SUB(NOW(), INTERVAL 48 HOUR))
                    ORDER BY f.date_formation ASC
                ");
                return $liste;
            } catch (Exception $e) {
                if ($table === end($tables_utilisateurs)) throw new Exception('Erreur SQL: ' . $e->getMessage());
            }
        }
    }

    // Stats pour le dashboard admin (nb formations, inscrits, certificats, taux)
    public function getStatsGlobales()
    {
        $db = config::getConnexion();
        $stats = [
            'total_formations' => 0,
            'total_inscrits' => 0,
            'certificats' => 0,
            'taux_completion' => 0
        ];

        try {
            // Formations
            $resF = $db->query("SELECT COUNT(*) as c FROM Formation");
            $stats['total_formations'] = $resF->fetch()['c'];

            // Inscriptions
            $resI = $db->query("SELECT COUNT(*) as c FROM inscription");
            $stats['total_inscrits'] = $resI->fetch()['c'];

            // Certificats (Terminées)
            $resC = $db->query("SELECT COUNT(*) as c FROM inscription WHERE statut = 'Terminée' OR progression >= 100");
            $stats['certificats'] = $resC->fetch()['c'];

            // Taux de complétion
            if ($stats['total_inscrits'] > 0) {
                $stats['taux_completion'] = round(($stats['certificats'] / $stats['total_inscrits']) * 100);
            }
        } catch (Exception $e) {
            // En cas d'erreur de table (ex: Inscription au lieu de inscription)
            try {
                $resI = $db->query("SELECT COUNT(*) as c FROM Inscription");
                $stats['total_inscrits'] = $resI->fetch()['c'];

                $resC = $db->query("SELECT COUNT(*) as c FROM Inscription WHERE statut = 'Terminée' OR progression >= 100");
                $stats['certificats'] = $resC->fetch()['c'];

                if ($stats['total_inscrits'] > 0) {
                    $stats['taux_completion'] = round(($stats['certificats'] / $stats['total_inscrits']) * 100);
                }
            } catch (Exception $e2) {
                // Ignore if tables don't exist yet
            }
        }

        return $stats;
    }

    public function getTuteurs()
    {
        $db = config::getConnexion();
        try {
            // Utiliser LIKE '%tuteur%' pour correspondre à "Tuteur", "tuteur", "tuteurs", "Tuteurs"
            $query = $db->query("SELECT * FROM utilisateur WHERE LOWER(role) LIKE '%tuteur%'");
            return $query->fetchAll();
        } catch (Exception $e) {
            try {
                $query = $db->query("SELECT * FROM User WHERE LOWER(role) LIKE '%tuteur%'");
                return $query->fetchAll();
            } catch (Exception $e2) {
                return [];
            }
        }
    }

    // Récupère les formations au format JSON pour FullCalendar
    public function getFormationsForCalendar()
    {
        $db = config::getConnexion();
        $events = [];
        
        try {
            $liste = $db->query("
                SELECT f.id_formation, f.titre, f.date_formation, f.is_online,
                       f.domaine, f.niveau, f.id_tuteur,
                       COALESCE(u.nom, 'Aptus') as tuteur_nom 
                FROM Formation f 
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id
            ");
            $formations = $liste->fetchAll();
        } catch (Exception $e) {
            try {
                $liste = $db->query("
                    SELECT f.id_formation, f.titre, f.date_formation, f.is_online,
                           f.domaine, f.niveau, f.id_tuteur,
                           COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN User u ON f.id_tuteur = u.id
                ");
                $formations = $liste->fetchAll();
            } catch (Exception $e2) {
                return [];
            }
        }

        // Same palette as the admin sidebar so colors match
        $palette = ['#6366f1','#0ea5e9','#10b981','#f59e0b','#ec4899','#8b5cf6','#14b8a6','#ef4444'];
        // Build a deterministic tuteur→color map from the fetched data
        $tuteurColorMap = [];
        $paletteIdx = 0;
        foreach ($formations as $f) {
            $tid = $f['id_tuteur'];
            if ($tid && !isset($tuteurColorMap[$tid])) {
                $tuteurColorMap[$tid] = $palette[$paletteIdx % count($palette)];
                $paletteIdx++;
            }
        }

        foreach ($formations as $f) {
            $tid       = $f['id_tuteur'];
            $color     = isset($tuteurColorMap[$tid]) ? $tuteurColorMap[$tid] : '#6366f1';
            $dateBase  = substr($f['date_formation'], 0, 10); // YYYY-MM-DD

            $events[] = [
                'id'              => 'f_' . $f['id_formation'],
                'title'           => $f['tuteur_nom'] . ' — ' . $f['titre'],
                'start'           => $dateBase . 'T09:00:00',
                'end'             => $dateBase . 'T10:00:00',
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'extendedProps'   => [
                    'id_tuteur'   => $tid,
                    'tuteur_nom'  => $f['tuteur_nom'],
                    'titre'       => $f['titre'],
                    'type'        => 'formation',
                    'lieu'        => $f['is_online'] ? 'En ligne' : 'Présentiel',
                    'domaine'     => $f['domaine'],
                    'niveau'      => $f['niveau'],
                ]
            ];
        }

        return $events;
    }

    // ============================================
    // CONTRÔLES DE SAISIE (validation côté serveur)
    // ============================================
    // Si un champ est invalide -> on lance une Exception que la vue va attraper
    private function validateFormation($formation)
    {
        // Titre : obligatoire, entre 5 et 100 caractères
        $titre = trim($formation->getTitre());
        if (empty($titre)) {
            throw new Exception("Le titre est obligatoire.");
        }
        if (strlen($titre) < 5 || strlen($titre) > 100) {
            throw new Exception("Le titre doit contenir entre 5 et 100 caractères.");
        }

        // Description : obligatoire, au moins 20 caractères
        // strip_tags() pour compter le vrai texte sans les balises HTML de Quill
        $descriptionText = trim(strip_tags($formation->getDescription()));
        if (empty($descriptionText)) {
            throw new Exception("La description est obligatoire.");
        }
        if (strlen($descriptionText) < 20) {
            throw new Exception("La description doit contenir au moins 20 caractères.");
        }

        // Domaine : obligatoire
        if (empty(trim($formation->getDomaine()))) {
            throw new Exception("Le domaine est obligatoire.");
        }

        // Niveau : obligatoire
        if (empty(trim($formation->getNiveau()))) {
            throw new Exception("Le niveau est obligatoire.");
        }

        // Date : obligatoire + interdiction de mettre une date passée
        if (empty($formation->getDateFormation())) {
            throw new Exception("La date de formation est obligatoire.");
        }
        if (strtotime($formation->getDateFormation()) < strtotime(date('Y-m-d'))) {
            throw new Exception("La date de formation ne peut pas être dans le passé.");
        }

        // Date de fin : optionnelle, mais si présente doit être >= date_formation
        if (!empty($formation->getDateFin()) && !empty($formation->getDateFormation())) {
            if (strtotime($formation->getDateFin()) < strtotime($formation->getDateFormation())) {
                throw new Exception("La date de fin ne peut pas être avant la date de début.");
            }
        }

        // Durée : optionnel, mais si rempli doit respecter le format "chiffre + unité"
        // Exemples valides : 10h, 2 jours, 30min
        $duree = trim($formation->getDuree());
        if (!empty($duree)) {
            if (!preg_match('/^\d+\s*[A-Za-z]+$/', $duree)) {
                throw new Exception("La durée doit avoir un format 'numérique + unité' (ex: 10h).");
            }
        }

        // Tuteur : obligatoire (on doit choisir qui anime la formation)
        if (empty($formation->getIdTuteur())) {
            throw new Exception("Vous devez sélectionner un tuteur.");
        }
        // NB: l'image n'est pas obligatoire
    }

    // Génère un lien Jitsi Meet automatiquement pour les formations en ligne
    private function generateJitsiLink($titre)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($titre));
        return "https://meet.jit.si/Aptus_" . $slug . "_" . uniqid();
    }

    // Ajout d'une formation : on valide d'abord, puis on insère en BDD
    public function addFormation($formation)
    {
        // Appel de validateFormation() -> si erreur, elle lance une Exception
        $this->validateFormation($formation);

        // Logique métier : Génération automatique du lien si online et vide
        $lien = $formation->getLienApiRoom();
        if ($formation->getIsOnline() == 1 && empty($lien)) {
            $lien = $this->generateJitsiLink($formation->getTitre());
            // On peut soit modifier l'objet, soit gérer cela ici. 
            // Pour rester simple, on va utiliser cette valeur dans l'execute.
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                INSERT INTO Formation 
                (titre, description, domaine, niveau, duree, date_formation, image_base64, id_tuteur, is_online, lien_api_room, prerequis_id, date_fin) 
                VALUES 
                (:titre, :description, :domaine, :niveau, :duree, :date_formation, :image_base64, :id_tuteur, :is_online, :lien_api_room, :prerequis_id, :date_fin)
            ");
            $query->execute([
                'titre' => $formation->getTitre(),
                'description' => $formation->getDescription(),
                'domaine' => $formation->getDomaine(),
                'niveau' => $formation->getNiveau(),
                'duree' => $formation->getDuree(),
                'date_formation' => $formation->getDateFormation(),
                'image_base64' => $formation->getImageBase64(),
                'id_tuteur' => $formation->getIdTuteur(),
                'is_online' => $formation->getIsOnline(),
                'lien_api_room' => $lien,
                'prerequis_id' => $formation->getPrerequisId(),
                'date_fin' => !empty($formation->getDateFin()) ? $formation->getDateFin() : null
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Erreur SQL: ' . $e->getMessage());
        }
    }

    // Suppression : on vérifie d'abord qu'il n'y a pas d'inscrits actifs
    public function deleteFormation($id)
    {
        $db = config::getConnexion();
        try {
            // Vérifier s'il y a des inscrits
            $nb_inscrits = 0;
            try {
                $check = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = :id");
                $check->execute(['id' => $id]);
                $nb_inscrits = $check->fetchColumn();
            } catch (Exception $e) {
                // Ignore si la table n'est pas trouvée
            }

            if ($nb_inscrits > 0) {
                throw new Exception("Impossible de supprimer une formation active avec des inscrits.");
            }

            // Supprimer les enregistrements enfants pour éviter une erreur de contrainte de clé étrangère
            try {
                $db->prepare("DELETE FROM rapport_emotions WHERE id_formation = :id")->execute(['id' => $id]);
            } catch(Exception $e) {}

            $query = $db->prepare("DELETE FROM Formation WHERE id_formation = :id");
            $query->execute(['id' => $id]);
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function updateFormation($formation, $id)
    {
        $this->validateFormation($formation);

        $lien = $formation->getLienApiRoom();
        if ($formation->getIsOnline() == 1 && empty($lien)) {
            $lien = $this->generateJitsiLink($formation->getTitre());
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                UPDATE Formation SET 
                titre=:titre, description=:description, domaine=:domaine, niveau=:niveau, 
                duree=:duree, date_formation=:date_formation, image_base64=:image_base64, 
                id_tuteur=:id_tuteur, is_online=:is_online, lien_api_room=:lien_api_room, 
                prerequis_id=:prerequis_id, date_fin=:date_fin 
                WHERE id_formation=:id
            ");
            $query->execute([
                'id' => $id,
                'titre' => $formation->getTitre(),
                'description' => $formation->getDescription(),
                'domaine' => $formation->getDomaine(),
                'niveau' => $formation->getNiveau(),
                'duree' => $formation->getDuree(),
                'date_formation' => $formation->getDateFormation(),
                'image_base64' => $formation->getImageBase64(),
                'id_tuteur' => $formation->getIdTuteur(),
                'is_online' => $formation->getIsOnline(),
                'lien_api_room' => $lien,
                'prerequis_id' => $formation->getPrerequisId(),
                'date_fin' => !empty($formation->getDateFin()) ? $formation->getDateFin() : null
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur SQL: ' . $e->getMessage());
        }
    }

    // Récupère une formation par son ID (pour la page d'édition)
    public function getFormationById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                FROM Formation f 
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id 
                WHERE f.id_formation = :id
            ");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            try {
                $query = $db->prepare("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN User u ON f.id_tuteur = u.id 
                    WHERE f.id_formation = :id
                ");
                $query->execute(['id' => $id]);
                return $query->fetch();
            } catch (Exception $e2) {
                throw new Exception('Erreur: ' . $e2->getMessage());
            }
        }
    }

    // Fonction spécifique pour ton scénario de Room Jitsi
    public function updateLienRoom($id, $lien)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE Formation SET lien_api_room = :lien WHERE id_formation = :id");
            $query->execute(['id' => $id, 'lien' => $lien]);
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    // 1. Pour la liste à droite du calendrier
    public function getFormationsByTuteur($id_tuteur)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                SELECT f.*, 
                (SELECT COUNT(*) FROM Inscription WHERE id_formation = f.id_formation) as nb_inscrits
                FROM Formation f 
                WHERE f.id_tuteur = :id
                ORDER BY f.date_formation ASC
            ");
            $query->execute(['id' => $id_tuteur]);
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    // 2. Pour le flux JSON du calendrier (FullCalendar)
    public function getCalendarEventsJSON($id_tuteur)
    {
        $formations = $this->getFormationsByTuteur($id_tuteur);
        $events = [];
        foreach ($formations as $f) {
            $events[] = [
                'title' => $f['titre'],
                'start' => $f['date_formation'],
                'backgroundColor' => ($f['is_online']) ? '#3498db' : '#2ecc71',
                'extendedProps' => [
                    'is_online' => (bool) $f['is_online'],
                    'nb_inscrits' => $f['nb_inscrits'],
                    'description' => $f['description'],
                    'lien_room' => $f['lien_api_room']
                ]
            ];
        }
        header('Content-Type: application/json');
        echo json_encode($events);
    }

    // ============================================================
    // CONCEPT 2 : Cartographe de Skill Tree
    // ============================================================

    /**
     * Récupère une formation avec ses infos + sa progression pour un étudiant.
     * Inclut le champ 'prerequis_id' ajouté par la migration SQL.
     *
     * @param int      $id_formation  ID de la formation cible.
     * @param int|null $id_user       ID étudiant (pour calculer si débloquée).
     * @return array|null
     */
    public function getFormationWithPrerequisite(int $id_formation, ?int $id_user = null): ?array
    {
        $db = config::getConnexion();
        try {
            // Récupération de la formation + progression de l'étudiant si connecté
            $sql = "
                SELECT f.*,
                       COALESCE(u.nom, 'Aptus') AS tuteur_nom,
                       COALESCE(i.progression, 0) AS ma_progression,
                       COALESCE(i.statut, '') AS mon_statut
                FROM Formation f
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id
                LEFT JOIN inscription i ON i.id_formation = f.id_formation
                    AND i.id_user = :id_user
                WHERE f.id_formation = :id
            ";
            $stmt = $db->prepare($sql);
            $stmt->execute(['id' => $id_formation, 'id_user' => $id_user ?? 0]);
            return $stmt->fetch() ?: null;
        } catch (\Exception $e) {
            // Fallback table User / Inscription (casse alternative)
            try {
                $sqlFb = "
                    SELECT f.*,
                           COALESCE(u.nom, 'Aptus') AS tuteur_nom,
                           COALESCE(i.progression, 0) AS ma_progression,
                           COALESCE(i.statut, '') AS mon_statut
                    FROM Formation f
                    LEFT JOIN User u ON f.id_tuteur = u.id
                    LEFT JOIN Inscription i ON i.id_formation = f.id_formation
                        AND i.id_user = :id_user
                    WHERE f.id_formation = :id
                ";
                $stmt = $db->prepare($sqlFb);
                $stmt->execute(['id' => $id_formation, 'id_user' => $id_user ?? 0]);
                return $stmt->fetch() ?: null;
            } catch (\Exception $e2) {
                return null;
            }
        }
    }

    /**
     * Construit récursivement la chaîne de prérequis d'une formation.
     * Retourne un tableau ordonné du plus ancien prérequis jusqu'à la formation finale.
     *
     * Exemple : Dev Web → HTML/CSS → Algorithmique
     * Résultat : [Algorithmique, HTML/CSS, Dev Web]
     *
     * @param int      $id_formation_finale  ID de la formation cible.
     * @param int|null $id_user              ID étudiant pour savoir ce qui est débloqué.
     * @param int      $depth                Profondeur max (sécurité anti-boucles).
     * @return array  Tableau de formations ordonnées (du prérequis au final).
     */
    public function getSkillTree(int $id_formation_finale, ?int $id_user = null, int $depth = 0): array
    {
        // Sécurité : on limite à 10 niveaux de récursivité pour éviter les boucles infinies
        if ($depth >= 10) {
            return [];
        }

        // Récupération de la formation finale avec ses infos
        $formation = $this->getFormationWithPrerequisite($id_formation_finale, $id_user);

        if (!$formation) {
            return [];
        }

        $chain = [];

        // Si cette formation a un prérequis, on remonte la chaîne récursivement
        if (!empty($formation['prerequis_id'])) {
            $prerequisChain = $this->getSkillTree(
                (int)$formation['prerequis_id'],
                $id_user,
                $depth + 1
            );
            // On ajoute les prérequis en PREMIER (ordre logique du parcours)
            $chain = array_merge($chain, $prerequisChain);
        }

        // Calcul de l'état de débloquage pour l'UI
        // Une formation est débloquée si son prérequis direct est complété (100%)
        if (!empty($formation['prerequis_id'])) {
            $prereq = $this->getFormationWithPrerequisite((int)$formation['prerequis_id'], $id_user);
            $formation['is_unlocked'] = ($prereq && $prereq['ma_progression'] >= 100);
        } else {
            // Pas de prérequis = toujours accessible
            $formation['is_unlocked'] = true;
        }

        // Ajout de la formation actuelle à la fin de la chaîne
        $chain[] = $formation;

        return $chain;
    }

    /**
     * Récupère toutes les formations avec leur prérequis pour construire
     * le catalogue complet du Skill Tree (vue catalogue).
     *
     * @param int|null $id_user  Pour calculer la progression de chaque étape.
     * @return array
     */
    public function getAllFormationsWithSkillTree(?int $id_user = null): array
    {
        $db = config::getConnexion();
        try {
            // On récupère d'abord toutes les formations "racines" (sans prérequis)
            // Ce sont les points de départ des parcours de compétences
            $sql = "
                SELECT id_formation
                FROM Formation
                WHERE prerequis_id IS NULL OR prerequis_id = 0
                ORDER BY domaine, titre
            ";
            $stmt = $db->query($sql);
            $roots = $stmt->fetchAll();

            $trees = [];
            foreach ($roots as $root) {
                // Pour chaque racine, on cherche les formations qui en dépendent (direct)
                $children = $this->getChildrenOf((int)$root['id_formation'], $id_user);
                if (!empty($children)) {
                    // On construit un arbre simple : racine + ses enfants
                    $rootFormation = $this->getFormationWithPrerequisite((int)$root['id_formation'], $id_user);
                    if ($rootFormation) {
                        $rootFormation['is_unlocked'] = true;
                        $trees[] = [
                            'root'     => $rootFormation,
                            'children' => $children
                        ];
                    }
                }
            }
            return $trees;
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Récupère les formations directement dépendantes d'une formation donnée.
     *
     * @param int      $parent_id  ID de la formation parente.
     * @param int|null $id_user    Pour calculer la progression.
     * @return array
     */
    private function getChildrenOf(int $parent_id, ?int $id_user = null): array
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("
                SELECT id_formation FROM Formation WHERE prerequis_id = :pid
            ");
            $stmt->execute(['pid' => $parent_id]);
            $childIds = $stmt->fetchAll();

            // Récupérer les formations parentes pour vérifier le débloquage
            $parentFormation = $this->getFormationWithPrerequisite($parent_id, $id_user);

            $children = [];
            foreach ($childIds as $row) {
                $child = $this->getFormationWithPrerequisite((int)$row['id_formation'], $id_user);
                if ($child) {
                    $child['is_unlocked'] = ($parentFormation && $parentFormation['ma_progression'] >= 100);
                    $children[] = $child;
                }
            }
            return $children;
        } catch (\Exception $e) {
            return [];
        }
    }

    // Annulation d'une formation par l'admin
    // -> passe la formation ET toutes ses inscriptions en statut 'annulée'
    public function annuler(int $id)
    {
        // Contrainte de sécurité : vérification du rôle (simulée via session)
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
            header('Location: formations_admin.php');
            exit();
        }

        try {
            $f = $this->getFormationById($id);
            if (!$f) {
                throw new Exception("Formation introuvable.");
            }

            // Contrainte : on ne peut pas annuler une formation déjà annulée
            if ((isset($f['statut']) && $f['statut'] === 'annulée')) {
                throw new Exception("Cette formation est déjà annulée.");
            }

            // Transaction : on annule la formation ET ses inscriptions en même temps
            // Si l'une des deux requêtes échoue -> rollback complet
            $db = config::getConnexion();
            $db->beginTransaction();

            $stmtF = $db->prepare("UPDATE Formation SET statut = 'annulée' WHERE id_formation = ?");
            if (!$stmtF->execute([$id])) {
                throw new Exception("Erreur lors de l'annulation de la formation.");
            }

            try {
                $stmtI = $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_formation = ?");
                $stmtI->execute([$id]);
            } catch (Exception $e) {
                $stmtI = $db->prepare("UPDATE Inscription SET statut = 'annulée' WHERE id_formation = ?");
                $stmtI->execute([$id]);
            }

            $db->commit();
            $_SESSION['flash_success'] = "La formation et ses inscriptions ont été annulées avec succès.";
        } catch (Exception $e) {
            if (isset($db) && $db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_error'] = $e->getMessage();
        }

        header('Location: formations_admin.php');
        exit();
    }
    // Recherche multi-critères pour le dashboard admin
    public function rechercherFormations($search = '', $domaine = '', $niveau = '')
    {
        $db = config::getConnexion();
        $sql = "SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                FROM Formation f 
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id 
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($search)) {
            $sql .= " AND (f.titre LIKE :search OR f.domaine LIKE :search OR f.description LIKE :search)";
            $params['search'] = '%' . $search . '%';
        }
        
        if (!empty($domaine)) {
            $sql .= " AND f.domaine = :domaine";
            $params['domaine'] = $domaine;
        }
        
        if (!empty($niveau)) {
            $sql .= " AND f.niveau = :niveau";
            $params['niveau'] = $niveau;
        }
        
        $sql .= " ORDER BY f.date_formation DESC";
        
        $query = $db->prepare($sql);
        $query->execute($params);
        return $query->fetchAll();
    }
}
