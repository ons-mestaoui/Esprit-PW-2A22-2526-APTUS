<?php
include_once __DIR__ . '/../config.php';
include_once __DIR__ . '/../model/Utilisateur.php';

/**
 * Contrôleur UtilisateurC : Gère la logique métier des utilisateurs.
 * Fait l'interface entre le Modèle (Utilisateur) et la Vue.
 */
class UtilisateurC {

    /**
     * Récupère la liste de tous les utilisateurs depuis la base de données.
     * Utilise query() car il n'y a pas de paramètres externes.
     */
    public function listerUtilisateurs() {
        $db = config::getConnexion();
        try {
            $liste = $db->query("SELECT * FROM utilisateur");
            return $liste->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function emailExists($email, $excludeId = 0) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT COUNT(*) FROM utilisateur WHERE email = :email AND id_utilisateur != :id");
            $query->execute([
                'email' => $email,
                'id' => $excludeId
            ]);
            return $query->fetchColumn() > 0;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Ajoute un nouvel utilisateur.
     * @param Utilisateur $utilisateur Objet de type Utilisateur (POO)
     * Utilise des requêtes préparées pour prévenir les injections SQL.
     */
    public function addUtilisateur($utilisateur) {
        $db = config::getConnexion();
        try {
            // Utilisation des getters de l'objet pour récupérer les données
            $query = $db->prepare("INSERT INTO utilisateur (nom, prenom, email, motDePasse, role, telephone) 
                                   VALUES (:nom, :prenom, :email, :motDePasse, :role, :telephone)");
            $query->execute([
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'motDePasse' => password_hash($utilisateur->getMotDePasse(), PASSWORD_DEFAULT),
                'role' => $utilisateur->getRole(),
                'telephone' => $utilisateur->getTelephone()
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteUtilisateur($id) {
        $db = config::getConnexion();
        try {
            // Supprimer d'abord les entrées dépendantes pour éviter les erreurs de contrainte de clé étrangère
            $db->prepare("DELETE FROM candidat WHERE id_candidat = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM entreprise WHERE id_entreprise = :id")->execute(['id' => $id]);
            
            // Puis supprimer l'utilisateur lui-même
            $query = $db->prepare("DELETE FROM utilisateur WHERE id_utilisateur = :id");
            $query->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Met à jour les informations d'un utilisateur.
     * Gère la modification optionnelle du mot de passe.
     */
    public function updateUtilisateur($utilisateur, $id) {
        $db = config::getConnexion();
        try {
            // Si le mot de passe est vide, on ne le modifie pas dans la base
            if (empty($utilisateur->getMotDePasse())) {
                $query = $db->prepare("UPDATE utilisateur 
                                       SET nom = :nom, prenom = :prenom, email = :email, 
                                           role = :role, telephone = :telephone 
                                       WHERE id_utilisateur = :id");
                $query->execute([
                    'id' => $id,
                    'nom' => $utilisateur->getNom(),
                    'prenom' => $utilisateur->getPrenom(),
                    'email' => $utilisateur->getEmail(),
                    'role' => $utilisateur->getRole(),
                    'telephone' => $utilisateur->getTelephone()
                ]);
            } else {
                $query = $db->prepare("UPDATE utilisateur 
                                       SET nom = :nom, prenom = :prenom, email = :email, 
                                           motDePasse = :motDePasse, role = :role, telephone = :telephone 
                                       WHERE id_utilisateur = :id");
                $query->execute([
                    'id' => $id,
                    'nom' => $utilisateur->getNom(),
                    'prenom' => $utilisateur->getPrenom(),
                    'email' => $utilisateur->getEmail(),
                    'motDePasse' => password_hash($utilisateur->getMotDePasse(), PASSWORD_DEFAULT),
                    'role' => $utilisateur->getRole(),
                    'telephone' => $utilisateur->getTelephone()
                ]);
            }
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function getUtilisateurById($id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT * FROM utilisateur WHERE id_utilisateur = :id");
            $query->execute([
                'id' => $id
            ]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }
}
?>
