<?php
// FormationController : gère toute la logique métier liée aux formations
// C'est ici qu'on fait les contrôles de saisie ET les requêtes SQL (MVC Etudiant)
include_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Formation.php';

class FormationController
{

    // Récupère toutes les formations avec le nom du tuteur (jointure)
    public function listerFormations()
    {
        $db = config::getConnexion();
        $tables_utilisateurs = ['utilisateur', 'User'];
        foreach ($tables_utilisateurs as $table) {
            try {
                $stmt = $db->query("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN $table u ON f.id_tuteur = u.id
                    WHERE f.statut = 'active'
                      AND (
                        (f.date_fin IS NOT NULL AND f.date_fin >= DATE_SUB(NOW(), INTERVAL 48 HOUR))
                        OR 
                        (f.date_fin IS NULL AND f.date_formation >= DATE_SUB(NOW(), INTERVAL 48 HOUR))
                      )
                    ORDER BY f.date_formation ASC
                ");
                $results = $stmt->fetchAll();
                
                // Préparation des données pour la Vue (Logic métier centralisée)
                return array_map([$this, 'formatFormationForView'], $results);
            } catch (Exception $e) {
                if ($table === end($tables_utilisateurs))
                    throw new Exception('Erreur SQL: ' . $e->getMessage());
            }
        }
    }

    // Récupère l'intégralité des formations (sans filtre de date pour le Skill Tree)
    public function listerToutesFormations()
    {
        $db = config::getConnexion();
        $tables_utilisateurs = ['utilisateur', 'User'];
        foreach ($tables_utilisateurs as $table) {
            try {
                $stmt = $db->query("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN $table u ON f.id_tuteur = u.id
                    WHERE f.statut = 'active'
                    ORDER BY f.date_formation ASC
                ");
                $results = $stmt->fetchAll();
                return array_map([$this, 'formatFormationForView'], $results);
            } catch (Exception $e) {
                if ($table === end($tables_utilisateurs))
                    throw new Exception('Erreur SQL: ' . $e->getMessage());
            }
        }
    }

    /**
     * Centralise la logique de présentation (Logic Métier / Display Logic)
     * Transforme une ligne brute de BDD en données prêtes pour la Vue.
     */
    public function formatFormationForView($f) {
        $f['id_formation'] = (int)($f['id_formation'] ?? 0);
        $f['titre_safe']   = htmlspecialchars($f['titre'] ?? '');
        $f['domaine_safe'] = htmlspecialchars($f['domaine'] ?? 'Général');
        $f['desc_short']   = htmlspecialchars(substr(strip_tags($f['description'] ?? ''), 0, 130));
        
        // Gestion des badges de niveau
        $niveauColors = ['Débutant'=>'#10b981','Intermédiaire'=>'#f59e0b','Avancé'=>'#ef4444','Expert'=>'#8b5cf6'];
        $f['niveau_color'] = $niveauColors[$f['niveau'] ?? ''] ?? 'var(--accent-primary)';
        
        $niveauClasses = ['Débutant'=>'badge-success','Intermédiaire'=>'badge-warning','Avancé'=>'badge-danger','Expert'=>'badge-primary'];
        $f['niveau_class'] = $niveauClasses[$f['niveau'] ?? ''] ?? 'badge-neutral';
        
        // Gestion du lieu/format
        $f['lieu_icon']  = $f['is_online'] ? 'video' : 'map-pin';
        $f['lieu_label'] = $f['is_online'] ? 'En ligne' : 'Présentiel';
        $f['lieu_color'] = $f['is_online'] ? '#3b82f6' : '#10b981';

        // Formatage de la date
        $f['date_format'] = date('d M. Y', strtotime($f['date_formation']));
        
        // Calcul du statut temporel
        $dateRef = strtotime(date('Y-m-d'));
        $dateFm  = strtotime(date('Y-m-d', strtotime($f['date_formation'])));
        $dateDiff = ($dateFm - $dateRef) / 86400;
        
        $f['statut_temporel'] = "";
        $f['statut_temporel_class'] = "";
        
        if ($dateDiff == 0) {
            $f['statut_temporel'] = "Aujourd'hui";
            $f['statut_temporel_class'] = "urgent";
        } elseif ($dateDiff == 1) {
            $f['statut_temporel'] = "Demain !";
            $f['statut_temporel_class'] = "urgent";
        } elseif ($dateDiff > 0) {
            $f['statut_temporel'] = "Dans " . round($dateDiff) . "j";
            $f['statut_temporel_class'] = "upcoming";
        } else {
            $f['statut_temporel'] = "Passé";
            $f['statut_temporel_class'] = "";
        }
        
        $f['est_passee'] = ($dateDiff < 0);

        // Style de background (Image ou Gradient)
        $f['bg_style'] = !empty($f['image_base64'])
            ? "background:url('{$f['image_base64']}') center/cover no-repeat;"
            : "background:linear-gradient(135deg,rgba(99,102,241,0.18),rgba(139,92,246,0.12));";

        return $f;
    }

    /**
     * 🧩 LOGIQUE MÉTIER ADMIN (MVC COMPLIANCE)
     */
    public function getAdminFormationsData() {
        $liste = $this->listerFormations();
        $domaines = array_unique(array_map(function($f) { return $f['domaine']; }, $liste));
        return [
            'listeFormations' => $liste,
            'totalFormations' => count($liste),
            'domaines' => $domaines,
            'tuteurs' => $this->getTuteurs()
        ];
    }

    /**
     * 🧩 LOGIQUE MÉTIER PAGE ADMIN COMPLÈTE (MVC COMPLIANCE)
     */
    public function getAdminPageData() {
        require_once __DIR__ . '/TuteurController.php';
        $tC = new TuteurController();

        $adminData = $this->getAdminFormationsData();
        $stats = $this->getStatsGlobales();
        
        $tuteursList = $tC->listerTuteurs();
        $formationEvents = $this->getFormationsForCalendar();
        $planningEvents = $tC->getPlanning(); 
        
        // Color palette for tuteurs
        $palette = ['var(--accent-primary)', 'var(--accent-secondary)', 'var(--accent-tertiary)', 'var(--accent-warning)', 'var(--accent-info)', '#8b5cf6', '#14b8a6', '#ef4444'];
        $tuteurColors = [];
        foreach ($tuteursList as $idx => $t) {
            $tuteurColors[$t['id']] = $palette[$idx % count($palette)];
        }

        return [
            'listeFormations'  => $adminData['listeFormations'],
            'totalFormations'  => $adminData['totalFormations'],
            'domaines'         => $adminData['domaines'],
            'tuteurs'          => $adminData['tuteurs'],
            'tuteursList'      => $tuteursList,
            'stats'            => $stats,
            'calendarEvents'   => array_merge($formationEvents, $planningEvents),
            'tuteurColors'     => $tuteurColors
        ];
    }

    /**
     * 🧩 LOGIQUE MÉTIER DÉTAIL (MVC COMPLIANCE)
     */
    public function getFormationDetailData($id, $id_user) {
        require_once __DIR__ . '/InscriptionController.php';
        $inscriC = new InscriptionController();
        
        $formation = $this->getFormationById($id);
        if (!$formation) return null;

        $formation = $this->formatFormationForView($formation);
        
        // Vérification prérequis
        $is_unlocked = true;
        $prereq_titre = "";
        if (!empty($formation['prerequis_id'])) {
            $prereq = $this->getFormationWithPrerequisite($formation['prerequis_id'], $id_user);
            if ($prereq && $prereq['ma_progression'] < 100) {
                $is_unlocked = false;
                $prereq_titre = $prereq['titre'];
            }
        }

        return [
            'formation'   => $formation,
            'is_unlocked' => $is_unlocked,
            'prereq_titre'=> $prereq_titre,
            'isInscribed'=> $inscriC->isUserInscribed($id, $id_user)
        ];
    }

    /**
     * 🧩 LOGIQUE MÉTIER "SKILL TREE" (MVC COMPLIANCE)
     */
    public function getSkillTreePageData($id_user, $target_id = null) {
        $viewMode = 'all';
        $skillChain = [];
        $allTrees = [];

        if ($target_id && (int)$target_id > 0) {
            $skillChain = $this->getSkillTree((int)$target_id, $id_user);
            $viewMode = 'chain';
        } else {
            $allTrees = $this->getAllFormationsWithSkillTree($id_user);
            $viewMode = 'all';
        }

        // Données pour la Neural Map
        $toutesLesFormations = $this->listerToutesFormations();
        $formationsData = [];
        $globalDone = 0;

        foreach ($toutesLesFormations as $f) {
            $data = $this->getFormationWithPrerequisite((int)$f['id_formation'], $id_user);
            $isUnlocked = true;
            if (!empty($data['prerequis_id'])) {
                $prereq = $this->getFormationWithPrerequisite((int)$data['prerequis_id'], $id_user);
                $isUnlocked = ($prereq && $prereq['ma_progression'] >= 100);
            }
            $data['is_unlocked'] = $isUnlocked;
            $data['description'] = strip_tags($data['description']); // Clean for JS
            $formationsData[] = $data;
            if ($data['ma_progression'] >= 100) $globalDone++;
        }

        $globalTotal = count($formationsData);
        $globalPercent = ($globalTotal > 0) ? round(($globalDone / $globalTotal) * 100) : 0;

        return [
            'viewMode' => $viewMode,
            'skillChain' => $skillChain,
            'allTrees' => $allTrees,
            'formationsData' => $formationsData,
            'globalPercent' => $globalPercent
        ];
    }

    public function getStatsGlobales()
    {
        $db = config::getConnexion();
        $stats = ['total_formations' => 0, 'total_inscrits' => 0, 'certificats' => 0, 'taux_completion' => 0];

        try {
            $resF = $db->query("SELECT COUNT(*) as c FROM Formation");
            $stats['total_formations'] = $resF->fetch()['c'];

            $tablesI = ['inscription', 'Inscription'];
            foreach ($tablesI as $table) {
                try {
                    $resI = $db->query("SELECT COUNT(*) as c FROM $table");
                    $stats['total_inscrits'] = $resI->fetch()['c'];
                    $resC = $db->query("SELECT COUNT(*) as c FROM $table WHERE statut = 'Terminée' OR progression >= 100");
                    $stats['certificats'] = $resC->fetch()['c'];
                    break;
                } catch (Exception $e) { if ($table === end($tablesI)) break; }
            }
            if ($stats['total_inscrits'] > 0) $stats['taux_completion'] = round(($stats['certificats'] / $stats['total_inscrits']) * 100);
        } catch (Exception $e) {}

        return $stats;
    }

    public function getTuteurs()
    {
        $db = config::getConnexion();
        $tables = ['utilisateur', 'User'];
        foreach ($tables as $table) {
            try {
                return $db->query("SELECT * FROM $table WHERE LOWER(role) LIKE '%tuteur%'")->fetchAll();
            } catch (Exception $e) { if ($table === end($tables)) return []; }
        }
    }

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
            } catch (Exception $e2) { return []; }
        }

