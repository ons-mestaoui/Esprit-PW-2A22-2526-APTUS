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
            // Generate a secure verification token for new accounts
            $token_verification = bin2hex(random_bytes(32));
            $est_verifie = 0; // By default, newly registered users are not verified
            
            // Utilisation des getters de l'objet pour récupérer les données
            $query = $db->prepare("INSERT INTO utilisateur (nom, prenom, email, motDePasse, role, telephone, est_verifie, token_verification) 
                                   VALUES (:nom, :prenom, :email, :motDePasse, :role, :telephone, :est_verifie, :token_verification)");
            $query->execute([
                'nom' => $utilisateur->getNom(),
                'prenom' => $utilisateur->getPrenom(),
                'email' => $utilisateur->getEmail(),
                'motDePasse' => password_hash($utilisateur->getMotDePasse(), PASSWORD_DEFAULT),
                'role' => $utilisateur->getRole(),
                'telephone' => $utilisateur->getTelephone(),
                'est_verifie' => $est_verifie,
                'token_verification' => $token_verification
            ]);
            
            // Return an array containing both the new ID and the verification token
            return [
                'id' => $db->lastInsertId(),
                'token' => $token_verification
            ];
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function deleteUtilisateur($id) {
        $db = config::getConnexion();
        try {
            // D'abord, gérer les dépendances profondes pour éviter les erreurs de clés étrangères
            // 1. Candidat
            $db->prepare("DELETE FROM candidatures WHERE id_candidat = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM cv WHERE id_candidat = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM inscription WHERE id_candidat = :id")->execute(['id' => $id]);
            
            // 2. Entreprise
            // Supprimer d'abord les candidatures liées aux offres de l'entreprise
            $db->prepare("DELETE FROM candidatures WHERE id_offre IN (SELECT id_offre FROM offreemploi WHERE id_entreprise = :id)")->execute(['id' => $id]);
            $db->prepare("DELETE FROM offreemploi WHERE id_entreprise = :id")->execute(['id' => $id]);

            // 3. Admin
            $db->prepare("DELETE FROM rapport_marche WHERE id_admin = :id")->execute(['id' => $id]);

            // 4. Formation (détacher le tuteur plutôt que de supprimer la formation)
            $db->prepare("UPDATE formation SET id_tuteur = NULL WHERE id_tuteur = :id")->execute(['id' => $id]);

            // Supprimer ensuite les entrées directement liées à l'utilisateur
            $db->prepare("DELETE FROM profil WHERE id_utilisateur = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM candidat WHERE id_candidat = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM entreprise WHERE id_entreprise = :id")->execute(['id' => $id]);
            $db->prepare("DELETE FROM administrateur WHERE id_admin = :id")->execute(['id' => $id]);
            
            // Puis supprimer l'utilisateur lui-même
            $query = $db->prepare("DELETE FROM utilisateur WHERE id_utilisateur = :id");
            $query->execute([
                'id' => $id
            ]);
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression : ' . $e->getMessage());
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

    /**
     * Générer un token de réinitialisation si l'email existe.
     */
    public function createPasswordResetToken($email) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE email = :email");
            $query->execute(['email' => $email]);
            $user = $query->fetch();

            if ($user) {
                $token = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $update = $db->prepare("UPDATE utilisateur SET reset_token = :token, token_expires = :expires WHERE email = :email");
                $update->execute([
                    'token' => $token,
                    'expires' => $expires,
                    'email' => $email
                ]);

                return $token;
            }
            return false;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Valider si un token est valide et non expiré.
     */
    public function validateResetToken($token) {
        $db = config::getConnexion();
        try {
            $now = date('Y-m-d H:i:s');
            $query = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE reset_token = :token AND token_expires > :now");
            $query->execute([
                'token' => $token,
                'now' => $now
            ]);
            $user = $query->fetch();
            return $user ? $user['id_utilisateur'] : false;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Mettre à jour le mot de passe et annuler le token.
     */
    public function resetPassword($id_utilisateur, $newPassword) {
        $db = config::getConnexion();
        try {
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $query = $db->prepare("UPDATE utilisateur SET motDePasse = :motDePasse, reset_token = NULL, token_expires = NULL WHERE id_utilisateur = :id");
            return $query->execute([
                'motDePasse' => $hashedPassword,
                'id' => $id_utilisateur
            ]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Valide un compte utilisateur à partir du token envoyé par email.
     */
    public function verifyAccount($token) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare("SELECT id_utilisateur FROM utilisateur WHERE token_verification = :token");
            $query->execute(['token' => $token]);
            $user = $query->fetch();

            if ($user) {
                $update = $db->prepare("UPDATE utilisateur SET est_verifie = 1, token_verification = NULL WHERE id_utilisateur = :id");
                return $update->execute(['id' => $user['id_utilisateur']]);
            }
            return false;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère les préférences utilisateur (JSON stocké dans la colonne `preferences`).
     * Retourne un tableau associatif avec les valeurs par défaut si vide.
     */
    public function getPreferences($id) {
        $db = config::getConnexion();
        $defaults = [
            'language' => 'fr',
            'timezone' => 'Africa/Tunis',
            'theme' => 'dark',
            'accent_color' => '#6366f1',
            'font_size' => 14,
            'privacy_public' => true,
            'privacy_email' => false,
            'privacy_phone' => false,
            'privacy_search' => true
        ];
        try {
            $query = $db->prepare("SELECT preferences FROM utilisateur WHERE id_utilisateur = :id");
            $query->execute(['id' => $id]);
            $row = $query->fetch();
            if ($row && !empty($row['preferences'])) {
                $prefs = json_decode($row['preferences'], true);
                if (is_array($prefs)) {
                    return array_merge($defaults, $prefs);
                }
            }
            return $defaults;
        } catch (Exception $e) {
            return $defaults;
        }
    }

    /**
     * Met à jour les préférences utilisateur.
     * Fusionne les nouvelles préférences avec les existantes.
     */
    public function updatePreferences($id, $newPrefs) {
        $db = config::getConnexion();
        try {
            $current = $this->getPreferences($id);
            $merged = array_merge($current, $newPrefs);
            $json = json_encode($merged, JSON_UNESCAPED_UNICODE);
            $query = $db->prepare("UPDATE utilisateur SET preferences = :prefs WHERE id_utilisateur = :id");
            return $query->execute(['prefs' => $json, 'id' => $id]);
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
