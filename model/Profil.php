<?php
class Profil {
    private ?int $id_profil;
    private ?int $id_utilisateur;
    private ?string $photo;
    private ?string $bio;
    private ?string $adresse;
    private ?string $ville;
    private ?string $pays;
    private ?string $dateNaissance;
    private ?string $linkedin;
    private ?string $siteWeb;
    private ?string $dateCreation;
    private ?string $dateMiseAJour;

    public function __construct(
        ?int $id_profil = null,
        ?int $id_utilisateur = null,
        ?string $photo = null,
        ?string $bio = null,
        ?string $adresse = null,
        ?string $ville = null,
        ?string $pays = null,
        ?string $dateNaissance = null,
        ?string $linkedin = null,
        ?string $siteWeb = null,
        ?string $dateCreation = null,
        ?string $dateMiseAJour = null
    ) {
        $this->id_profil = $id_profil;
        $this->id_utilisateur = $id_utilisateur;
        $this->photo = $photo;
        $this->bio = $bio;
        $this->adresse = $adresse;
        $this->ville = $ville;
        $this->pays = $pays;
        $this->dateNaissance = $dateNaissance;
        $this->linkedin = $linkedin;
        $this->siteWeb = $siteWeb;
        $this->dateCreation = $dateCreation;
        $this->dateMiseAJour = $dateMiseAJour;
    }

    // Getters
    public function getIdProfil(): ?int { return $this->id_profil; }
    public function getIdUtilisateur(): ?int { return $this->id_utilisateur; }
    public function getPhoto(): ?string { return $this->photo; }
    public function getBio(): ?string { return $this->bio; }
    public function getAdresse(): ?string { return $this->adresse; }
    public function getVille(): ?string { return $this->ville; }
    public function getPays(): ?string { return $this->pays; }
    public function getDateNaissance(): ?string { return $this->dateNaissance; }
    public function getLinkedin(): ?string { return $this->linkedin; }
    public function getSiteWeb(): ?string { return $this->siteWeb; }
    public function getDateCreation(): ?string { return $this->dateCreation; }
    public function getDateMiseAJour(): ?string { return $this->dateMiseAJour; }

    // Setters
    public function setIdProfil(?int $id_profil): void { $this->id_profil = $id_profil; }
    public function setIdUtilisateur(?int $id_utilisateur): void { $this->id_utilisateur = $id_utilisateur; }
    public function setPhoto(?string $photo): void { $this->photo = $photo; }
    public function setBio(?string $bio): void { $this->bio = $bio; }
    public function setAdresse(?string $adresse): void { $this->adresse = $adresse; }
    public function setVille(?string $ville): void { $this->ville = $ville; }
    public function setPays(?string $pays): void { $this->pays = $pays; }
    public function setDateNaissance(?string $dateNaissance): void { $this->dateNaissance = $dateNaissance; }
    public function setLinkedin(?string $linkedin): void { $this->linkedin = $linkedin; }
    public function setSiteWeb(?string $siteWeb): void { $this->siteWeb = $siteWeb; }
    public function setDateCreation(?string $dateCreation): void { $this->dateCreation = $dateCreation; }
    public function setDateMiseAJour(?string $dateMiseAJour): void { $this->dateMiseAJour = $dateMiseAJour; }
}
?>
