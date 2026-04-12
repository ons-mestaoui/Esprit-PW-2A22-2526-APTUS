<?php

class CV
{
    private ?int $id_cv;
    private ?int $id_candidat;
    private ?int $id_template;
    private ?string $nomDocument;
    private ?string $nomComplet;
    private ?string $titrePoste;
    private ?string $resume;
    private ?string $infoContact;
    private ?string $experience;
    private ?string $formation;
    private ?string $competences;
    private ?string $langues;
    private ?string $urlPhoto;
    private ?string $couleurTheme;
    private ?string $statut;
    private ?string $dateCreation;
    private ?string $dateMiseAJour;

    public function __construct(
        ?int $id_cv = null,
        ?int $id_candidat = null,
        ?int $id_template = null,
        ?string $nomDocument = "",
        ?string $nomComplet = "",
        ?string $titrePoste = "",
        ?string $resume = "",
        ?string $infoContact = "",
        ?string $experience = "",
        ?string $formation = "",
        ?string $competences = "",
        ?string $langues = "",
        ?string $urlPhoto = "",
        ?string $couleurTheme = "#2563eb",
        ?string $statut = "en_attente",
        ?string $dateCreation = null,
        ?string $dateMiseAJour = null
    ) {
        $this->id_cv = $id_cv;
        $this->id_candidat = $id_candidat;
        $this->id_template = $id_template;
        $this->nomDocument = $nomDocument;
        $this->nomComplet = $nomComplet;
        $this->titrePoste = $titrePoste;
        $this->resume = $resume;
        $this->infoContact = $infoContact;
        $this->experience = $experience;
        $this->formation = $formation;
        $this->competences = $competences;
        $this->langues = $langues;
        $this->urlPhoto = $urlPhoto;
        $this->couleurTheme = $couleurTheme;
        $this->statut = $statut;
        $this->dateCreation = $dateCreation;
        $this->dateMiseAJour = $dateMiseAJour;
    }

    // Getters
    public function getIdCv(): ?int { return $this->id_cv; }
    public function getIdCandidat(): ?int { return $this->id_candidat; }
    public function getIdTemplate(): ?int { return $this->id_template; }
    public function getNomDocument(): ?string { return $this->nomDocument; }
    public function getNomComplet(): ?string { return $this->nomComplet; }
    public function getTitrePoste(): ?string { return $this->titrePoste; }
    public function getResume(): ?string { return $this->resume; }
    public function getInfoContact(): ?string { return $this->infoContact; }
    public function getExperience(): ?string { return $this->experience; }
    public function getFormation(): ?string { return $this->formation; }
    public function getCompetences(): ?string { return $this->competences; }
    public function getLangues(): ?string { return $this->langues; }
    public function getUrlPhoto(): ?string { return $this->urlPhoto; }
    public function getCouleurTheme(): ?string { return $this->couleurTheme; }
    public function getStatut(): ?string { return $this->statut; }
    public function getDateCreation(): ?string { return $this->dateCreation; }
    public function getDateMiseAJour(): ?string { return $this->dateMiseAJour; }

    // Setters
    public function setNomDocument(?string $nom): void { $this->nomDocument = $nom; }
    public function setNomComplet(?string $nom): void { $this->nomComplet = $nom; }
    public function setTitrePoste(?string $titre): void { $this->titrePoste = $titre; }
    public function setResume(?string $resume): void { $this->resume = $resume; }
    public function setInfoContact(?string $info): void { $this->infoContact = $info; }
    public function setExperience(?string $exp): void { $this->experience = $exp; }
    public function setFormation(?string $form): void { $this->formation = $form; }
    public function setCompetences(?string $comp): void { $this->competences = $comp; }
    public function setLangues(?string $lang): void { $this->langues = $lang; }
    public function setUrlPhoto(?string $url): void { $this->urlPhoto = $url; }
    public function setCouleurTheme(?string $color): void { $this->couleurTheme = $color; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
}
