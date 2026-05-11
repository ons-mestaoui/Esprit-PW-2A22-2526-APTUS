<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Admin.php';

class AdminC {
    public function getAdminById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM administrateur WHERE id_admin = :id");
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function addAdmin($admin) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("INSERT INTO administrateur (id_admin, niveau) VALUES (:id, :niveau)");
            $query->execute([
                'id' => $admin->getIdAdmin(),
                'niveau' => $admin->getNiveau()
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function updateAdmin($admin, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("UPDATE administrateur SET niveau = :niveau WHERE id_admin = :id");
            $query->execute([
                'niveau' => $admin->getNiveau(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteAdmin($id) {
        $db = config::getConnexion();
        try {
            $db->prepare("DELETE FROM administrateur WHERE id_admin = :id")->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
