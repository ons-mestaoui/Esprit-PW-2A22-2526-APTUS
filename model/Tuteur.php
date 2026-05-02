<?php
class Tuteur {
    private ?int $id_tuteur;
    private ?string $specialite;
    private ?string $experience;
    private ?string $biographie;

    public function __construct(
        ?int $id_tuteur = null,
        ?string $specialite = null,
        ?string $experience = null,
        ?string $biographie = null
    ) {
        $this->id_tuteur = $id_tuteur;
        $this->specialite = $specialite;
        $this->experience = $experience;
        $this->biographie = $biographie;
    }

    public function getIdTuteur(): ?int { return $this->id_tuteur; }
    public function setIdTuteur(?int $id_tuteur): void { $this->id_tuteur = $id_tuteur; }

    public function getSpecialite(): ?string { return $this->specialite; }
    public function setSpecialite(?string $specialite): void { $this->specialite = $specialite; }

    public function getExperience(): ?string { return $this->experience; }
    public function setExperience(?string $experience): void { $this->experience = $experience; }

    public function getBiographie(): ?string { return $this->biographie; }
    public function setBiographie(?string $biographie): void { $this->biographie = $biographie; }
}
?>
