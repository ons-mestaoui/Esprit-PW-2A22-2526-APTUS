<?php
// InscriptionController : gère les inscriptions/desinscriptions des candidats
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Inscription.php';

class InscriptionController
{
    // Récupère les données complètes pour générer un certificat (Version ultra-robuste)
    public function getCertificateAccessData($id_user, $id_formation)
    {
        $db = config::getConnexion();
        
        // 1. On récupère les infos de base (progression + titre formation)
        $stmt = $db->prepare("
            SELECT i.progression, i.statut, f.titre, f.id_tuteur
            FROM inscription i
            JOIN Formation f ON i.id_formation = f.id_formation
            WHERE i.id_user = :uid AND i.id_formation = :fid
        ");
        $stmt->execute(['uid' => $id_user, 'fid' => $id_formation]);
        $res = $stmt->fetch();
        
        if (!$res) {
            $stmt = $db->prepare("
                SELECT i.progression, i.statut, f.titre, f.id_tuteur
                FROM Inscription i
                JOIN Formation f ON i.id_formation = f.id_formation
                WHERE i.id_user = :uid AND i.id_formation = :fid
            ");
            $stmt->execute(['uid' => $id_user, 'fid' => $id_formation]);
            $res = $stmt->fetch();
        }

        if (!$res) return false;

        // Force 100% si le statut est terminé (Sécurité Smart Sync)
        if ($res['statut'] === 'Terminée') {
            $res['progression'] = 100;
        }

        // 2. On essaie de récupérer le NOM du candidat (utilisateur ou candidat)
        $res['user_nom'] = 'Candidat Aptus';
        $res['role'] = 'Candidat';
        
        try {
            // Priorité à la table utilisateur
            $stmtU = $db->prepare("SELECT nom, role FROM utilisateur WHERE id = ?");
            $stmtU->execute([$id_user]);
            $u = $stmtU->fetch();
            if ($u) {
                $res['user_nom'] = $u['nom'];
                $res['role'] = $u['role'];
            } else {
                // Fallback table candidat
                $stmtC = $db->prepare("SELECT nom, 'Candidat' as role FROM candidat WHERE id = ?");
                $stmtC->execute([$id_user]);
                $c = $stmtC->fetch();
                if ($c) {
                    $res['user_nom'] = $c['nom'];
                    $res['role'] = 'Candidat';
                }
            }
        } catch (Exception $e) { /* On garde les valeurs par défaut */ }

        // 3. On récupère le NOM du tuteur
        $res['tuteur_nom'] = 'Responsable Aptus';
        if (!empty($res['id_tuteur'])) {
            try {
                $stmtT = $db->prepare("SELECT nom FROM utilisateur WHERE id = ?");
                $stmtT->execute([$res['id_tuteur']]);
                $t = $stmtT->fetch();
                if ($t) $res['tuteur_nom'] = $t['nom'];
            } catch (Exception $e) { }
        }

        return $res;
    }

    // Vérifie si un utilisateur est déjà inscrit à une formation
    public function isUserInscribed($id_formation, $id_user)
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = ? AND id_user = ?");
            $stmt->execute([$id_formation, $id_user]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM Inscription WHERE id_formation = ? AND id_user = ?");
                $stmt->execute([$id_formation, $id_user]);
                return $stmt->fetchColumn() > 0;
            } catch (Exception $e2) {
                return false;
            }
        }
    }

    // --- ALGORITHME SMART PROGRESSION ---
    // Recalcule la progression réelle basée sur les chapitres vus
    public function calculateSmartPercentage($id_user, $id_formation, $total_chapters)
    {
        if ($total_chapters <= 0) return 0;
        
        $db = config::getConnexion();
        // On récupère la liste des chapitres vus stockée en JSON dans 'commentaires' (ou une colonne libre)
        // Alternative : on utilise une table dédiée si elle existe, sinon on reste sur une approche agile
        try {
            $stmt = $db->prepare("SELECT chapitres_vus FROM inscription WHERE id_user = ? AND id_formation = ?");
            $stmt->execute([$id_user, $id_formation]);
            $json = $stmt->fetchColumn();
            
            $vus = $json ? json_decode($json, true) : [];
            if (!is_array($vus)) $vus = [];
            
            $count_vus = count($vus);
            $percentage = min(100, (int)round(($count_vus / $total_chapters) * 100));
            
            // Mise à jour de la progression réelle en BDD
            $this->updateProgressionValue($id_user, $id_formation, $percentage);
            
            return $percentage;
        } catch (Exception $e) { return 0; }
    }

