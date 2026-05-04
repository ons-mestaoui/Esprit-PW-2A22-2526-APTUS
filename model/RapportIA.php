<?php

class RapportIA {
    private ?int $id_rapport_ia;
    private ?int $id_cv;
    private ?int $scoreGlobal;
    private ?string $pointsForts;
    private ?string $pointsFaibles;
    private ?string $sectionsManquantes;
    private ?string $suggestions;
    private ?string $dateAnalyse;

    public function __construct(
        ?int $id_rapport_ia = null,
        ?int $id_cv = null,
        ?int $scoreGlobal = 0,
        ?string $pointsForts = "",
        ?string $pointsFaibles = "",
        ?string $sectionsManquantes = "",
        ?string $suggestions = "",
        ?string $dateAnalyse = null
    ) {
        $this->id_rapport_ia = $id_rapport_ia;
        $this->id_cv = $id_cv;
        $this->scoreGlobal = $scoreGlobal;
        $this->pointsForts = $pointsForts;
        $this->pointsFaibles = $pointsFaibles;
        $this->sectionsManquantes = $sectionsManquantes;
        $this->suggestions = $suggestions;
        $this->dateAnalyse = $dateAnalyse;
    }

    // Getters
    public function getIdRapportIa(): ?int { return $this->id_rapport_ia; }
    public function getIdCv(): ?int { return $this->id_cv; }
    public function getScoreGlobal(): ?int { return $this->scoreGlobal; }
    public function getPointsForts(): ?string { return $this->pointsForts; }
    public function getPointsFaibles(): ?string { return $this->pointsFaibles; }
    public function getSectionsManquantes(): ?string { return $this->sectionsManquantes; }
    public function getSuggestions(): ?string { return $this->suggestions; }
    public function getDateAnalyse(): ?string { return $this->dateAnalyse; }
}
?>
