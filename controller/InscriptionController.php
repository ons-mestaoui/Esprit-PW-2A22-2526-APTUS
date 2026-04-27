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
        // C'est le socle qui ne doit pas échouer
        $stmt = $db->prepare("
            SELECT i.progression, f.titre, f.id_tuteur
            FROM inscription i
            JOIN Formation f ON i.id_formation = f.id_formation
            WHERE i.id_user = :uid AND i.id_formation = :fid
        ");
        $stmt->execute(['uid' => $id_user, 'fid' => $id_formation]);
        $res = $stmt->fetch();
        
        if (!$res) {
            // Deuxième essai avec table Inscription majuscule (casse alternative)
            $stmt = $db->prepare("
                SELECT i.progression, f.titre, f.id_tuteur
                FROM Inscription i
                JOIN Formation f ON i.id_formation = f.id_formation
                WHERE i.id_user = :uid AND i.id_formation = :fid
            ");
            $stmt->execute(['uid' => $id_user, 'fid' => $id_formation]);
            $res = $stmt->fetch();
        }

        if (!$res) return false;

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
                // Correction : le nom de la colonne est id_badge
                $stmtB = $db->prepare("SELECT id_badge FROM badge WHERE nom = ? LIMIT 1");
                $stmtB->execute([$niveau]);
                $id_badge = $stmtB->fetchColumn();

                if ($id_badge) {
                    // On insère dans user_badges (on ajoute id_formation si présent dans la table)
                    $stmtAssign = $db->prepare("INSERT IGNORE INTO user_badges (id_user, id_badge, id_formation, date_obtention) VALUES (?, ?, ?, ?)");
                    $stmtAssign->execute([$id_user, $id_badge, $id_formation, date('Y-m-d')]);
                }
            }
            // ------------------------------------------

        } catch (Exception $e) {
            // Fallback pour les noms de tables en majuscules/minuscules selon l'OS
            $update = $db->prepare("UPDATE Inscription SET statut = 'Terminée', progression = 100 WHERE id_formation = ? AND id_user = ?");
            $update->execute([$id_formation, $id_user]);
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
}