    public function markChapterAsViewed($id_user, $id_formation, $id_chapter, $total_chapters)
    {
        $db = config::getConnexion();
        try {
            // 1. Récupérer les chapitres déjà vus
            $stmt = $db->prepare("SELECT chapitres_vus FROM inscription WHERE id_user = ? AND id_formation = ?");
            $stmt->execute([$id_user, $id_formation]);
            $json = $stmt->fetchColumn();
            
            $vus = $json ? json_decode($json, true) : [];
            if (!is_array($vus)) $vus = [];
            
            // 2. Ajouter le nouveau chapitre s'il n'y est pas déjà
            if (!in_array($id_chapter, $vus)) {
                $vus[] = $id_chapter;
                $new_json = json_encode($vus);
                
                $stmtU = $db->prepare("UPDATE inscription SET chapitres_vus = ? WHERE id_user = ? AND id_formation = ?");
                $stmtU->execute([$new_json, $id_user, $id_formation]);
            }
            
            // 3. Recalculer le pourcentage global
            return $this->calculateSmartPercentage($id_user, $id_formation, $total_chapters);
        } catch (Exception $e) {
            // Si la colonne 'chapitres_vus' n'existe pas encore (fallback), on peut logger l'erreur
            return 0;
        }
    }

    private function updateProgressionValue($id_user, $id_formation, $percentage)
    {
        $db = config::getConnexion();
        $sql = "UPDATE inscription SET progression = ? WHERE id_user = ? AND id_formation = ?";
        $db->prepare($sql)->execute([$percentage, $id_user, $id_formation]);
        
        // Si 100%, on passe le statut à Terminée
        if ($percentage >= 100) {
            $db->prepare("UPDATE inscription SET statut = 'Terminée' WHERE id_user = ? AND id_formation = ? AND statut != 'Terminée'")
               ->execute([$id_user, $id_formation]);
        }
    }
    // -------------------------------------

