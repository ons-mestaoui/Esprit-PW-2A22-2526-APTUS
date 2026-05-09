<?php
class Candidat {
    private ?int $id_candidat;
    private ?string $competences;
    private ?string $niveauEtudes;
    private ?string $niveau;

    public function __construct(
        ?int $id_candidat = null,
        ?string $competences = null,
        ?string $niveauEtudes = null,
        ?string $niveau = null
    ) {
        $this->id_candidat = $id_candidat;
        $this->competences = $competences;
        $this->niveauEtudes = $niveauEtudes;
        $this->niveau = $niveau;
    }

    public function getIdCandidat(): ?int { return $this->id_candidat; }
    public function setIdCandidat(?int $id_candidat): void { $this->id_candidat = $id_candidat; }

    public function getCompetences(): ?string { return $this->competences; }
    public function setCompetences(?string $competences): void { $this->competences = $competences; }

    public function getNiveauEtudes(): ?string { return $this->niveauEtudes; }
    public function setNiveauEtudes(?string $niveauEtudes): void { $this->niveauEtudes = $niveauEtudes; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): void { $this->niveau = $niveau; }
}
?>
