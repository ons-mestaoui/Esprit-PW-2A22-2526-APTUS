<?php
class RapportMarche
{
    private $id_rapport_marche;
    private $id_admin;
    private $titre;
    private $description;
    private $date_publication;
    private $region;
    private $secteur_principal;
    private $salaire_moyen_global;
    private $salaire_min_global;
    private $salaire_max_global;
    private $tendance_generale;
    private $niveau_demande_global;
    private $nombre_donnees;
    private $auteur;
    private $contenu_detaille;
    private $image_couverture;
    private $vues;

    public function __construct($id_admin, $titre, $description, $date_publication, $region, $secteur_principal, $salaire_moyen_global, $salaire_min_global, $salaire_max_global, $tendance_generale, $niveau_demande_global, $nombre_donnees, $auteur, $contenu_detaille = '', $image_couverture = '', $vues = 0, $id_rapport_marche = null)
    {
        $this->id_rapport_marche = $id_rapport_marche;
        $this->id_admin = $id_admin;
        $this->titre = $titre;
        $this->description = $description;
        $this->date_publication = $date_publication;
        $this->region = $region;
        $this->secteur_principal = $secteur_principal;
        $this->salaire_moyen_global = $salaire_moyen_global;
        $this->salaire_min_global = $salaire_min_global;
        $this->salaire_max_global = $salaire_max_global;
        $this->tendance_generale = $tendance_generale;
        $this->niveau_demande_global = $niveau_demande_global;
        $this->nombre_donnees = $nombre_donnees;
        $this->auteur = $auteur;
        $this->contenu_detaille = $contenu_detaille;
        $this->image_couverture = $image_couverture;
        $this->vues = $vues;
    }

    public function getIdRapportMarche() { return $this->id_rapport_marche; }
    public function getIdAdmin() { return $this->id_admin; }
    public function getTitre() { return $this->titre; }
    public function getDescription() { return $this->description; }
    public function getDatePublication() { return $this->date_publication; }
    public function getRegion() { return $this->region; }
    public function getSecteurPrincipal() { return $this->secteur_principal; }
    public function getSalaireMoyenGlobal() { return $this->salaire_moyen_global; }
    public function getSalaireMinGlobal() { return $this->salaire_min_global; }
    public function getSalaireMaxGlobal() { return $this->salaire_max_global; }
    public function getTendanceGenerale() { return $this->tendance_generale; }
    public function getNiveauDemandeGlobal() { return $this->niveau_demande_global; }
    public function getNombreDonnees() { return $this->nombre_donnees; }
    public function getAuteur() { return $this->auteur; }
    public function getContenuDetaille() { return $this->contenu_detaille; }
    public function getImageCouverture() { return $this->image_couverture; }
    public function getVues() { return $this->vues; }
}
?>
