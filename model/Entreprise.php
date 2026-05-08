<?php
class Entreprise {
    private ?int $id_entreprise;
    private ?string $secteur;
    private ?string $siret;
    private ?string $raisonSociale;
    private ?string $taille;
    private ?int $anneeFondation;

    public function __construct(
        ?int $id_entreprise = null, 
        ?string $secteur = null, 
        ?string $siret = null, 
        ?string $raisonSociale = null, 
        ?string $taille = null, 
        ?int $anneeFondation = null
    ) {
        $this->id_entreprise = $id_entreprise;
        $this->secteur = $secteur;
        $this->siret = $siret;
        $this->raisonSociale = $raisonSociale;
        $this->taille = $taille;
        $this->anneeFondation = $anneeFondation;
    }

    public function getIdEntreprise(): ?int { return $this->id_entreprise; }
    public function setIdEntreprise(?int $id_entreprise): void { $this->id_entreprise = $id_entreprise; }

    public function getSecteur(): ?string { return $this->secteur; }
    public function setSecteur(?string $secteur): void { $this->secteur = $secteur; }

    public function getSiret(): ?string { return $this->siret; }
    public function setSiret(?string $siret): void { $this->siret = $siret; }

    public function getRaisonSociale(): ?string { return $this->raisonSociale; }
    public function setRaisonSociale(?string $raisonSociale): void { $this->raisonSociale = $raisonSociale; }

    public function getTaille(): ?string { return $this->taille; }
    public function setTaille(?string $taille): void { $this->taille = $taille; }

    public function getAnneeFondation(): ?int { return $this->anneeFondation; }
    public function setAnneeFondation(?int $anneeFondation): void { $this->anneeFondation = $anneeFondation; }
}
?>
