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
            JOIN formation f ON i.id_formation = f.id_formation
            WHERE i.id_utilisateur = :uid AND i.id_formation = :fid
        ");
        $stmt->execute(['uid' => $id_user, 'fid' => $id_formation]);
        $res = $stmt->fetch();

        if (!$res)
            return false;

        // Force 100% si le statut est terminé (Sécurité Smart Sync)
        if ($res['statut'] === 'Terminée') {
            $res['progression'] = 100;
        }

        // 2. On essaie de récupérer le NOM du candidat (utilisateur ou candidat)
        $res['user_nom'] = 'Candidat Aptus';
        $res['role'] = 'Candidat';

        try {
            // Priorité à la table utilisateur
            $stmtU = $db->prepare("SELECT nom, role FROM utilisateur WHERE id_utilisateur = ?");
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
        } catch (Exception $e) { /* On garde les valeurs par défaut */
        }

        // 3. On récupère le NOM du tuteur
        $res['tuteur_nom'] = 'Responsable Aptus';
        if (!empty($res['id_tuteur'])) {
            try {
                $stmtT = $db->prepare("SELECT nom FROM utilisateur WHERE id_utilisateur = ?");
                $stmtT->execute([$res['id_tuteur']]);
                $t = $stmtT->fetch();
                if ($t)
                    $res['tuteur_nom'] = $t['nom'];
            } catch (Exception $e) {
            }
        }

        return $res;
    }

    // Vérifie si un utilisateur est déjà inscrit à une formation
    public function isUserInscribed($id_formation, $id_user)
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT COUNT(*) FROM inscription WHERE id_formation = ? AND id_utilisateur = ?");
            $stmt->execute([$id_formation, $id_user]);
            return $stmt->fetchColumn() > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    // --- ALGORITHME SMART PROGRESSION ---
    /**
     * Valide mathématiquement la progression basée sur le temps de lecture (Smart Dwell Time).
     * Empêche la triche en vérifiant le ratio temps passé / mots lus.
     */
    public function validateDwellProgression($dwell_seconds, $word_count, $new_prog, $current_prog)
    {
        // Algorithme de validation (Loi de lecture moyenne : 250 mots/min => 4.17 mots/sec)
        // Plancher technique de 180 secondes (3 min) pour assurer une imprégnation minimale
        $min_required = max(180, ($word_count > 0) ? ($word_count / 4.17) : 180);

        $ratio = ($dwell_seconds > 0) ? min($dwell_seconds / $min_required, 1.0) : 0;
        $calc_prog = (int) round($ratio * 100);

        // On ne valide jamais plus que ce que l'IA a calculé
        $validated = min($new_prog, $calc_prog);

        // On retourne le maximum pour ne jamais régresser dans l'apprentissage
        return max($current_prog, $validated);
    }

    // Recalcule la progression réelle basée sur les chapitres vus
    public function calculateSmartPercentage($id_user, $id_formation, $total_chapters)
    {
        if ($total_chapters <= 0)
            return 0;

        $db = config::getConnexion();
        // On récupère la liste des chapitres vus stockée en JSON dans 'commentaires' (ou une colonne libre)
        // Alternative : on utilise une table dédiée si elle existe, sinon on reste sur une approche agile
        try {
            $stmt = $db->prepare("SELECT chapitres_vus FROM inscription WHERE id_utilisateur = ? AND id_formation = ?");
            $stmt->execute([$id_user, $id_formation]);
            $json = $stmt->fetchColumn();

            $vus = $json ? json_decode($json, true) : [];
            if (!is_array($vus))
                $vus = [];

            $count_vus = count($vus);
            $percentage = min(100, (int) round(($count_vus / $total_chapters) * 100));

            // Mise à jour de la progression réelle en BDD
            $this->updateProgressionValue($id_user, $id_formation, $percentage);

            return $percentage;
        } catch (Exception $e) {
            return 0;
        }
    }

    public function markChapterAsViewed($id_user, $id_formation, $id_chapter, $total_chapters)
    {
        $db = config::getConnexion();
        try {
            // 1. Récupérer les chapitres déjà vus
            $stmt = $db->prepare("SELECT chapitres_vus FROM inscription WHERE id_utilisateur = ? AND id_formation = ?");
            $stmt->execute([$id_user, $id_formation]);
            $json = $stmt->fetchColumn();

            $vus = $json ? json_decode($json, true) : [];
            if (!is_array($vus))
                $vus = [];

            // 2. Ajouter le nouveau chapitre s'il n'y est pas déjà
            if (!in_array($id_chapter, $vus)) {
                $vus[] = $id_chapter;
                $new_json = json_encode($vus);

                $stmtU = $db->prepare("UPDATE inscription SET chapitres_vus = ? WHERE id_utilisateur = ? AND id_formation = ?");
                $stmtU->execute([$new_json, $id_user, $id_formation]);
            }

            // 3. Recalculer le pourcentage global
            return $this->calculateSmartPercentage($id_user, $id_formation, $total_chapters);
        } catch (Exception $e) {
            // Si la colonne 'chapitres_vus' n'existe pas encore (fallback), on peut logger l'erreur
            return 0;
        }
    }

    public function updateProgressionValue($id_user, $id_formation, $percentage)
    {
        $db = config::getConnexion();

        // ── CONTRAINTE STRICTE : progression=100 ↔ statut=Terminée ──────────
        // Déterminer le bon statut en fonction du pourcentage
        if ($percentage >= 100) {
            $percentage = 100; // Jamais au-delà de 100
            $statut = 'Terminée';
        } elseif ($percentage > 0) {
            $statut = 'En cours';
        } else {
            $statut = 'En attente';
        }

        $db->prepare("UPDATE inscription SET progression = ?, statut = ? WHERE id_utilisateur = ? AND id_formation = ?")
            ->execute([$percentage, $statut, $id_user, $id_formation]);
    }
    // -------------------------------------

    // Récupère la progression actuelle d'un étudiant pour une formation
    public function getCurrentProgression($id_formation, $id_user)
    {
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("SELECT progression FROM inscription WHERE id_formation = :f AND id_utilisateur = :u LIMIT 1");
            $stmt->execute(['f' => $id_formation, 'u' => $id_user]);
            $res = $stmt->fetchColumn();
            return $res ? (int) $res : 0;
        } catch (Exception $e) {
            return 0;
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
                JOIN formation f ON i.id_formation = f.id_formation
                LEFT JOIN utilisateur u ON f.id_tuteur = u.id_utilisateur
                WHERE i.id_utilisateur = ?
                ORDER BY i.date_inscription DESC
            ");
            $stmt->execute([$id_user]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🧩 LOGIQUE MÉTIER "MES FORMATIONS" (MVC COMPLIANCE)
     */
    public function getMyFormationsPageData($id_user)
    {
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
            // ── Ne jamais recalculer les formations annulées ──────────────────
            if ($c['statut'] === 'annulée') {
                $c['filter_cat'] = 'annulee';
                $annuleeCours++;
                $c['is_available']       = (date('Y-m-d', strtotime($c['date_formation'])) <= date('Y-m-d'));
                $c['display_statut']     = $c['statut'];
                $c['date_format_brut']   = date('d/m/Y', strtotime($c['date_formation']));
                $mesCours[] = $formationC->formatFormationForView($c);
                continue;
            }

            // ── RÈGLE DE PROGRESSION HYBRIDE : 3 CAS ─────────────────────────────
            //
            // CAS A : Formation avec chapitres + étudiant a ouvert ≥1 chapitre
            //         → chapter-based (source de vérité : chapitres_vus / total actuel)
            //
            // CAS B : Formation avec chapitres + étudiant n'a cliqué AUCUN chapitre
            //         → conserver la valeur DB (dwell-time existant) plafonnée à 99%
            //           (ne jamais régresser le progrès existant, ne jamais atteindre 100%
            //            sans validation chapitres)
            //
            // CAS C : Aucun chapitre configuré par le tuteur
            //         → 0% verrouillé (impossible de progresser sans contenu)
            $resources      = $tuteurC->getResources($c['id_formation']);
            $total_chapters = count($resources);
            $dbProg         = (int)$c['progression'];

            if ($total_chapters > 0) {
                // Vérifier si l'étudiant a déjà interagi avec les chapitres
                $viewedChapters = $this->getViewedChapters($id_user, $c['id_formation']);

                if (!empty($viewedChapters)) {
                    // CAS A : chapitre(s) cliqués → recalcul depuis chapitres_vus
                    $prog = $this->calculateSmartPercentage($id_user, $c['id_formation'], $total_chapters);
                    $c['progression'] = $prog;
                    // Synchroniser le statut
                    if ($prog >= 100)  { $c['statut'] = 'Terminée'; }
                    elseif ($prog > 0) { $c['statut'] = 'En cours'; }
                    else               { $c['statut'] = 'En attente'; }
                } else {
                    // CAS B : aucun chapitre cliqué → garder la valeur DB (dwell-time legacy)
                    // Plafonner à 99% : 100% ne peut venir que des chapitres
                    $prog = min(99, $dbProg);
                    $c['progression'] = $prog;
                    if ($prog > 0) { $c['statut'] = 'En cours'; }
                    else           { $c['statut'] = 'En attente'; }
                    // Si la DB avait 100% (faux positif dwell-time) → corriger en DB
                    if ($dbProg >= 100) {
                        $this->updateProgressionValue($id_user, $c['id_formation'], $prog);
                    }
                }
            } else {
                // CAS C : aucun chapitre configuré → 0% verrouillé
                $c['progression'] = 0;
                $c['statut']      = 'En attente';
                // Auto-corriger la DB si une ancienne valeur dwell-time y était stockée
                if ($dbProg !== 0) {
                    $this->updateProgressionValue($id_user, $c['id_formation'], 0);
                }
            }

            // Stats logic
            if ($c['statut'] === 'Terminée') {
                $completedCours++;
                $c['filter_cat'] = 'terminee';
            } else {
                $enCoursCours++;
                $c['filter_cat'] = 'en-cours';
            }

            // Date & Availability logic
            $dateF = date('Y-m-d', strtotime($c['date_formation']));
            $c['is_available']   = ($dateF <= date('Y-m-d'));
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
        $stmt = $db->prepare("SELECT date_formation FROM formation WHERE id_formation = ?");
        $stmt->execute([$id_formation]);
        $date_f = $stmt->fetchColumn();

        if (!$date_f || strtotime($date_f) > strtotime(date('Y-m-d'))) {
            throw new Exception("Impossible de terminer une formation prévue dans le futur.");
        }

        // 2. Mise à jour du statut
        try {
            $update = $db->prepare("UPDATE inscription SET statut = 'Terminée', progression = 100 WHERE id_formation = ? AND id_utilisateur = ?");
            $update->execute([$id_formation, $id_user]);

            // --- NOUVEAU : SYSTÈME DE GAMIFICATION ---
            // 3. Attribution du Badge correspondant au niveau
            $stmtInfo = $db->prepare("SELECT niveau FROM formation WHERE id_formation = ?");
            $stmtInfo->execute([$id_formation]);
            $niveau = $stmtInfo->fetchColumn();

            if ($niveau) {
                require_once __DIR__ . '/BadgeController.php';
                $badgeC = new BadgeController();
                $badgeC->attribuerBadgeNiveau($id_user, $niveau, $id_formation);
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
            $stmt = $db->prepare("SELECT chapitres_vus FROM inscription WHERE id_utilisateur = ? AND id_formation = ?");
            $stmt->execute([$id_user, $id_formation]);
            $json = $stmt->fetchColumn();
            $vus = $json ? json_decode($json, true) : [];
            return is_array($vus) ? $vus : [];
        } catch (Exception $e) {
            return [];
        }
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
            $stmt = $db->prepare("INSERT INTO inscription (id_utilisateur, id_formation, date_inscription, statut, progression) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$id_user, $id_formation, date('Y-m-d'), 'En attente', 0]);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // Désinscription d'un candidat (action front-office)
    // Contraintes vérifiées ici dans le contrôleur (pas dans le model)
    public function desinscrire()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_formation'])) {
            $id_formation = (int) $_POST['id_formation'];
            $id_user = SessionManager::getUserId() ?? 10; // Récupère l'ID via SessionManager ou 10 pour la démo

            try {
                $db = config::getConnexion();

                // Contrainte 1 : Bloquer si la formation a déjà commencé (date passée)
                $stmtF = $db->prepare("SELECT date_formation FROM formation WHERE id_formation = ?");
                $stmtF->execute([$id_formation]);
                $date_f = $stmtF->fetchColumn();

                if ($date_f && strtotime($date_f) <= strtotime(date('Y-m-d'))) {
                    throw new Exception("Impossible de se désinscrire : la formation a déjà commencé ou est passée.");
                }

                // Contrainte 2 : Bloquer si le statut de l'inscription est 'Terminée'
                $stmtI = $db->prepare("SELECT statut FROM inscription WHERE id_formation = ? AND id_utilisateur = ?");
                $stmtI->execute([$id_formation, $id_user]);

                $statut_actuel = $stmtI->fetchColumn();
                // On ne bloque la désinscription que si la formation a COMMENCÉ ET qu'elle est en cours/terminée
                if (strtotime($date_f) <= strtotime(date('Y-m-d')) && ($statut_actuel === 'En cours' || $statut_actuel === 'Terminée')) {
                    throw new Exception("Impossible de se désinscrire d'une formation qui a déjà commencé ou qui est terminée.");
                }

                // Suppression de l'inscription
                $delete = $db->prepare("DELETE FROM inscription WHERE id_formation = ? AND id_utilisateur = ?");
                $delete->execute([$id_formation, $id_user]);

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
                $update = $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_inscri = ?");
                $update->execute([(int) $_GET['id_inscription']]);
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
                $update = $db->prepare("UPDATE inscription SET statut = ? WHERE id_inscri = ?");
                $update->execute([$_POST['statut'], (int) $_POST['id_inscription']]);
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
                $id_f = (int) ($data['id_formation'] ?? 0);
                $id_u = (int) ($data['id_utilisateur'] ?? SessionManager::getUserId());
                if (!$id_f || !$id_u)
                    return ['success' => false, 'message' => 'Données manquantes.'];
                try {
                    $this->inscrire($id_f, $id_u);
                    return ['success' => true, 'message' => 'Inscription réussie !'];
                } catch (Exception $e) {
                    return ['success' => false, 'message' => $e->getMessage()];
                }

            case 'desinscrire':
                $id_f = (int) ($data['id_formation'] ?? 0);
                $id_u = (int) ($data['id_utilisateur'] ?? SessionManager::getUserId() ?? 0);
                if (!$id_f || !$id_u)
                    return ['success' => false, 'message' => 'Données manquantes.'];
                try {
                    // Logique de désinscription sécurisée
                    $db = config::getConnexion();
                    $stmtF = $db->prepare("SELECT date_formation FROM formation WHERE id_formation = ?");
                    $stmtF->execute([$id_f]);
                    $date_f = $stmtF->fetchColumn();
                    if ($date_f && strtotime($date_f) <= strtotime(date('Y-m-d'))) {
                        return ['success' => false, 'message' => "La formation a déjà commencé ou est passée."];
                    }

                    $stmtI = $db->prepare("DELETE FROM inscription WHERE id_formation = ? AND id_utilisateur = ?");
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
