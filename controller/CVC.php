<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/CV.php';

class CVC
{
    public function addCV(CV $cv)
    {
        $db = config::getConnexion();
        $query = $db->prepare(
            'INSERT INTO cv (id_candidat, id_template, nomDocument, nomComplet, titrePoste, resume, infoContact, experience, formation, competences, langues, urlPhoto, couleurTheme, statut, dateCreation, dateMiseAJour) 
            VALUES (:id_candidat, :id_template, :nomDocument, :nomComplet, :titrePoste, :resume, :infoContact, :experience, :formation, :competences, :langues, :urlPhoto, :couleurTheme, :statut, NOW(), NOW())'
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
            'statut'      => $cv->getStatut()
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
            'id'          => $id
        ]);
    }

    public function deleteCV($id)
    {
        $db = config::getConnexion();
        $query = $db->prepare('DELETE FROM cv WHERE id_cv = :id');
        $query->execute(['id' => $id]);
    }
}
