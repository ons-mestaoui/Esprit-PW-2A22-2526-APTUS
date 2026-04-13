<?php
class DonneeMarche
{
    private $id_donnee;
    private $id_rapport_marche;
    private $domaine;
    private $competence;
    private $salaire_min;
    private $salaire_max;
    private $salaire_moyen;
    private $demande;
    private $date_collecte;
    private $description;

    public function __construct($id_rapport_marche, $domaine, $competence, $salaire_min, $salaire_max, $salaire_moyen, $demande, $date_collecte, $description = '', $id_donnee = null)
    {
        $this->id_donnee = $id_donnee;
        $this->id_rapport_marche = $id_rapport_marche;
        $this->domaine = $domaine;
        $this->competence = $competence;
        $this->salaire_min = $salaire_min;
        $this->salaire_max = $salaire_max;
        $this->salaire_moyen = $salaire_moyen;
        $this->demande = $demande;
        $this->date_collecte = $date_collecte;
        $this->description = $description;
    }

    public function getIdDonnee() { return $this->id_donnee; }
    public function getIdRapportMarche() { return $this->id_rapport_marche; }
    public function getDomaine() { return $this->domaine; }
    public function getCompetence() { return $this->competence; }
    public function getSalaireMin() { return $this->salaire_min; }
    public function getSalaireMax() { return $this->salaire_max; }
    public function getSalaireMoyen() { return $this->salaire_moyen; }
    public function getDemande() { return $this->demande; }
    public function getDateCollecte() { return $this->date_collecte; }
    public function getDescription() { return $this->description; }
}
?>
