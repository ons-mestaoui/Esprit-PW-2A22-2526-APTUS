<?php

class Template
{
    private ?int $id_template;
    private string $nom;
    private string $description;
    private string $urlMiniature;
    private string $structureHtml;
    private int $estPremium;
    private ?string $dateCreation;

    public function __construct(
        ?int $id_template = null,
        string $nom = "",
        string $description = "",
        string $urlMiniature = "",
        string $structureHtml = "",
        int $estPremium = 0,
        ?string $dateCreation = null
    ) {
        $this->id_template = $id_template;
        $this->nom = $nom;
        $this->description = $description;
        $this->urlMiniature = $urlMiniature;
        $this->structureHtml = $structureHtml;
        $this->estPremium = $estPremium;
        $this->dateCreation = $dateCreation;
    }

    public function getIdTemplate(): ?int { return $this->id_template; }
    public function getNom(): string { return $this->nom; }
    public function getDescription(): string { return $this->description; }
    public function getUrlMiniature(): string { return $this->urlMiniature; }
    public function getStructureHtml(): string { return $this->structureHtml; }
    public function getEstPremium(): int { return $this->estPremium; }
    public function getDateCreation(): ?string { return $this->dateCreation; }

    public function setNom(string $nom): void { $this->nom = $nom; }
    public function setDescription(string $description): void { $this->description = $description; }
    public function setUrlMiniature(string $urlMiniature): void { $this->urlMiniature = $urlMiniature; }
    public function setStructureHtml(string $structureHtml): void { $this->structureHtml = $structureHtml; }
    public function setEstPremium(int $estPremium): void { $this->estPremium = $estPremium; }
}
