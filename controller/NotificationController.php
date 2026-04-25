<?php
require_once __DIR__ . '/../config.php';

class NotificationController {

    /**
     * Crée une notification avec gestion de priorité via préfixe.
     * Priorités supportées : URGENT_, SILENT_, (par défaut: NORMAL)
     */
    public static function creerNotification(
        $user_id, $type, $message, $url = '', $icon = 'bell', $priority = 'NORMAL'
    ): bool {
        try {
            $db = config::getConnexion();
            
            // On préfixe le type si c'est urgent ou silencieux
            $prefix = ($priority === 'URGENT') ? 'URGENT_' : (($priority === 'SILENT') ? 'SILENT_' : '');
            $finalType = $prefix . $type;

            $sql = "INSERT INTO notifications 
                    (user_id, type, message, url_action, icon, is_read, created_at)
                    VALUES (:uid, :type, :msg, :url, :icon, 0, NOW())";
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'uid'  => (int)$user_id,
                'type' => $finalType,
                'msg'  => $message,
                'url'  => $url,
                'icon' => $icon
            ]);
        } catch (Exception $e) {
            error_log("[Notification] Erreur : " . $e->getMessage());
            return false;
        }
    }

    /**
     * Récupère les notifications non lues avec icône et timestamp relatif.
     */
    public function getUnreadNotifications($user_id): array {
        return $this->getRecentUnread($user_id, 20);
    }

    public function getRecentUnread($user_id, $limit = 10): array {
        $db = config::getConnexion();
        $sql = "SELECT id, type, message, url_action, icon, created_at,
                       TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS age_minutes
                FROM notifications
                WHERE user_id = :uid AND is_read = 0
                ORDER BY created_at DESC
                LIMIT :lim";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':uid', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Récupère TOUTES les notifications (lues + non lues) pour un historique.
     */
    public function getAll($user_id, $limit = 30): array {
        $db = config::getConnexion();
        $sql = "SELECT id, type, message, url_action, icon, is_read, created_at,
                       TIMESTAMPDIFF(MINUTE, created_at, NOW()) AS age_minutes
                FROM notifications
                WHERE user_id = :uid
                ORDER BY created_at DESC
                LIMIT :lim";
        $stmt = $db->prepare($sql);
        $stmt->bindValue(':uid', (int)$user_id, PDO::PARAM_INT);
        $stmt->bindValue(':lim', (int)$limit,   PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Marque toutes les notifs d'un user comme lues. */
    public function markAsRead($user_id): bool {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid AND is_read = 0");
        return $stmt->execute(['uid' => (int)$user_id]);
    }

    /** Supprime toutes les notifications d'un utilisateur. */
    public function deleteAll($user_id): bool {
        $db = config::getConnexion();
        $stmt = $db->prepare("DELETE FROM notifications WHERE user_id = :uid");
        return $stmt->execute(['uid' => (int)$user_id]);
    }

    /** Marque une seule notif comme lue. */
    public function markOneAsRead($notif_id): bool {
        $db = config::getConnexion();
        $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE id = :id");
        return $stmt->execute(['id' => (int)$notif_id]);
    }

    /** Compte les notifs non lues. */
    public function countUnread($user_id): int {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute(['uid' => (int)$user_id]);
        return (int)$stmt->fetchColumn();
    }

    /** Helper : retourne un label humain du temps écoulé */
    public static function timeAgo(int $minutes): string {
        if ($minutes < 1)     return "à l'instant";
        if ($minutes < 60)    return "il y a {$minutes} min";
        $h = floor($minutes / 60);
        if ($h < 24)          return "il y a {$h}h";
        $d = floor($h / 24);
        return "il y a {$d}j";
    }
}
