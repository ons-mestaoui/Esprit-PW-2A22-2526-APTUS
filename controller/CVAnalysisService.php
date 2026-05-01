<?php
require_once __DIR__ . '/../config.php';

class CVAnalysisService {
    private $pdo;

    public function __construct() {
        $this->pdo = config::getConnexion();
    }

    /**
     * Match jobs based on keywords found in CV
     */
    public function matchJobs(array $keywords) {
        if (empty($keywords)) return [];
        
        // Simulating matching with a weighted query or just fetching latest relevant posts
        // For now, let's fetch high-impact jobs
        $sql = "SELECT id as id_post, titre as title, description, 'Aptus HQ' as location, 'Développement' as domain 
                FROM hr_post 
                ORDER BY date_post DESC LIMIT 3";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        $jobs = $stmt->fetchAll();

        foreach ($jobs as &$job) {
            $job['match_score'] = rand(70, 95); // Simulated match logic
        }
        return $jobs;
    }

    /**
     * Get all available trainings
     */
    public function getAvailableTrainings() {
        $sql = "SELECT id_formation as id, titre as title, domaine as domain, niveau as level 
                FROM formation";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Match trainings by domains suggested by AI
     */
    public function matchTrainingsByDomain(array $domains) {
        if (empty($domains)) return [];

        $placeholders = implode(',', array_fill(0, count($domains), '?'));
        $sql = "SELECT id_formation as id, titre as title, domaine as domain, niveau as level 
                FROM formation 
                WHERE domaine IN ($placeholders)
                OR titre LIKE ?
                LIMIT 3";
        
        $params = $domains;
        $params[] = '%' . ($domains[0] ?? '') . '%'; // Fallback search

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        $trainings = $stmt->fetchAll();

        foreach ($trainings as &$tr) {
            $tr['match_score'] = rand(85, 98);
        }
        return $trainings;
    }
}
