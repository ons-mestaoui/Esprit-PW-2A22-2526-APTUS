<?php
require_once __DIR__ . '/../config.php';

class TuteurDashboardController
{
    public function __construct()
    {
        // Plus besoin de dossier uploads car tout passe en Base64 dans la BDD
    }

    public function getStudentsByFormation($id_formation)
    {
        $db = config::getConnexion();
        try {
            $query = "
                SELECT i.id_user, i.progression, i.statut, COALESCE(u.nom, 'Anonyme') as nom_etudiant, u.email
                FROM inscription i
                JOIN candidat c ON i.id_user = c.id
                LEFT JOIN utilisateur u ON i.id_user = u.id
                WHERE i.id_formation = :id_formation
                ORDER BY u.nom
            ";
            $stmt = $db->prepare($query);
            $stmt->execute(['id_formation' => $id_formation]);
            $students = $stmt->fetchAll();
            
            // --- SYNC SMART PROGRESSION ---
            require_once __DIR__ . '/InscriptionController.php';
            $inscriC = new InscriptionController();
            $resources = $this->getResources($id_formation);
            $total_chapters = count($resources);
            
            if ($total_chapters > 0) {
                foreach ($students as &$s) {
                    if ($s['statut'] !== 'Terminée' && $s['progression'] < 100) {
                        // Recalcul forcer pour le tuteur seulement si non fini
                        $s['progression'] = $inscriC->calculateSmartPercentage($s['id_user'], $id_formation, $total_chapters);
                    } else {
                        $s['progression'] = 100;
                    }
                }
            } else {
                // Pas de chapitres = On s'assure que la prog est à 0 si non fini
                foreach ($students as &$s) {
                    if ($s['statut'] !== 'Terminée') {
                        $s['progression'] = 0;
                    }
                }
            }
            return $students;
        } catch (Exception $e) {
            // Fallback pour compatibilité OS/Table Names
            return [];
        }
    }

    public function updateProgression($id_formation, $id_user, $progression)
    {
        $db = config::getConnexion();
        $statut = ($progression >= 100) ? 'Terminée' : (($progression > 0) ? 'En cours' : 'En attente');
        try {
            $stmt = $db->prepare("UPDATE inscription SET progression = :p, statut = :s WHERE id_formation = :f AND id_user = :u");
            $success = $stmt->execute(['p' => $progression, 's' => $statut, 'f' => $id_formation, 'u' => $id_user]);
            
            if ($success && $progression >= 100) {
                // CONCEPT 2 & 4 : Notification de débloquage (Game-alike XP)
                require_once __DIR__ . '/NotificationController.php';
                require_once __DIR__ . '/FormationController.php';
                
                $fC = new FormationController();
                $formation = $fC->getFormationById($id_formation);
                $titre = $f['titre'] ?? $formation['titre'] ?? 'une formation';
                
                // 1. Notif de succès
                NotificationController::creerNotification(
                    $id_user, 
                    'success', 
                    "Félicitations ! Vous avez terminé la formation : $titre. 🎓", 
                    "certificate.php?f_id=$id_formation", 
                    'award'
                );

                // 2. Détection des nouveaux cours débloqués (Prerequis logic)
                $stmtNext = $db->prepare("SELECT id_formation, titre FROM Formation WHERE prerequis_id = :pid");
                $stmtNext->execute(['pid' => $id_formation]);
                $nextOnes = $stmtNext->fetchAll();
                
                foreach ($nextOnes as $next) {
                    NotificationController::creerNotification(
                        $id_user,
                        'info',
                        "🚀 Nouveau débloqué : " . $next['titre'] . ". Découvrez votre nouveau chemin sur la Skill Map !",
                        "skill_tree.php?id=" . $next['id_formation'],
                        'unlock'
                    );
                }
            }
            return $success;
        } catch (Exception $e) {
            try {
                $stmt = $db->prepare("UPDATE Inscription SET progression = :p, statut = :s WHERE id_formation = :f AND id_user = :u");
                return $stmt->execute(['p' => $progression, 's' => $statut, 'f' => $id_formation, 'u' => $id_user]);
            } catch (Exception $e2) {
                return false;
            }
        }
    }

    public function getResources($id_formation)
    {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT description FROM formation WHERE id_formation = :id");
        $stmt->execute(['id' => $id_formation]);
        $row = $stmt->fetch();
        if ($row && preg_match('/<!-- APTUS_RESOURCES: (.*?) -->/s', $row['description'], $matches)) {
            return json_decode($matches[1], true) ?: [];
        }
        return [];
    }

