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
                if ($table === end($tables_utilisateurs))
                    throw new Exception('Erreur SQL: ' . $e->getMessage());
            }
        }
    }

    // Stats pour le dashboard admin (nb formations, inscrits, certificats, taux)
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
            return $query->fetchAll();
        } catch (Exception $e) { throw new Exception('Erreur: ' . $e->getMessage()); }
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
}
