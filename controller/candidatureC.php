<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/scoreAIC.php';
require_once __DIR__ . '/mailC.php';

class candidatureC {

    // Ajouter une candidature avec Scoring IA
    public function addCandidature($candidature) {
        $db = config::getConnexion();
        
        // 1. Récupérer les détails complets de l'offre
        $sqlOffre = "SELECT titre, description, question FROM offreemploi WHERE id_offre = :id";
        $reqOffre = $db->prepare($sqlOffre);
        $reqOffre->execute(['id' => $candidature->getIdOffre()]);
        $offre = $reqOffre->fetch();
        
        $contexteOffre = "OFFRE : " . ($offre['titre'] ?? '') . ". DESCRIPTION : " . ($offre['description'] ?? '');
        $questionEntreprise = $offre['question'] ?? "Quelle est votre motivation ?";
        
        // 2. Calculer le score avec une structure claire
        $scoreAIC = new scoreAIC();
        $prompt = "--- CONTEXTE DE L'OFFRE ---\n$contexteOffre\n\n" .
                  "--- QUESTION DE L'ENTREPRISE ---\n$questionEntreprise\n\n" .
                  "--- RÉPONSE DU CANDIDAT ---\n" . $candidature->getReponsesQues() . "\n\n" .
                  "Analyse la réponse par rapport à la question et à l'offre pour donner un score.";
        $score = $scoreAIC->calculerScore($prompt);

        // 3. Déterminer le statut initial
        $statut = 'En attente';

        // 4. Insertion
        $sql = "INSERT INTO candidatures 
            (id_candidat, id_offre, nom, prenom, email, reponses_ques, cv__cand, date_candidature, note, statut) 
            VALUES (:id_candidat, :id_offre, :nom, :prenom, :email, :reponses_ques, :cv__cand, :date_candidature, :note, :statut)";
        
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'id_candidat' => $candidature->getIdCandidat(),
                'id_offre' => $candidature->getIdOffre(),
                'nom' => $candidature->getNom(),
                'prenom' => $candidature->getPrenom(),
                'email' => $candidature->getEmail(),
                'reponses_ques' => $candidature->getReponsesQues(),
                'cv__cand' => $candidature->getCvCand(),
                'date_candidature' => $candidature->getDateCandidature(),
                'note' => $score,
                'statut' => $statut
            ]);

            $lastId = $db->lastInsertId();

            // Pas d'auto-rejection pour le moment, le RH voit tout.

        } catch (Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

    // Afficher (récupérer) toutes les candidatures (avec titre de l'offre grâce à la clé étrangère)
    public function afficherCandidatures() {
        $sql = "SELECT c.*, o.titre as titre_offre, o.question as question_offre 
                FROM candidatures c 
                LEFT JOIN offreemploi o ON c.id_offre = o.id_offre";
        $db = config::getConnexion();
        try {
            $liste = $db->query($sql);
            return $liste;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
    // Filtrer les candidatures
    public function filtrerCandidatures($criteres = []) {
        $sql = "SELECT c.*, o.titre as titre_offre, o.question as question_offre 
                FROM candidatures c 
                LEFT JOIN offreemploi o ON c.id_offre = o.id_offre 
                WHERE 1=1";
        
        $params = [];
        if (!empty($criteres['status'])) {
            if ($criteres['status'] === 'shortliste' || $criteres['status'] === 'Accepté') {
                $sql .= " AND c.statut = 'Accepté'";
            } elseif ($criteres['status'] === 'refuse' || $criteres['status'] === 'Refusé') {
                $sql .= " AND c.statut = 'Refusé'";
            } elseif ($criteres['status'] === 'en_attente') {
                $sql .= " AND (c.statut = 'En attente' OR c.statut = 'en_attente')";
            }
        }

        if (!empty($criteres['offre_id'])) {
            $sql .= " AND c.id_offre = :offre_id";
            $params['offre_id'] = $criteres['offre_id'];
        }

        if (!empty($criteres['q'])) {
            $sql .= " AND (c.nom LIKE :q OR c.prenom LIKE :q)";
            $params['q'] = '%' . $criteres['q'] . '%';
        }

        $sql .= " ORDER BY c.date_candidature DESC";

        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute($params);
            return $req;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }



    // Mettre à jour le statut d'une candidature + créer une notification
    public function updateStatut($id_candidature, $nouveau_statut) {
        $db = config::getConnexion();
        try {
            // 1. Récupérer les infos de la candidature + l'entreprise (via table utilisateur)
            $sql = "SELECT c.*, o.titre as titre_offre, u.nom as nom_entreprise, u.email as email_entreprise 
                    FROM candidatures c 
                    LEFT JOIN offreemploi o ON c.id_offre = o.id_offre 
                    LEFT JOIN utilisateur u ON o.id_entreprise = u.id_utilisateur
                    WHERE c.id_candidature = :id";
            $req = $db->prepare($sql);
            $req->execute(['id' => $id_candidature]);
            $cand = $req->fetch();
            if (!$cand) return false;

            // SECURITÉ : Si le statut est déjà celui-là, on arrête tout (évite les doublons au refresh)
            if ($cand['statut'] === $nouveau_statut) {
                return true;
            }

            // 2. Mettre à jour le statut
            $sql2 = "UPDATE candidatures SET statut = :statut WHERE id_candidature = :id";
            $req2 = $db->prepare($sql2);
            $req2->execute(['statut' => $nouveau_statut, 'id' => $id_candidature]);

            // 3. Créer la notification
            $nom = $cand['prenom'] . ' ' . $cand['nom'];
            $poste = $cand['titre_offre'] ?? 'un poste';
            if ($nouveau_statut === 'Accepté') {
                $message = "Bonjour $nom, félicitations ! Votre candidature pour le poste \"$poste\" a été retenue.";
            } else {
                $message = "Bonjour $nom, nous vous informons que votre candidature pour le poste \"$poste\" n'a malheureusement pas été retenue.";
            }

            $this->addNotification($cand['id_candidat'], $id_candidature, $message);

            // 4. SI REFUS : Envoyer le mail via Brevo (Dynamique)
            if ($nouveau_statut === 'Refusé') {
                $mailC = new mailC();
                $mailC->envoyerMailRefus(
                    $cand['email'], 
                    $cand['prenom'] . ' ' . $cand['nom'],
                    $cand['nom_entreprise'] ?? 'Aptus Recruitment',
                    $cand['email_entreprise'] ?? 'contact@aptus.tn'
                );
            }

            return true;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Ajouter une notification
    public function addNotification($id_candidat, $id_candidature, $message) {
        $sql = "INSERT INTO notifications (id_candidat, id_candidature, message) VALUES (:id_candidat, :id_candidature, :message)";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'id_candidat' => $id_candidat,
                'id_candidature' => $id_candidature,
                'message' => $message
            ]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer les notifications d'un candidat
    public function getNotificationsByCandidat($id_candidat) {
        $sql = "SELECT * FROM notifications WHERE id_candidat = :id ORDER BY date_notif DESC";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute(['id' => $id_candidat]);
            return $req->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Marquer les notifications comme lues
    public function markNotificationsRead($id_candidat) {
        $sql = "UPDATE notifications SET is_read = 1 WHERE id_candidat = :id AND is_read = 0";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute(['id' => $id_candidat]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Supprimer une notification
    public function deleteNotification($id_notif) {
        $sql = "DELETE FROM notifications WHERE id_notif = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute(['id' => $id_notif]);
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer une candidature spécifique par son ID
    public function getCandidatureById($id) {
        $sql = "SELECT * FROM candidatures WHERE id_candidature = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Récupérer les candidatures d'un candidat spécifique
    public function getCandidaturesByCandidat($id_candidat) {
        $sql = "SELECT * FROM candidatures WHERE id_candidat = :id ORDER BY date_candidature DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute(['id' => $id_candidat]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    // Vérifier si un candidat a déjà postulé à une offre
    public function hasAlreadyApplied($id_candidat, $id_offre) {
        $sql = "SELECT COUNT(*) FROM candidatures WHERE id_candidat = :id_c && id_offre = :id_o";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'id_c' => $id_candidat,
                'id_o' => $id_offre
            ]);
            return $req->fetchColumn() > 0;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }

    public function saveAiReport($id_candidature, $report) {
        $sql = "UPDATE candidatures SET ai_report = :report WHERE id_candidature = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([
                'report' => $report,
                'id' => $id_candidature
            ]);
            return true;
        } catch (Exception $e) {
            die('Error:' . $e->getMessage());
        }
    }
}
?>