    // Récupère la progression actuelle d'un étudiant pour une formation
    public function getCurrentProgression($id_formation, $id_user)
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT progression FROM inscription WHERE id_formation = :f AND id_user = :u LIMIT 1");
            $stmt->execute(['f' => $id_formation, 'u' => $id_user]);
            $res = $stmt->fetchColumn();
            return $res ? (int)$res : 0;
        } catch (Exception $e) {
            try {
                $stmt = $db->prepare("SELECT progression FROM Inscription WHERE id_formation = :f AND id_user = :u LIMIT 1");
                $stmt->execute(['f' => $id_formation, 'u' => $id_user]);
                $res = $stmt->fetchColumn();
                return $res ? (int)$res : 0;
            } catch (Exception $e2) {
                return 0;
            }
        }
    }

    // Récupère les formations auxquelles un candidat est inscrit
    // On fait une jointure pour avoir aussi les infos de la formation et du tuteur
    public function listerMesFormations($id_user)
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("
                SELECT f.*, i.statut, i.progression, 
                       COALESCE(u.nom, 'Aptus') as tuteur_nom
                FROM inscription i
                JOIN Formation f ON i.id_formation = f.id_formation
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id
                WHERE i.id_user = ?
                ORDER BY i.date_inscription DESC
            ");
            $stmt->execute([$id_user]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            try {
                $stmt = $db->prepare("
                    SELECT f.*, i.statut, i.progression, 
                           COALESCE(u.nom, 'Aptus') as tuteur_nom
                    FROM Inscription i
                    JOIN Formation f ON i.id_formation = f.id_formation
                    LEFT JOIN User u ON f.id_tuteur = u.id
                    WHERE i.id_user = ?
                    ORDER BY i.date_inscription DESC
                ");
                $stmt->execute([$id_user]);
                return $stmt->fetchAll();
            } catch (Exception $e2) {
                return [];
            }
        }
    }

    /**
     * 🧩 LOGIQUE MÉTIER "MES FORMATIONS" (MVC COMPLIANCE)
     */
    public function getMyFormationsPageData($id_user) {
        require_once __DIR__ . '/TuteurDashboardController.php';
        require_once __DIR__ . '/FormationController.php';
        $tuteurC = new TuteurDashboardController();
        $formationC = new FormationController();

        $mesCoursRaw = $this->listerMesFormations($id_user);
        $mesCours = [];
        
        $completedCours = 0;
        $enCoursCours = 0;
        $annuleeCours = 0;

        foreach ($mesCoursRaw as $c) {
            // Smart Progression logic
            if ($c['statut'] !== 'Terminée' && $c['progression'] < 100) {
                $resources = $tuteurC->getResources($c['id_formation']);
                if (!empty($resources)) {
                    $c['progression'] = $this->calculateSmartPercentage($id_user, $c['id_formation'], count($resources));
                } else {
                    $c['progression'] = 0;
                }
            } else {
                $c['progression'] = 100;
            }

            // Stats logic
            if ($c['progression'] == 100 || $c['statut'] === 'Terminée') {
                $completedCours++;
                $c['filter_cat'] = 'terminee';
            } elseif ($c['statut'] === 'annulée') {
                $annuleeCours++;
                $c['filter_cat'] = 'annulee';
            } else {
                $enCoursCours++;
                $c['filter_cat'] = 'en-cours';
            }

            // Date & Availability logic
            $dateF = date('Y-m-d', strtotime($c['date_formation']));
            $c['is_available'] = ($dateF <= date('Y-m-d'));
            $c['display_statut'] = (!$c['is_available'] && $c['statut'] !== 'annulée') ? 'En attente' : $c['statut'];
            $c['date_format_brut'] = date('d/m/Y', strtotime($c['date_formation']));

            // Use common formatter
            $mesCours[] = $formationC->formatFormationForView($c);
        }

        $totalCours = count($mesCours);
        $globalProgress = $totalCours > 0 ? round(($completedCours / $totalCours) * 100) : 0;

        return [
            'mesCours' => $mesCours,
            'totalCours' => $totalCours,
            'completedCours' => $completedCours,
            'enCoursCours' => $enCoursCours,
            'annuleeCours' => $annuleeCours,
            'globalProgress' => $globalProgress
        ];
    }

    // Marquer une formation comme terminée (progression = 100%)
    // Contrainte : on ne peut pas terminer une formation dont la date est dans le futur
    public function terminerFormation($id_formation, $id_user)
    {
        $db = config::getConnexion();

        // 1. Contrainte de date (PHP)
        $stmt = $db->prepare("SELECT date_formation FROM Formation WHERE id_formation = ?");
        $stmt->execute([$id_formation]);
        $date_f = $stmt->fetchColumn();

        if (!$date_f || strtotime($date_f) > strtotime(date('Y-m-d'))) {
            throw new Exception("Impossible de terminer une formation prévue dans le futur.");
        }

        // 2. Mise à jour du statut
        try {
            $update = $db->prepare("UPDATE inscription SET statut = 'Terminée', progression = 100 WHERE id_formation = ? AND id_user = ?");
            $update->execute([$id_formation, $id_user]);
            
            // --- NOUVEAU : SYSTÈME DE GAMIFICATION ---
            // 3. Attribution du Badge correspondant au niveau
            $stmtInfo = $db->prepare("SELECT niveau FROM formation WHERE id_formation = ?");
            $stmtInfo->execute([$id_formation]);
            $niveau = $stmtInfo->fetchColumn();

            if ($niveau) {
                require_once __DIR__ . '/BadgeController.php';
                $badgeC = new BadgeController();
                $badgeC->attribuerBadgeNiveau($id_user, $niveau);
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getViewedChapters($id_user, $id_formation)
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT chapitres_vus FROM inscription WHERE id_user = ? AND id_formation = ?");
            $stmt->execute([$id_user, $id_formation]);
            $json = $stmt->fetchColumn();
            $vus = $json ? json_decode($json, true) : [];
            return is_array($vus) ? $vus : [];
        } catch (Exception $e) { return []; }
    }
    // Récupérer la collection de badges d'un utilisateur
    public function getMesBadges($id_user)
    {
        try {
            $db = config::getConnexion();
            $stmt = $db->prepare("
                SELECT b.*, ub.date_obtention 
                FROM user_badges ub 
                JOIN badge b ON ub.id_badge = b.id_badge 
                WHERE ub.id_user = ?
                ORDER BY ub.date_obtention DESC
            ");
            $stmt->execute([$id_user]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    // Inscription d'un candidat à une formation
    public function inscrire($id_formation, $id_user)
    {
        $db = config::getConnexion();
        try {
            // Check if already inscribed
            try {
                $stmt = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = ? AND id_user = ?");
                $stmt->execute([$id_formation, $id_user]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Vous êtes déjà inscrit à cette formation.");
                }
            } catch (Exception $e) {
                // Table might be capitalized
                $stmt = $db->prepare("SELECT COUNT(*) FROM Inscription WHERE id_formation = ? AND id_user = ?");
                $stmt->execute([$id_formation, $id_user]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("Vous êtes déjà inscrit à cette formation.");
                }
            }

            try {
                $stmt = $db->prepare("INSERT INTO inscription (id_user, id_formation, date_inscription, statut, progression) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_user, $id_formation, date('Y-m-d'), 'En attente', 0]);
            } catch (Exception $e) {
                $stmt = $db->prepare("INSERT INTO Inscription (id_user, id_formation, date_inscription, statut, progression) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$id_user, $id_formation, date('Y-m-d'), 'En attente', 0]);
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Désinscription d'un candidat (action front-office)
    // Contraintes vérifiées ici dans le contrôleur (pas dans le model)
    public function desinscrire()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_formation'])) {
            $id_formation = (int)$_POST['id_formation'];
            $id_user = $_SESSION['user_id'] ?? 10; // Récupère l'ID via session ou 10 pour la démo

            try {
                $db = config::getConnexion();

                // Contrainte 1 : Bloquer si la formation a déjà commencé (date passée)
                $stmtF = $db->prepare("SELECT date_formation FROM Formation WHERE id_formation = ?");
                $stmtF->execute([$id_formation]);
                $date_f = $stmtF->fetchColumn();

                if ($date_f && strtotime($date_f) <= strtotime(date('Y-m-d'))) {
                    throw new Exception("Impossible de se désinscrire : la formation a déjà commencé ou est passée.");
                }

                // Contrainte 2 : Bloquer si le statut de l'inscription est 'Terminée'
                $stmtI = null;
                try {
                    $stmtI = $db->prepare("SELECT statut FROM inscription WHERE id_formation = ? AND id_user = ?");
                    $stmtI->execute([$id_formation, $id_user]);
                } catch (Exception $e) {
                    $stmtI = $db->prepare("SELECT statut FROM Inscription WHERE id_formation = ? AND id_user = ?");
                    $stmtI->execute([$id_formation, $id_user]);
                }

                $statut_actuel = $stmtI->fetchColumn();
                // On ne bloque la désinscription que si la formation a COMMENCÉ ET qu'elle est en cours/terminée
                if (strtotime($date_f) <= strtotime(date('Y-m-d')) && ($statut_actuel === 'En cours' || $statut_actuel === 'Terminée')) {
                    throw new Exception("Impossible de se désinscrire d'une formation qui a déjà commencé ou qui est terminée.");
                }

                // Suppression de l'inscription
                try {
                    $delete = $db->prepare("DELETE FROM inscription WHERE id_formation = ? AND id_user = ?");
                    $delete->execute([$id_formation, $id_user]);
                    if ($delete->rowCount() == 0) {
                        $delete = $db->prepare("DELETE FROM Inscription WHERE id_formation = ? AND id_user = ?");
                        $delete->execute([$id_formation, $id_user]);
                    }
                } catch (Exception $e) {
                    throw new Exception("Erreur système lors de la désinscription.");
                }

                $_SESSION['flash_success'] = "Vous vous êtes désinscrit de la formation avec succès.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
            header("Location: formations_my.php");
            exit();
        }
    }

    // Annulation d'une inscription par l'admin (back-office)
    // On vérifie que c'est bien un admin avant de faire quoi que ce soit
    public function annulerAdmin()
    {
        if (isset($_GET['id_inscription'])) {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
                header("Location: formations_admin.php");
                exit();
            }

            try {
                $db = config::getConnexion();
                try {
                    $update = $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_inscri = ?");
                    $update->execute([(int)$_GET['id_inscription']]);
                } catch(Exception $e) {
                    $update = $db->prepare("UPDATE Inscription SET statut = 'annulée' WHERE id_inscri = ?");
                    $update->execute([(int)$_GET['id_inscription']]);
                }
                $_SESSION['flash_success'] = "L'inscription a été annulée.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation de l'inscription.";
            }
            header("Location: formations_admin.php");
            exit();
        }
    }

    // Changement de statut d'une inscription par l'admin
    // Statuts possibles : En attente, En cours, Terminée, annulée, shortlisté, refusé
    public function updateStatut()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_inscription']) && isset($_POST['statut'])) {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
                header("Location: formations_admin.php");
                exit();
            }

            // Contrainte : on vérifie que le statut demandé est dans la liste autorisée
            $statuts_autorises = ['En attente', 'En cours', 'Terminée', 'annulée', 'shortlisté', 'refusé'];
            if (!in_array($_POST['statut'], $statuts_autorises)) {
                $_SESSION['flash_error'] = "Statut invalide.";
                header("Location: formations_admin.php");
                exit();
            }

            try {
                $db = config::getConnexion();
                try {
                    $update = $db->prepare("UPDATE inscription SET statut = ? WHERE id_inscri = ?");
                    $update->execute([$_POST['statut'], (int)$_POST['id_inscription']]);
                } catch(Exception $e) {
                    $update = $db->prepare("UPDATE Inscription SET statut = ? WHERE id_inscri = ?");
                    $update->execute([$_POST['statut'], (int)$_POST['id_inscription']]);
                }
                $_SESSION['flash_success'] = "Le statut de l'inscription a été mis à jour.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
            header("Location: formations_admin.php");
            exit();
        }
    }

    /**
     * Point d'entrée centralisé pour les requêtes AJAX liées aux inscriptions
     */
    public function handleAjax($action, $data)
    {
        require_once __DIR__ . '/SessionManager.php';
        switch ($action) {
            case 'inscrire':
                $id_f = (int)($data['id_formation'] ?? 0);
                $id_u = (int)($data['id_user'] ?? SessionManager::getUserId());
                if (!$id_f || !$id_u) return ['success' => false, 'message' => 'Données manquantes.'];
                try {
                    $this->inscrire($id_f, $id_u);
                    return ['success' => true, 'message' => 'Inscription réussie !'];
                } catch (Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }

            case 'desinscrire':
                $id_f = (int)($data['id_formation'] ?? 0);
                $id_u = (int)($data['id_user'] ?? $_SESSION['user_id'] ?? 0);
                if (!$id_f || !$id_u) return ['success' => false, 'message' => 'Données manquantes.'];
                try {
                    // Logique de désinscription sécurisée
                    $db = config::getConnexion();
                    $stmtF = $db->prepare("SELECT date_formation FROM Formation WHERE id_formation = ?");
                    $stmtF->execute([$id_f]);
                    $date_f = $stmtF->fetchColumn();
                    if ($date_f && strtotime($date_f) <= strtotime(date('Y-m-d'))) {
                        return ['success' => false, 'message' => "La formation a déjà commencé ou est passée."];
                    }
                    
                    $stmtI = $db->prepare("DELETE FROM inscription WHERE id_formation = ? AND id_user = ?");
                    $stmtI->execute([$id_f, $id_u]);
                    return ['success' => true, 'message' => 'Désinscription effectuée avec succès.'];
                } catch (Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }

            default:
                return ['success' => false, 'message' => 'Action inconnue dans InscriptionController.'];
        }
    }
}