        $palette = ['#6366f1', '#0ea5e9', '#10b981', '#f59e0b', '#ec4899', '#8b5cf6', '#14b8a6', '#ef4444'];
        $tuteurColorMap = []; $paletteIdx = 0;
        foreach ($formations as $f) {
            $tid = $f['id_tuteur'];
            if ($tid && !isset($tuteurColorMap[$tid])) {
                $tuteurColorMap[$tid] = $palette[$paletteIdx % count($palette)];
                $paletteIdx++;
            }
        }

        foreach ($formations as $f) {
            $tid = $f['id_tuteur'];
            $color = $tuteurColorMap[$tid] ?? '#6366f1';
            $dateBase = substr($f['date_formation'], 0, 10);
            $events[] = [
                'id' => 'f_' . $f['id_formation'],
                'title' => $f['tuteur_nom'] . ' — ' . $f['titre'],
                'start' => $dateBase . 'T09:00:00',
                'end' => $dateBase . 'T10:00:00',
                'backgroundColor' => $color,
                'borderColor' => $color,
                'extendedProps' => [
                    'id_tuteur' => $tid, 'tuteur_nom' => $f['tuteur_nom'],
                    'titre' => $f['titre'], 'type' => 'formation',
                    'lieu' => $f['is_online'] ? 'En ligne' : 'Présentiel',
                    'domaine' => $f['domaine'], 'niveau' => $f['niveau'],
                ]
            ];
        }
        return $events;
    }

    private function validateFormation($formation, $isUpdate = false)
    {
        $titre = trim($formation->getTitre());
        if (empty($titre)) throw new Exception("Le titre est obligatoire.");
        if (strlen($titre) < 5 || strlen($titre) > 100) throw new Exception("Le titre doit contenir entre 5 et 100 caractères.");

        $descriptionText = trim(strip_tags($formation->getDescription()));
        if (empty($descriptionText)) throw new Exception("La description est obligatoire.");
        if (strlen($descriptionText) < 10) throw new Exception("La description doit contenir au moins 10 caractères.");

        if (empty(trim($formation->getDomaine()))) throw new Exception("Le domaine est obligatoire.");
        if (empty(trim($formation->getNiveau()))) throw new Exception("Le niveau est obligatoire.");

        if (empty($formation->getDateFormation())) throw new Exception("La date de formation est obligatoire.");
        
        // Only check if date is in the past if it's a NEW formation
        if (!$isUpdate && strtotime($formation->getDateFormation()) < strtotime(date('Y-m-d'))) {
            throw new Exception("La date de formation ne peut pas être dans le passé.");
        }

        if (!empty($formation->getDateFin()) && !empty($formation->getDateFormation())) {
            if (strtotime($formation->getDateFin()) < strtotime($formation->getDateFormation())) throw new Exception("La date de fin ne peut pas être avant la date de début.");
        }

        $duree = trim($formation->getDuree());
        if (!empty($duree) && !preg_match('/^\d+\s*[A-Za-z]+$/', $duree)) throw new Exception("La durée doit avoir un format 'numérique + unité' (ex: 10h).");
        if (empty($formation->getIdTuteur())) throw new Exception("Vous devez sélectionner un tuteur.");
    }

    private function generateJitsiLink($titre)
    {
        $slug = preg_replace('/[^a-zA-Z0-9]+/', '-', strtolower($titre));
        return "https://meet.jit.si/Aptus_" . $slug . "_" . uniqid();
    }

    public function addFormation($formation)
    {
        $this->validateFormation($formation, false);
        $lien = $formation->getLienApiRoom();
        if ($formation->getIsOnline() == 1 && empty($lien)) {
            $lien = $this->generateJitsiLink($formation->getTitre());
        }

        $db = config::getConnexion();
        try {
            $query = $db->prepare("
                INSERT INTO Formation 
                (titre, description, domaine, niveau, duree, date_formation, image_base64, id_tuteur, is_online, lien_api_room, prerequis_id, date_fin, statut) 
                VALUES 
                (:titre, :description, :domaine, :niveau, :duree, :date_formation, :image_base64, :id_tuteur, :is_online, :lien_api_room, :prerequis_id, :date_fin, 'active')
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

    public function deleteFormation($id)
    {
        $db = config::getConnexion();
        try {
            $nb_inscrits = 0;
            $tablesI = ['inscription', 'Inscription'];
            foreach ($tablesI as $table) {
                try {
                    $check = $db->prepare("SELECT COUNT(*) FROM $table WHERE id_formation = :id");
                    $check->execute(['id' => $id]);
                    $nb_inscrits = $check->fetchColumn();
                    break;
                } catch (Exception $e) { if ($table === end($tablesI)) break; }
            }

            if ($nb_inscrits > 0) throw new Exception("Impossible de supprimer une formation active avec des inscrits.");

            try { $db->prepare("DELETE FROM rapport_emotions WHERE id_formation = :id")->execute(['id' => $id]); } catch (Exception $e) {}

            $query = $db->prepare("DELETE FROM Formation WHERE id_formation = :id");
            return $query->execute(['id' => $id]);
        } catch (Exception $e) { throw $e; }
    }

    public function updateFormation($formation, $id)
    {
        $this->validateFormation($formation, true);
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

    public function getFormationById($id)
    {
        $db = config::getConnexion();
        $tables = ['utilisateur', 'User'];
        foreach ($tables as $table) {
            try {
                $query = $db->prepare("
                    SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom 
                    FROM Formation f 
                    LEFT JOIN $table u ON f.id_tuteur = u.id 
                    WHERE f.id_formation = :id
                ");
                $query->execute(['id' => $id]);
                $res = $query->fetch();
                if ($res) return $res;
            } catch (Exception $e) { if ($table === end($tables)) throw $e; }
        }
        return null;
    }

    public function updateLienRoom($id, $lien)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE Formation SET lien_api_room = :lien WHERE id_formation = :id");
            $query->execute(['id' => $id, 'lien' => $lien]);
        } catch (Exception $e) { throw new Exception('Erreur: ' . $e->getMessage()); }
    }

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
            $raw = $query->fetchAll();
            
            $formatted = [];
            foreach ($raw as $f) {
                $formatted[] = $this->formatFormationForView($f);
            }
            return $formatted;
        } catch (Exception $e) { return []; }
    }

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
        exit();
    }

    // --- LOGIQUE SKILL TREE ---

    public function getFormationWithPrerequisite(int $id_formation, ?int $id_user = null): ?array
    {
        $db = config::getConnexion();
        $tablesU = ['utilisateur', 'User'];
        $tablesI = ['inscription', 'Inscription'];
        
        foreach ($tablesU as $tU) {
            foreach ($tablesI as $tI) {
                try {
                    $sql = "SELECT f.*, COALESCE(u.nom, 'Aptus') AS tuteur_nom, COALESCE(i.progression, 0) AS ma_progression, COALESCE(i.statut, '') AS mon_statut FROM Formation f LEFT JOIN $tU u ON f.id_tuteur = u.id LEFT JOIN $tI i ON i.id_formation = f.id_formation AND i.id_user = :id_user WHERE f.id_formation = :id";
                    $stmt = $db->prepare($sql);
                    $stmt->execute(['id' => $id_formation, 'id_user' => $id_user ?? 0]);
                    $res = $stmt->fetch();
                    if ($res) {
                        // Smart Logic Sync : Respect du statut terminé
                        if ($res['mon_statut'] === 'Terminée') {
                            $res['ma_progression'] = 100;
                        }
                        return $res;
                    }
                } catch (Exception $e) { }
            }
        }
        return null;
    }

    public function getSkillTree(int $id_formation_finale, ?int $id_user = null, int $depth = 0): array
    {
        if ($depth >= 10) return [];
        $formation = $this->getFormationWithPrerequisite($id_formation_finale, $id_user);
        if (!$formation) return [];
        $chain = [];
        if (!empty($formation['prerequis_id'])) {
            $prerequisChain = $this->getSkillTree((int) $formation['prerequis_id'], $id_user, $depth + 1);
            $chain = array_merge($chain, $prerequisChain);
        }
        if (!empty($formation['prerequis_id'])) {
            $prereq = $this->getFormationWithPrerequisite((int) $formation['prerequis_id'], $id_user);
            $formation['is_unlocked'] = ($prereq && $prereq['ma_progression'] >= 100);
        } else { $formation['is_unlocked'] = true; }
        $chain[] = $formation;
        return $chain;
    }

    public function getAllFormationsWithSkillTree(?int $id_user = null): array
    {
        $db = config::getConnexion();
        try {
            $sql = "SELECT id_formation FROM Formation WHERE prerequis_id IS NULL OR prerequis_id = 0 ORDER BY domaine, titre";
            $stmt = $db->query($sql);
            $roots = $stmt->fetchAll();
            $trees = [];
            foreach ($roots as $root) {
                $children = $this->getChildrenOf((int) $root['id_formation'], $id_user);
                if (!empty($children)) {
                    $rootFormation = $this->getFormationWithPrerequisite((int) $root['id_formation'], $id_user);
                    if ($rootFormation) {
                        $rootFormation['is_unlocked'] = true;
                        $trees[] = ['root' => $rootFormation, 'children' => $children];
                    }
                }
            }
            return $trees;
        } catch (Exception $e) { return []; }
    }

    /**
     * LOGIQUE MÉTIER CATALOGUE (MVC COMPLIANCE)
     */
    public function getCatalogData($filters) {
        $liste = $this->listerFormations(); // Déjà formaté par formatFormationForView
        
        $q = $filters['q'] ?? '';
        $domaine = $filters['domaine'] ?? '';
        $niveau = $filters['niveau'] ?? '';
        $sort = $filters['sort'] ?? 'date_desc';
        $page = (int)($filters['page'] ?? 1);
        $perPage = 6;

        $formations = [];
        $domainesMap = [];
        
        foreach($liste as $f) {
            if (!empty($f['domaine'])) {
                $domainesMap[$f['domaine']] = true;
            }
            
            $match = true;
            if ($q && stripos($f['titre'], $q) === false && stripos($f['description'], $q) === false) $match = false;
            if ($domaine && $f['domaine'] != $domaine) $match = false;
            if ($niveau && $f['niveau'] != $niveau) $match = false;
            
            if ($match) {
                $formations[] = $f;
            }
        }

        // Tri logic
        usort($formations, function($a, $b) use ($sort) {
            if ($sort === 'date_asc') return $a['id_formation'] <=> $b['id_formation'];
            if ($sort === 'titre_asc') return strcasecmp($a['titre'], $b['titre']);
            return $b['id_formation'] <=> $a['id_formation'];
        });

        $totalFormations = count($formations);
        $totalPages = ceil($totalFormations / $perPage);
        $offset = ($page - 1) * $perPage;
        $formationsPage = array_slice($formations, $offset, $perPage);

        return [
            'formationsPage' => $formationsPage,
            'totalFormations' => $totalFormations,
            'totalPages' => $totalPages,
            'domaines' => array_keys($domainesMap)
        ];
    }

    private function getChildrenOf(int $parent_id, ?int $id_user = null): array
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT id_formation FROM Formation WHERE prerequis_id = :pid");
            $stmt->execute(['pid' => $parent_id]);
            $childIds = $stmt->fetchAll();
            $parentFormation = $this->getFormationWithPrerequisite($parent_id, $id_user);
            $children = [];
            foreach ($childIds as $row) {
                $child = $this->getFormationWithPrerequisite((int) $row['id_formation'], $id_user);
                if ($child) {
                    $child['is_unlocked'] = ($parentFormation && $parentFormation['ma_progression'] >= 100);
                    $children[] = $child;
                }
            }
            return $children;
        } catch (Exception $e) { return []; }
    }

    public function annuler(int $id)
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
            header('Location: formations_admin.php'); exit();
        }
        try {
            $f = $this->getFormationById($id);
            if (!$f) throw new Exception("Formation introuvable.");
            if ((isset($f['statut']) && $f['statut'] === 'annulée')) throw new Exception("Cette formation est déjà annulée.");
            $db = config::getConnexion();
            $db->beginTransaction();
            $db->prepare("UPDATE Formation SET statut = 'annulée' WHERE id_formation = ?")->execute([$id]);
            try { $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_formation = ?")->execute([$id]);
            } catch (Exception $e) { $db->prepare("UPDATE Inscription SET statut = 'annulée' WHERE id_formation = ?")->execute([$id]); }
            $db->commit();
            $_SESSION['flash_success'] = "La formation et ses inscriptions ont été annulées avec succès.";
        } catch (Exception $e) { if (isset($db) && $db->inTransaction()) $db->rollBack(); $_SESSION['flash_error'] = $e->getMessage(); }
        header('Location: formations_admin.php'); exit();
    }

    public function rechercherFormations($search = '', $domaine = '', $niveau = '')
    {
        $db = config::getConnexion();
        $sql = "SELECT f.*, COALESCE(u.nom, 'Aptus') as tuteur_nom FROM Formation f LEFT JOIN utilisateur u ON f.id_tuteur = u.id WHERE 1=1";
        $params = [];
        if (!empty($search)) { $sql .= " AND (f.titre LIKE :search OR f.domaine LIKE :search OR f.description LIKE :search)"; $params['search'] = '%' . $search . '%'; }
        if (!empty($domaine)) { $sql .= " AND f.domaine = :domaine"; $params['domaine'] = $domaine; }
        if (!empty($niveau)) { $sql .= " AND f.niveau = :niveau"; $params['niveau'] = $niveau; }
        $sql .= " ORDER BY f.date_formation DESC";
        $query = $db->prepare($sql); $query->execute($params);
        return $query->fetchAll();
    }

    /**
     * 🧩 LOGIQUE MÉTIER CENTRALISÉE (MVC COMPLIANCE)
     * Regroupe toutes les données nécessaires à la visionneuse de cours
     */
    public function getFormationViewerData($id_formation, $id_user)
    {
        require_once __DIR__ . '/TuteurDashboardController.php';
        require_once __DIR__ . '/InscriptionController.php';
        
        $tuteurC = new TuteurDashboardController();
        $inscriC = new InscriptionController();

        $formation = $this->getFormationById($id_formation);
        if (!$formation) return null;

        $resources = $tuteurC->getResources($id_formation);
        $current_progression = $inscriC->getCurrentProgression($id_formation, $id_user);
        $viewed_chapters = $inscriC->getViewedChapters($id_user, $id_formation);

        // Calculs métier / Presentation logic
        $clean_desc = preg_replace('/<!-- APTUS_RESOURCES: .*? -->/s', '', $formation['description']);
        $word_count = str_word_count(strip_tags($clean_desc));
        $min_read_seconds = max(180, (int)round($word_count / 4.17));
        $has_chapters = !empty($resources);
        
        // Date formatting centralisée
        $mois = ['January'=>'Janvier','February'=>'Février','March'=>'Mars','April'=>'Avril','May'=>'Mai','June'=>'Juin','July'=>'Juillet','August'=>'Août','September'=>'Septembre','October'=>'Octobre','November'=>'Novembre','December'=>'Décembre'];
        $current_month_fr = $mois[date('F')] . ' ' . date('Y');

        return [
            'formation'           => $this->formatFormationForView($formation),
            'resources'           => $resources,
            'current_progression' => $current_progression,
            'viewed_chapters'     => $viewed_chapters,
            'clean_desc'          => $clean_desc,
            'word_count'          => $word_count,
            'min_read_seconds'    => $min_read_seconds,
            'has_chapters'        => $has_chapters,
            'total_chapters'      => count($resources),
            'current_month_fr'    => $current_month_fr,
            'reading_time_est'    => max(1, round($word_count / 250))
        ];
    }

    /**
     * 🚀 ROUTEUR AJAX CENTRALISÉ (BACK-OFFICE)
     */
    public function handleAjax()
    {
        $action = $_REQUEST['action'] ?? '';
        
        switch ($action) {
            case 'search_formations':
                $search  = $_GET['s'] ?? '';
                $domaine = $_GET['d'] ?? '';
                $niveau  = $_GET['n'] ?? '';
                $this->renderSearchRows($search, $domaine, $niveau);
                break;

            case 'add_formation':
                header('Content-Type: application/json');
                echo json_encode($this->add_formation_handler($_POST, $_FILES));
                break;

            case 'edit_formation':
                header('Content-Type: application/json');
                echo json_encode($this->edit_formation_handler($_POST, $_FILES));
                break;

            case 'delete_formation':
                header('Content-Type: application/json');
                $id = (int)($_POST['id'] ?? 0);
                try {
                    $this->deleteFormation($id);
                    echo json_encode(['success' => true, 'message' => 'Formation supprimée.']);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                }
                break;
                
            default:
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
        }
    }

    private function renderSearchRows($search, $domaine, $niveau)
    {
        $rawFormations = $this->rechercherFormations($search, $domaine, $niveau);
        $listeFormations = array_map([$this, 'formatFormationForView'], $rawFormations);
        
        // On inclut le partial pour le rendu
        include __DIR__ . '/../view/backoffice/admin_table_rows_partial.php';
    }

    private function add_formation_handler($data, $files) {
        try {
            $image_base64 = "";
            if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
                $image_data = file_get_contents($files['image']['tmp_name']);
                $type = strtolower(pathinfo($files['image']['name'], PATHINFO_EXTENSION));
                $image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($image_data);
            }

            $f = new Formation(
                $data['titre'],
                $data['description'],
                $data['domaine'],
                $data['niveau'],
                $data['duree'] ?? '0',
                $data['date_formation'],
                $image_base64,
                !empty($data['id_tuteur']) ? (int)$data['id_tuteur'] : null,
                (int)$data['is_online'],
                trim($data['online_url'] ?? ''),
                !empty($data['prerequis_id']) ? (int)$data['prerequis_id'] : null,
                !empty($data['date_fin']) ? $data['date_fin'] : null
            );

            $this->addFormation($f);
            return ['success' => true, 'type' => 'success', 'message' => 'Formation ajoutée avec succès !'];
        } catch (Exception $e) {
            return ['success' => false, 'type' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function edit_formation_handler($data, $files) {
        $id = (int)($data['id_formation'] ?? 0);
        if (!$id) return ['success' => false, 'message' => 'ID manquant.'];

        $formation_old = $this->getFormationById($id);
        if (!$formation_old) return ['success' => false, 'message' => 'Formation non trouvée.'];

        $is_online = (int) ($data['is_online'] ?? 0);
        $lien_room = trim($data['online_url'] ?? '');

        $image_base64 = $formation_old['image_base64'];
        if (isset($files['image']) && $files['image']['error'] === UPLOAD_ERR_OK) {
            $image_data = file_get_contents($files['image']['tmp_name']);
            $type = strtolower(pathinfo($files['image']['name'], PATHINFO_EXTENSION));
            $image_base64 = 'data:image/' . $type . ';base64,' . base64_encode($image_data);
        }

        try {
            $f = new Formation(
                $data['titre'],
                $data['description'],
                $data['domaine'],
                $data['niveau'],
                $data['duree'] ?? '0',
                $data['date_formation'],
                $image_base64,
                !empty($data['id_tuteur']) ? (int) $data['id_tuteur'] : null,
                $is_online,
                $lien_room,
                !empty($data['prerequis_id']) ? (int)$data['prerequis_id'] : null,
                !empty($data['date_fin']) ? $data['date_fin'] : null
            );

            $this->updateFormation($f, $id);
            return ['success' => true, 'message' => 'Formation modifiée avec succès.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
