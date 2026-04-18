<?php
class offre{
    private $id_offre;
    private $titre;
    private $description;
    private $domaine;
    private $competences_requises;
    private $experience_requise;
    private $salaire;
    private $question;
    private $date_publication;
    private $date_expir;
    private $img_post;

    

    public function __construct( string $titre, string $description, string $domaine, string $competences_requises, string $experience_requise, float $salaire, string $question, string $date_publication, string $date_expir, ?string $img_post = null) {

        $this->titre = $titre;
        $this->description = $description;
        $this->domaine = $domaine;
        $this->competences_requises = $competences_requises;
        $this->experience_requise = $experience_requise;
        $this->salaire = $salaire;
        $this->question = $question;
        $this->date_publication = $date_publication;
        $this->date_expir = $date_expir;
        $this->img_post = $img_post;
        
    }

    public function getTitre() {
        return $this->titre;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDomaine() {
        return $this->domaine;
    }

    public function getCompetencesRequises() {
        return $this->competences_requises;
    }

    public function getExperienceRequise() {
        return $this->experience_requise;
    }
     public function getSalaire() {
        return $this->salaire;
    }
     public function getQuestion() {
        return $this->question;
    }
     public function getDatePublication() {
        return $this->date_publication;
    }
     public function getDateExpir() {
        return $this->date_expir;
    }
    public function getImgPost() {
        return $this->img_post;
    }
    public function setTitre($titre) {
        $this->titre = $titre;
    }
    public function setDescription($description) {
        $this->description = $description;
    }
    public function setDomaine($domaine) {
        $this->domaine = $domaine;
    }
    public function setCompetencesRequises($competences_requises) {
        $this->competences_requises = $competences_requises;
    }
    public function setExperienceRequise($experience_requise) {
        $this->experience_requise = $experience_requise;
    }
    public function setSalaire($salaire) {
        $this->salaire = $salaire;
    }
    public function setQuestion($question) {
        $this->question = $question;
    }
    public function setDatePublication($date_publication) {
        $this->date_publication = $date_publication;
    }
    public function setDateExpir($date_expir) {
        $this->date_expir = $date_expir;
    }
    public function setImgPost($img_post) {
        $this->img_post = $img_post;
    }

}