<?php

class GuideRecrutement {
    private ?int $id_guide;
    private ?int $id_cv;
    private ?int $id_candidat;
    private ?string $titre_poste;
    private ?string $contenu_json;
    private ?string $date_creation;

    public function __construct(
        ?int $id_guide = null,
        ?int $id_cv = null,
        ?int $id_candidat = null,
        ?string $titre_poste = "",
        ?string $contenu_json = "",
        ?string $date_creation = null
    ) {
        $this->id_guide = $id_guide;
        $this->id_cv = $id_cv;
        $this->id_candidat = $id_candidat;
        $this->titre_poste = $titre_poste;
        $this->contenu_json = $contenu_json;
        $this->date_creation = $date_creation;
    }

    // Getters
    public function getIdGuide(): ?int { return $this->id_guide; }
    public function getIdCv(): ?int { return $this->id_cv; }
    public function getIdCandidat(): ?int { return $this->id_candidat; }
    public function getTitrePoste(): ?string { return $this->titre_poste; }
    public function getContenuJson(): ?string { return $this->contenu_json; }
    public function getDateCreation(): ?string { return $this->date_creation; }
}
?>
