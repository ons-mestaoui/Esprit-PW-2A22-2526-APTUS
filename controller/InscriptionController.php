<?php
// InscriptionController : gère les inscriptions/desinscriptions des candidats
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Inscription.php';

class InscriptionController
{
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
        } catch (Exception $e) {
            $update = $db->prepare("UPDATE Inscription SET statut = 'Terminée', progression = 100 WHERE id_formation = ? AND id_user = ?");
            $update->execute([$id_formation, $id_user]);
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
                if ($statut_actuel === 'En cours' || $statut_actuel === 'Terminée') {
                    throw new Exception("Impossible de se désinscrire d'une formation en cours ou terminée.");
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
