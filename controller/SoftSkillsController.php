<?php
/**
 * ============================================================
 * SoftSkillsController — Concept 3 : Évaluateur Soft-Skills
 * ============================================================
 * Objectif : Recevoir le score d'analyse facial (face-api.js) via AJAX,
 * valider un seuil minimum, puis mettre à jour la progression de
 * l'étudiant à 100% pour valider le certificat.
 *
 * Méthodes publiques :
 *   - validerCertificat($id_formation, $id_user, $score) → bool
 *   - handleAjax() → point d'entrée HTTP pour les requêtes AJAX POST.
 */
require_once __DIR__ . '/../config.php';

class SoftSkillsController
{
    /**
     * Seuil minimum de score (sur 100) pour valider le certificat.
     * En dessous de ce seuil, le certificat n'est PAS accordé.
     */
    private const SEUIL_VALIDATION = 55;

    // ----------------------------------------------------------
    // MÉTHODE PRINCIPALE : Valider le certificat via le score
    // ----------------------------------------------------------

    /**
     * Si le score de soft-skills dépasse le seuil, on met la progression
     * à 100% dans la table inscription (et statut = 'Terminée').
     *
     * @param int   $id_formation  Formation concernée.
     * @param int   $id_user       Étudiant concerné.
     * @param float $score         Score obtenu (0-100) depuis face-api.js.
     * @return bool  true si le certificat est validé, false sinon.
     * @throws \Exception Si la mise à jour BDD échoue.
     */
    public function validerCertificat(int $id_formation, int $id_user, float $score): bool
    {
        // Contrôle du seuil côté serveur (double validation, le JS fait aussi la vérif)
        if ($score < self::SEUIL_VALIDATION) {
            return false;
        }

        $db = config::getConnexion();

        // Mise à jour de la progression de l'étudiant avec requête préparée PDO
        $sql = "
            UPDATE inscription
            SET progression = 100,
                statut      = 'Terminée'
            WHERE id_formation = :id_formation
              AND id_user      = :id_user
        ";

        try {
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'id_formation' => $id_formation,
                'id_user'      => $id_user
            ]);

            // Si aucune ligne affectée (casse alternative de la table)
            if ($stmt->rowCount() === 0) {
                throw new \Exception('Inscription non trouvée.');
            }
            return true;

        } catch (\Exception $e) {
            // Fallback avec table 'Inscription' (majuscule)
            try {
                $sqlFallback = str_replace('UPDATE inscription', 'UPDATE Inscription', $sql);
                $stmt = $db->prepare($sqlFallback);
                $stmt->execute([
                    'id_formation' => $id_formation,
                    'id_user'      => $id_user
                ]);
                return true;
            } catch (\Exception $e2) {
                throw new \Exception('Erreur BDD lors de la validation : ' . $e2->getMessage());
            }
        }
    }

    // ----------------------------------------------------------
    // POINT D'ENTRÉE AJAX
    // ----------------------------------------------------------

    /**
     * Handler AJAX : reçoit POST { id_formation, score } et répond en JSON.
     * À inclure via une route comme ?action=softskills_validate
     *
     * Réponse JSON succès  : { "success": true,  "message": "Certificat validé !" }
     * Réponse JSON échec   : { "success": false,  "message": "Score insuffisant." }
     */
    public function handleAjax(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            return;
        }

        // Lecture et validation des paramètres POST
        $id_formation = isset($_POST['id_formation']) ? (int)$_POST['id_formation'] : 0;
        $score        = isset($_POST['score'])        ? (float)$_POST['score']       : 0;
        // On utilise la session utilisateur (ou 10 pour la démo)
        $id_user      = isset($_SESSION['user_id'])   ? (int)$_SESSION['user_id']    : 10;

        header('Content-Type: application/json');

        if ($id_formation <= 0) {
            echo json_encode(['success' => false, 'message' => 'Formation invalide.']);
            return;
        }

        if ($score < 0 || $score > 100) {
            echo json_encode(['success' => false, 'message' => 'Score invalide.']);
            return;
        }

        try {
            $valide = $this->validerCertificat($id_formation, $id_user, $score);

            if ($valide) {
                echo json_encode([
                    'success' => true,
                    'message' => '🎓 Excellent ! Votre certificat a été validé grâce à votre score Soft-Skills de ' . round($score) . '/100 !'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => '😔 Score insuffisant (' . round($score) . '/100). Le seuil requis est de ' . self::SEUIL_VALIDATION . '/100. Réessayez !'
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
        }
    }
}
