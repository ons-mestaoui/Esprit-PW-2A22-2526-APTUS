<?php
require_once __DIR__ . '/../config.php';

class InscriptionController
{
    /**
     * Liste les formations d'un utilisateur
     */
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
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e2) {
                return [];
            }
        }
    }

    /**
     * Marque une formation comme terminée
     */
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
        } catch(Exception $e) {
            $update = $db->prepare("UPDATE Inscription SET statut = 'Terminée', progression = 100 WHERE id_formation = ? AND id_user = ?");
            $update->execute([$id_formation, $id_user]);
        }
        return true;
    }
}
