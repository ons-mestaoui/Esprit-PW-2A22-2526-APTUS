<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Inscription.php';

class InscriptionController
{
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

    public function desinscrire()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_formation'])) {
            $id_formation = (int)$_POST['id_formation'];
            $id_user = $_SESSION['user_id'] ?? 10; // Récupère l'ID via session ou 10 pour la démo

            try {
                Inscription::desinscrire($id_user, $id_formation);
                $_SESSION['flash_success'] = "Vous vous êtes désinscrit de la formation avec succès.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
            header("Location: formations_my.php");
            exit();
        }
    }

    public function annulerAdmin()
    {
        if (isset($_GET['id_inscription'])) {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
                header("Location: formations_admin.php");
                exit();
            }

            try {
                Inscription::annulerParAdmin((int)$_GET['id_inscription']);
                $_SESSION['flash_success'] = "L'inscription a été annulée.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = "Erreur lors de l'annulation de l'inscription.";
            }
            header("Location: formations_admin.php");
            exit();
        }
    }

    public function updateStatut()
    {
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_inscription']) && isset($_POST['statut'])) {
            if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
                $_SESSION['flash_error'] = "Action non autorisée. Rôle admin requis.";
                header("Location: formations_admin.php");
                exit();
            }

            try {
                Inscription::updateStatutAdmin((int)$_POST['id_inscription'], $_POST['statut']);
                $_SESSION['flash_success'] = "Le statut de l'inscription a été mis à jour.";
            } catch (Exception $e) {
                $_SESSION['flash_error'] = $e->getMessage();
            }
            header("Location: formations_admin.php");
            exit();
        }
    }
}
