<?php
/**
 * ============================================================
 * TuteurController — Gestion des Tuteurs + Planning (admin)
 * ============================================================
 * CRUD tuteurs + gestion du planning des créneaux horaires.
 *
 * Table auto-créée : planning_tuteur
 *   id, id_tuteur, titre, debut (DATETIME), fin (DATETIME),
 *   couleur (hex), recurrent (TINYINT), created_at
 */
require_once __DIR__ . '/../config.php';

// ── Auto-création de la table planning_tuteur si elle n'existe pas ──
(function () {
    try {
        $db = config::getConnexion();
        $db->exec("
            CREATE TABLE IF NOT EXISTS planning_tuteur (
                id          INT AUTO_INCREMENT PRIMARY KEY,
                id_tuteur   INT          NOT NULL,
                titre       VARCHAR(200) NOT NULL DEFAULT 'Disponible',
                debut       DATETIME     NOT NULL,
                fin         DATETIME     NOT NULL,
                couleur     VARCHAR(10)  NOT NULL DEFAULT '#6366f1',
                recurrent   TINYINT(1)   NOT NULL DEFAULT 0,
                created_at  TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_tuteur (id_tuteur)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
    } catch (\Exception $e) {
        // Silencieux en prod — la table existe peut-être déjà
    }
})();

class TuteurController
{
    // ----------------------------------------------------------
    // LISTER tous les tuteurs avec leurs statistiques
    // ----------------------------------------------------------

    /**
     * Retourne tous les utilisateurs ayant le rôle 'Tuteur',
     * enrichis du nombre de formations et d'inscrits totaux.
     *
     * @return array
     */
    public function listerTuteurs(): array
    {
        $db = config::getConnexion();

        $sql = "
            SELECT
                u.id,
                u.nom,
                u.email,
                u.role,
                '' AS specialite,
                '' AS bio,
                '' AS avatar,
                COUNT(DISTINCT f.id_formation)        AS nb_formations,
                COUNT(DISTINCT i.id_inscri)           AS nb_etudiants
            FROM utilisateur u
            LEFT JOIN Formation f  ON f.id_tuteur = u.id
            LEFT JOIN inscription i ON i.id_formation = f.id_formation
            WHERE LOWER(u.role) LIKE '%tuteur%'
            GROUP BY u.id
            ORDER BY u.nom ASC
        ";

        try {
            $stmt = $db->query($sql);
            return $stmt->fetchAll();
        } catch (\Exception $e) {
            // Fallback : table User (casse alternative)
            try {
                $sqlFb = str_replace(
                    ['FROM utilisateur', 'LEFT JOIN inscription'],
                    ['FROM User', 'LEFT JOIN Inscription'],
                    $sql
                );
                $stmt = $db->query($sqlFb);
                return $stmt->fetchAll();
            } catch (\Exception $e2) {
                return [];
            }
        }
    }

    // ----------------------------------------------------------
    // CRÉER un nouveau tuteur
    // ----------------------------------------------------------

    /**
     * Valide et insère un nouveau tuteur dans la table utilisateur.
     * Si l'email existe déjà, on met juste son rôle à 'Tuteur'.
     *
     * @param array $data  ['nom', 'email', 'specialite', 'bio']
     * @return array  ['success' => bool, 'message' => string, 'id' => int|null]
     */
    public function creerTuteur(array $data): array
    {
        // Validation côté serveur
        $nom = trim($data['nom'] ?? '');
        $email = trim($data['email'] ?? '');

        if (empty($nom) || strlen($nom) < 2) {
            return ['success' => false, 'message' => 'Le nom doit contenir au moins 2 caractères.'];
        }
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Adresse email invalide.'];
        }

        $specialite = trim($data['specialite'] ?? '');
        $bio = trim($data['bio'] ?? '');

        $db = config::getConnexion();

        // Vérifier si l'email existe déjà
        try {
            $checkSql = "SELECT id, role FROM utilisateur WHERE email = :email LIMIT 1";
            $check = $db->prepare($checkSql);
            $check->execute(['email' => $email]);
            $existing = $check->fetch();
        } catch (\Exception $e) {
            try {
                $check = $db->prepare("SELECT id, role FROM User WHERE email = :email LIMIT 1");
                $check->execute(['email' => $email]);
                $existing = $check->fetch();
            } catch (\Exception $e2) {
                $existing = null;
            }
        }

        if ($existing) {
            if ($existing['role'] === 'Tuteur') {
                return ['success' => false, 'message' => 'Cet email est déjà enregistré comme tuteur.'];
            }
            // Upgrade du rôle → Tuteur
            try {
                $upd = $db->prepare("UPDATE utilisateur SET role = 'Tuteur' WHERE id = :id");
                $upd->execute(['id' => $existing['id']]);
                return ['success' => true, 'message' => 'Utilisateur promu Tuteur avec succès.', 'id' => $existing['id']];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()];
            }
        }

        // Insertion d'un nouveau tuteur
        // Mot de passe temporaire haché (l'admin pourra envoyer un lien de reset)
        $tempPassword = password_hash('Aptus@' . rand(1000, 9999), PASSWORD_DEFAULT);

        $insertSql = "
            INSERT INTO utilisateur (nom, email, mot_de_passe, role, specialite, bio)
            VALUES (:nom, :email, :mdp, 'Tuteur', :specialite, :bio)
        ";

        try {
            $stmt = $db->prepare($insertSql);
            $stmt->execute([
                'nom' => $nom,
                'email' => $email,
                'mdp' => $tempPassword,
                'specialite' => $specialite,
                'bio' => $bio,
            ]);
            $newId = $db->lastInsertId();
            return ['success' => true, 'message' => "Tuteur {$nom} créé avec succès !", 'id' => $newId];
        } catch (\Exception $e) {
            // Fallback : colonnes sans specialite/bio si elles n'existent pas
            try {
                $stmt = $db->prepare("
                    INSERT INTO utilisateur (nom, email, mot_de_passe, role)
                    VALUES (:nom, :email, :mdp, 'Tuteur')
                ");
                $stmt->execute(['nom' => $nom, 'email' => $email, 'mdp' => $tempPassword]);
                return ['success' => true, 'message' => "Tuteur {$nom} créé avec succès !", 'id' => $db->lastInsertId()];
            } catch (\Exception $e2) {
                return ['success' => false, 'message' => 'Erreur SQL : ' . $e2->getMessage()];
            }
        }
    }

    // ----------------------------------------------------------
    // SUPPRIMER un tuteur
    // ----------------------------------------------------------

    /**
     * Supprime un tuteur uniquement s'il n'a aucune formation active.
     * Sinon, on repose juste son rôle à 'Candidat' (soft delete).
     *
     * @param int $id  ID du tuteur à supprimer.
     * @return array
     */
    public function supprimerTuteur(int $id): array
    {
        $db = config::getConnexion();

        // Vérifier s'il a des formations actives
        try {
            $check = $db->prepare("
                SELECT COUNT(*) FROM Formation
                WHERE id_tuteur = :id AND (statut IS NULL OR statut != 'annulée')
            ");
            $check->execute(['id' => $id]);
            $nbFormations = $check->fetchColumn();
        } catch (\Exception $e) {
            $nbFormations = 0;
        }

        if ($nbFormations > 0) {
            // On rétrograde le rôle plutôt que de supprimer
            try {
                $stmt = $db->prepare("UPDATE utilisateur SET role = 'Candidat' WHERE id = :id");
                $stmt->execute(['id' => $id]);
                return [
                    'success' => true,
                    'message' => "Ce tuteur a {$nbFormations} formations — son rôle a été rétrogradé à Candidat (données préservées)."
                ];
            } catch (\Exception $e) {
                return ['success' => false, 'message' => 'Erreur lors de la rétrogradation : ' . $e->getMessage()];
            }
        }

        // Suppression complète si aucune formation
        try {
            $stmt = $db->prepare("DELETE FROM utilisateur WHERE id = :id AND role = 'Tuteur'");
            $stmt->execute(['id' => $id]);
            if ($stmt->rowCount() === 0) {
                return ['success' => false, 'message' => 'Tuteur introuvable ou rôle incorrect.'];
            }
            return ['success' => true, 'message' => 'Tuteur supprimé avec succès.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()];
        }
    }

    // ----------------------------------------------------------
    // PLANNING — Récupérer tous les créneaux (format FullCalendar)
    // ----------------------------------------------------------

    /**
     * Retourne tous les créneaux de planning de TOUS les tuteurs
     * (ou d'un tuteur spécifique si $id_tuteur > 0) au format
     * FullCalendar eventSource JSON.
     *
     * Chaque événement inclut :
     *   id, title, start, end, backgroundColor, extendedProps.tuteur_nom
     *
     * @param int $id_tuteur  0 = tous les tuteurs
     * @return array
     */
    public function getPlanning(int $id_tuteur = 0): array
    {
        $db = config::getConnexion();

        $sql = "
            SELECT
                p.id,
                p.id_tuteur,
                p.titre,
                p.debut,
                p.fin,
                p.couleur,
                p.recurrent,
                COALESCE(u.nom, CONCAT('Tuteur #', p.id_tuteur)) AS tuteur_nom
            FROM planning_tuteur p
            LEFT JOIN utilisateur u ON u.id = p.id_tuteur
        ";

        $params = [];
        if ($id_tuteur > 0) {
            $sql .= " WHERE p.id_tuteur = :id_tuteur";
            $params['id_tuteur'] = $id_tuteur;
        }

        $sql .= " ORDER BY p.debut ASC";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
        } catch (\Exception $e) {
            // Fallback : table User
            try {
                $sqlFb = str_replace('LEFT JOIN utilisateur', 'LEFT JOIN User', $sql);
                $stmt = $db->prepare($sqlFb);
                $stmt->execute($params);
                $rows = $stmt->fetchAll();
            } catch (\Exception $e2) {
                return [];
            }
        }

        // Conversion en format FullCalendar
        $events = [];
        foreach ($rows as $r) {
            $events[] = [
                'id' => $r['id'],
                'title' => $r['tuteur_nom'] . ' — ' . $r['titre'],
                'start' => $r['debut'],
                'end' => $r['fin'],
                'backgroundColor' => $r['couleur'],
                'borderColor' => $r['couleur'],
                'extendedProps' => [
                    'id_tuteur' => $r['id_tuteur'],
                    'tuteur_nom' => $r['tuteur_nom'],
                    'titre' => $r['titre'],
                    'recurrent' => (bool) $r['recurrent'],
                ]
            ];
        }

        return $events;
    }

    // ----------------------------------------------------------
    // PLANNING — Ajouter un créneau
    // ----------------------------------------------------------

    /**
     * Insère un nouveau créneau horaire pour un tuteur.
     * Validations : id_tuteur requis, debut < fin, dates valides.
     *
     * @param array $data ['id_tuteur', 'titre', 'debut', 'fin', 'couleur', 'recurrent']
     * @return array { success, message, id }
     */
    public function addCreneau(array $data): array
    {
        $id_tuteur = (int) ($data['id_tuteur'] ?? 0);
        $titre = trim($data['titre'] ?? 'Disponible');
        $debut = trim($data['debut'] ?? '');
        $fin = trim($data['fin'] ?? '');
        $couleur = preg_match('/^#[0-9a-fA-F]{6}$/', $data['couleur'] ?? '') ? $data['couleur'] : '#6366f1';
        $recurrent = isset($data['recurrent']) ? 1 : 0;

        // Validations
        if ($id_tuteur <= 0) {
            return ['success' => false, 'message' => 'Sélectionnez un tuteur.'];
        }
        if (empty($debut) || empty($fin)) {
            return ['success' => false, 'message' => 'Les dates de début et fin sont requises.'];
        }
        if (strtotime($debut) >= strtotime($fin)) {
            return ['success' => false, 'message' => 'La date de fin doit être après la date de début.'];
        }

        $db = config::getConnexion();

        try {
            $stmt = $db->prepare("
                INSERT INTO planning_tuteur (id_tuteur, titre, debut, fin, couleur, recurrent)
                VALUES (:id_tuteur, :titre, :debut, :fin, :couleur, :recurrent)
            ");
            $stmt->execute([
                'id_tuteur' => $id_tuteur,
                'titre' => $titre,
                'debut' => $debut,
                'fin' => $fin,
                'couleur' => $couleur,
                'recurrent' => $recurrent,
            ]);
            return ['success' => true, 'message' => 'Créneau ajouté.', 'id' => $db->lastInsertId()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur SQL : ' . $e->getMessage()];
        }
    }

    // ----------------------------------------------------------
    // PLANNING — Modifier un créneau (resize/drag FullCalendar)
    // ----------------------------------------------------------

    /**
     * Met à jour debut/fin d'un créneau après drag & drop ou resize.
     *
     * @param int    $id     ID du créneau
     * @param string $debut  Nouveau datetime de début (ISO 8601)
     * @param string $fin    Nouveau datetime de fin   (ISO 8601)
     * @return array
     */
    public function updateCreneau(int $id, string $debut, string $fin): array
    {
        if ($id <= 0 || empty($debut) || empty($fin)) {
            return ['success' => false, 'message' => 'Paramètres invalides.'];
        }
        if (strtotime($debut) >= strtotime($fin)) {
            return ['success' => false, 'message' => 'Fin doit être après début.'];
        }

        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("
                UPDATE planning_tuteur SET debut = :debut, fin = :fin WHERE id = :id
            ");
            $stmt->execute(['id' => $id, 'debut' => $debut, 'fin' => $fin]);
            return ['success' => true, 'message' => 'Créneau mis à jour.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ----------------------------------------------------------
    // PLANNING — Supprimer un créneau
    // ----------------------------------------------------------

    /**
     * @param int $id  ID du créneau planning_tuteur
     * @return array
     */
    public function deleteCreneau(int $id): array
    {
        if ($id <= 0) {
            return ['success' => false, 'message' => 'ID invalide.'];
        }
        $db = config::getConnexion();
        try {
            $stmt = $db->prepare("DELETE FROM planning_tuteur WHERE id = :id");
            $stmt->execute(['id' => $id]);
            return ['success' => true, 'message' => 'Créneau supprimé.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => 'Erreur : ' . $e->getMessage()];
        }
    }

    // ----------------------------------------------------------
    // POINT D'ENTRÉE AJAX — étendu planning + tuteurs
    // ----------------------------------------------------------

    /**
     * Routeur AJAX : dispatche toutes les actions (tuteurs + planning).
     * Réponses JSON : { success: bool, message: string, [data: ...] }
     */
    public function handleAjax(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'POST requis.']);
            return;
        }

        $action = $_POST['action'] ?? '';

        switch ($action) {

            // ── Tuteurs ──────────────────────────────────────
            case 'add_tuteur':
                echo json_encode($this->creerTuteur($_POST));
                break;

            case 'delete_tuteur':
                $id = (int) ($_POST['id'] ?? 0);
                echo json_encode($this->supprimerTuteur($id));
                break;

            // ── Planning ─────────────────────────────────────
            case 'get_planning':
                $idT = (int) ($_POST['id_tuteur'] ?? 0);
                echo json_encode(['success' => true, 'events' => $this->getPlanning($idT)]);
                break;

            case 'add_creneau':
                echo json_encode($this->addCreneau($_POST));
                break;

            case 'update_creneau':
                $id = (int) ($_POST['id'] ?? 0);
                $debut = $_POST['debut'] ?? '';
                $fin = $_POST['fin'] ?? '';
                echo json_encode($this->updateCreneau($id, $debut, $fin));
                break;

            case 'delete_creneau':
                $id = (int) ($_POST['id'] ?? 0);
                echo json_encode($this->deleteCreneau($id));
                break;

            default:
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => "Action inconnue : '{$action}'"]);
        }
    }
}
