<?php
require_once __DIR__ . '/../config.php';

class UserC {
    public function getUserById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM utilisateur WHERE id = :id");
            $query->execute(['id' => $id]);
            $user = $query->fetch();
            
            // Si pas trouvé dans utilisateur, essayer User
            if (!$user) {
                $query = $db->prepare("SELECT * FROM User WHERE id = :id");
                $query->execute(['id' => $id]);
                $user = $query->fetch();
            }
            
            return $user;
        } catch (Exception $e) {
            return null;
        }
    }
}
?>
