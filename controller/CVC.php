<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/CV.php';

class CVC
{
    public function addCV(CV $cv)
    {
        $db = config::getConnexion();
        $query = $db->prepare(
            'INSERT INTO cv (id_candidat, id_template, nomDocument, nomComplet, titrePoste, resume, infoContact, experience, formation, competences, langues, urlPhoto, couleurTheme, statut, dateCreation, dateMiseAJour, ai_analysis, is_tailored, target_job_url, tailoring_report) 
            VALUES (:id_candidat, :id_template, :nomDocument, :nomComplet, :titrePoste, :resume, :infoContact, :experience, :formation, :competences, :langues, :urlPhoto, :couleurTheme, :statut, NOW(), NOW(), :ai_analysis, :is_tailored, :target_job_url, :tailoring_report)'
        );
        $query->execute([
            'id_candidat' => $cv->getIdCandidat(),
            'id_template' => $cv->getIdTemplate(),
            'nomDocument' => $cv->getNomDocument(),
            'nomComplet'  => $cv->getNomComplet(),
            'titrePoste'  => $cv->getTitrePoste(),
            'resume'      => $cv->getResume(),
            'infoContact' => $cv->getInfoContact(),
            'experience'  => $cv->getExperience(),
            'formation'   => $cv->getFormation(),
            'competences' => $cv->getCompetences(),
            'langues'     => $cv->getLangues(),
            'urlPhoto'    => $cv->getUrlPhoto(),
            'couleurTheme'=> $cv->getCouleurTheme(),
            'statut'      => $cv->getStatut(),
            'ai_analysis' => $cv->getAiAnalysis(),
            'is_tailored' => $cv->getIsTailored(),
            'target_job_url' => $cv->getTargetJobUrl(),
            'tailoring_report' => $cv->getTailoringReport()
        ]);
        return $db->lastInsertId();
    }

    public function listCVByCandidat($id_candidat)
    {
        $db = config::getConnexion();
        if ($id_candidat === null) {
            // Mode dev: afficher tous les CVs
            $query = $db->prepare('SELECT * FROM cv ORDER BY dateMiseAJour DESC');
            $query->execute();
        } else {
            $query = $db->prepare('SELECT * FROM cv WHERE id_candidat = :id OR id_candidat IS NULL ORDER BY dateMiseAJour DESC');
            $query->execute(['id' => $id_candidat]);
        }
        return $query->fetchAll();
    }

    public function getCVById($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare('SELECT * FROM cv WHERE id_cv = :id');
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public function updateCV($id, CV $cv)
    {
        $db = config::getConnexion();
        $query = $db->prepare(
            'UPDATE cv SET 
                nomDocument   = :nomDocument,
                nomComplet    = :nomComplet,
                titrePoste    = :titrePoste,
                resume        = :resume,
                infoContact   = :infoContact,
                experience    = :experience,
                formation     = :formation,
                competences   = :competences,
                langues       = :langues,
                urlPhoto      = :urlPhoto,
                couleurTheme  = :couleurTheme,
                ai_analysis   = :ai_analysis,
                is_tailored   = :is_tailored,
                target_job_url = :target_job_url,
                tailoring_report = :tailoring_report,
                dateMiseAJour = NOW()
            WHERE id_cv = :id'
        );
        $query->execute([
            'nomDocument' => $cv->getNomDocument(),
            'nomComplet'  => $cv->getNomComplet(),
            'titrePoste'  => $cv->getTitrePoste(),
            'resume'      => $cv->getResume(),
            'infoContact' => $cv->getInfoContact(),
            'experience'  => $cv->getExperience(),
            'formation'   => $cv->getFormation(),
            'competences' => $cv->getCompetences(),
            'langues'     => $cv->getLangues(),
            'urlPhoto'    => $cv->getUrlPhoto(),
            'couleurTheme'=> $cv->getCouleurTheme(),
            'ai_analysis' => $cv->getAiAnalysis(),
            'is_tailored' => $cv->getIsTailored(),
            'target_job_url' => $cv->getTargetJobUrl(),
            'tailoring_report' => $cv->getTailoringReport(),
            'id'          => $id
        ]);
    }

    public function updateTailoring($id, $isTailored, $jobUrl, $report)
    {
        $db = config::getConnexion();
        $query = $db->prepare('UPDATE cv SET is_tailored = :is_t, target_job_url = :url, tailoring_report = :report WHERE id_cv = :id');
        $query->execute([
            'is_t' => $isTailored,
            'url' => $jobUrl,
            'report' => $report,
            'id' => $id
        ]);
    }

    public function deleteCV($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare('DELETE FROM cv WHERE id_cv = :id');
        $query->execute(['id' => $id]);
    }

    public function getTotalCVs()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) as total FROM cv');
            $result = $query->fetch();
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    public function getCVGrowth()
    {
        $db = config::getConnexion();
        try {
            $currMonth = $db->query('SELECT COUNT(*) FROM cv WHERE MONTH(dateCreation) = MONTH(CURRENT_DATE()) AND YEAR(dateCreation) = YEAR(CURRENT_DATE())')->fetchColumn();
            $lastMonth = $db->query('SELECT COUNT(*) FROM cv WHERE dateCreation >= DATE_SUB(CURRENT_DATE(), INTERVAL 2 MONTH) AND dateCreation < DATE_SUB(CURRENT_DATE(), INTERVAL 1 MONTH)')->fetchColumn();
            return ['current' => $currMonth, 'last' => $lastMonth];
        } catch (Exception $e) {
            return ['current' => 0, 'last' => 0];
        }
    }

    public function getRecentCVAdditionsCount()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) FROM cv WHERE dateCreation >= DATE_SUB(NOW(), INTERVAL 7 DAY)');
            return $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }
}