    public function addResource($id_formation, $type, $titre, $data_or_url)
    {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT description FROM formation WHERE id_formation = :id");
        $stmt->execute(['id' => $id_formation]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $description = $row['description'];
        $resources = [];
        if (preg_match('/<!-- APTUS_RESOURCES: (.*?) -->/s', $description, $matches)) {
            $resources = json_decode($matches[1], true) ?: [];
            $description = preg_replace('/<!-- APTUS_RESOURCES: .*? -->/s', '', $description);
        }

        $resources[] = [
            'id' => uniqid(),
            'type' => $type,
            'titre' => $titre,
            'url' => $data_or_url, // Ici, l'URL peut être un lien YouTube OU une chaîne Base64 (data:application/pdf;base64,...)
            'date' => date('Y-m-d H:i:s')
        ];

        $description .= '<!-- APTUS_RESOURCES: ' . json_encode($resources) . ' -->';

        $stmtU = $db->prepare("UPDATE formation SET description = :desc WHERE id_formation = :id");
        return $stmtU->execute(['desc' => $description, 'id' => $id_formation]);
    }
    
    public function deleteResource($id_formation, $resource_id)
    {
        $db = config::getConnexion();
        $stmt = $db->prepare("SELECT description FROM formation WHERE id_formation = :id");
        $stmt->execute(['id' => $id_formation]);
        $row = $stmt->fetch();
        if (!$row) return false;

        $description = $row['description'];
        $resources = [];
        if (preg_match('/<!-- APTUS_RESOURCES: (.*?) -->/s', $description, $matches)) {
            $resources = json_decode($matches[1], true) ?: [];
            $description = preg_replace('/<!-- APTUS_RESOURCES: .*? -->/s', '', $description);
            
            $resources = array_filter($resources, function($r) use ($resource_id) {
                return $r['id'] !== $resource_id;
            });
            $resources = array_values($resources);
            
            $description .= '<!-- APTUS_RESOURCES: ' . json_encode($resources) . ' -->';
            
            $stmtU = $db->prepare("UPDATE formation SET description = :desc WHERE id_formation = :id");
            return $stmtU->execute(['desc' => $description, 'id' => $id_formation]);
        }
        return true;
    }

    public function getGlobalStats($id_tuteur) {
        $db = config::getConnexion();
        try {
            // Calcul complet via requête SQL ou on récupère juste tout
            $stmt = $db->prepare("
                SELECT i.progression, i.statut
                FROM inscription i
                JOIN candidat c ON i.id_user = c.id
                JOIN Formation f ON i.id_formation = f.id_formation
                WHERE f.id_tuteur = :id_tuteur
            ");
            $stmt->execute(['id_tuteur' => $id_tuteur]);
            $inscriptions = $stmt->fetchAll();
        } catch (Exception $e) {
            try {
                $stmt = $db->prepare("
                    SELECT i.progression, i.statut
                    FROM Inscription i
                    JOIN candidat c ON i.id_user = c.id
                    JOIN Formation f ON i.id_formation = f.id_formation
                    WHERE f.id_tuteur = :id_tuteur
                ");
                $stmt->execute(['id_tuteur' => $id_tuteur]);
                $inscriptions = $stmt->fetchAll();
            } catch (Exception $e2) {
                $inscriptions = [];
            }
        }

        $total_students = count($inscriptions);
        $completed = 0;
        foreach ($inscriptions as $i) {
            if ($i['progression'] >= 100 || $i['statut'] === 'Terminée') {
                $completed++;
            }
        }
        $taux = $total_students > 0 ? round(($completed / $total_students) * 100) : 0;
        return [
            'total_students' => $total_students,
            'completed' => $completed,
            'taux' => $taux
        ];
    }
    /**
     * RÉEL TEMPS : Récupérer les alertes IA basées sur les émotions récentes
     * Scanne les émotions des 15 dernières minutes pour les formations du tuteur
     */
    public function getRecentAIAlerts($id_tuteur)
    {
        $db = config::getConnexion();
        try {
            $query = "
                SELECT re.*, f.titre as formation_titre, u.nom as etudiant_nom
                FROM rapport_emotions re
                JOIN formation f ON re.id_formation = f.id_formation
                JOIN utilisateur u ON re.id_candidat = u.id
                WHERE f.id_tuteur = :id_tuteur
                AND re.date_mesure >= (NOW() - INTERVAL 15 MINUTE)
                ORDER BY re.date_mesure DESC
            ";
            $stmt = $db->prepare($query);
            $stmt->execute(['id_tuteur' => $id_tuteur]);
            $emotions = $stmt->fetchAll();

            $alerts = [];
            $grouped = [];

            // On groupe par étudiant et formation
            foreach ($emotions as $e) {
                $key = $e['id_candidat'] . '_' . $e['id_formation'];
                if (!isset($grouped[$key])) {
                    $grouped[$key] = [
                        'nom' => $e['etudiant_nom'],
                        'formation' => $e['formation_titre'],
                        'emotions' => []
                    ];
                }
                $grouped[$key]['emotions'][] = $e['emotion_detectee'];
            }

            // Logique d'alerte : Si confusion ou tristesse > 3 occurrences en 15 min
            foreach ($grouped as $data) {
                $counts = array_count_values($data['emotions']);
                $confusionCount = ($counts['Confusion'] ?? 0) + ($counts['Triste'] ?? 0) + ($counts['Colère'] ?? 0);
                
                if ($confusionCount >= 3) {
                    $alerts[] = [
                        'type' => 'critical',
                        'message' => "L'IA détecte une difficulté majeure pour <strong>" . $data['nom'] . "</strong> sur le cours <em>" . $data['formation'] . "</em>.",
                        'conseil' => "L'élève semble perdu ou frustré. Essayez de lui envoyer un message privé ou de faire une pause explicative.",
                        'icon' => 'alert-circle'
                    ];
                }
            }

            return $alerts;
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * 🧩 LOGIQUE MÉTIER GESTION FORMATION (MVC COMPLIANCE)
     */
    public function getTuteurManagePageData($id_formation) {
        require_once __DIR__ . '/FormationController.php';
        $fC = new FormationController();
        
        $formation = $fC->getFormationById($id_formation);
        if (!$formation) return null;

        $formation = $fC->formatFormationForView($formation);
        $students  = $this->getStudentsByFormation($id_formation);
        $resources = $this->getResources($id_formation);

        return [
            'formation' => $formation,
            'students'  => $students,
            'resources' => $resources
        ];
    }
}
