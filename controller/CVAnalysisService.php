<?php
require_once __DIR__ . '/../config.php';

class CVAnalysisService {
    /**
     * Matches keywords from AI analysis with available jobs.
     */
    public function matchJobs(array $keywords): array {
        // This is a placeholder logic. In a real scenario, you'd query a 'jobs' table.
        // For now, we return some dummy data that matches the 'fruity' UI needs.
        return [
            [
                'title' => 'Développeur Fullstack Senior',
                'company' => 'Aptus Tech',
                'match_score' => 95,
                'location' => 'Tunis (Hybride)',
                'salary' => '2500 - 3500 DT'
            ],
            [
                'title' => 'Product Manager IT',
                'company' => 'Digital Solutions',
                'match_score' => 82,
                'location' => 'Remote',
                'salary' => '3000 - 4500 DT'
            ]
        ];
    }

    /**
     * Matches suggested domains with training programs.
     */
    public function matchTrainingsByDomain(array $domains): array {
        $db = config::getConnexion();
        
        // Let's assume we have a 'formation' table. 
        // If not, we return relevant mock data to avoid empty sections.
        try {
            $query = $db->prepare("SELECT * FROM formation LIMIT 3");
            $query->execute();
            $results = $query->fetchAll();
            
            if ($results) {
                return array_map(function($f) {
                    return [
                        'title' => $f['nomFormation'] ?? $f['titre'] ?? 'Formation Aptus',
                        'domain' => $f['domaine'] ?? 'Expertise',
                        'duration' => $f['duree'] ?? '20h',
                        'level' => 'Intermédiaire'
                    ];
                }, $results);
            }
        } catch (Exception $e) {
            // Table might not exist yet in this specific backup
        }

        return [
            [
                'title' => 'Masterclass Architecture Microservices',
                'domain' => 'Backend',
                'duration' => '15h',
                'level' => 'Avancé'
            ],
            [
                'title' => 'UI/UX Design & Psychologie Cognitive',
                'domain' => 'Design',
                'duration' => '12h',
                'level' => 'Intermédiaire'
            ]
        ];
    }
}
