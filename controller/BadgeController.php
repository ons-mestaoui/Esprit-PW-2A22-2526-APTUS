<?php
require_once __DIR__ . '/../config.php';

class BadgeController {

    /**
     * Attribue un badge à un utilisateur en fonction du niveau d'une formation terminée.
     * Niveaux supportés : Débutant, Intermédiaire, Expert
     */
    public function attribuerBadgeNiveau(int $id_user, string $niveau, ?int $id_formation = null): bool {
        try {
            $db = config::getConnexion();
            
            // 1. Trouver l'id_badge correspondant au nom du niveau
            $stmt = $db->prepare("SELECT id_badge FROM badge WHERE nom = ? LIMIT 1");
            $stmt->execute([$niveau]);
            $id_badge = $stmt->fetchColumn();

            if (!$id_badge) {
                // Optionnel : Créer le badge s'il n'existe pas (auto-réparation)
                $ins = $db->prepare("INSERT INTO badge (nom, description) VALUES (?, ?)");
                $ins->execute([$niveau, "Badge décerné pour la réussite d'une formation de niveau $niveau."]);
                $id_badge = $db->lastInsertId();
            }

            // 2. Insérer dans user_badges (si pas déjà possédé pour cette formation ou globalement)
            // Note: On peut posséder le même badge via plusieurs formations
            $sqlCheck = "SELECT COUNT(*) FROM user_badges WHERE id_user = ? AND id_badge = ?";
            if ($id_formation) $sqlCheck .= " AND id_formation = ?";
            
            $stmtCheck = $db->prepare($sqlCheck);
            $checkParams = [$id_user, $id_badge];
            if ($id_formation) $checkParams[] = $id_formation;
            
            $stmtCheck->execute($checkParams);
            if ($stmtCheck->fetchColumn() == 0) {
                $sqlInsert = "INSERT INTO user_badges (id_user, id_badge, id_formation, date_obtention) VALUES (?, ?, ?, NOW())";
                $stmtInsert = $db->prepare($sqlInsert);
                return $stmtInsert->execute([$id_user, $id_badge, $id_formation]);
            }

            return true;
        } catch (Exception $e) {
            error_log("[BadgeController] Erreur : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère tous les badges existants.
     */
    public function listerBadges(): array {
        $db = config::getConnexion();
        return $db->query("SELECT * FROM badge ORDER BY nom ASC")->fetchAll();
    }

    /**
     * Récupère un badge spécifique par son ID.
     */
    public function getBadgeById(int $id_badge): ?array {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT * FROM badge WHERE id_badge = ?");
        $stmt->execute([$id_badge]);
        return $stmt->fetch() ?: null;
    }
}
