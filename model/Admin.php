<?php
class Admin {
    private ?int $id_admin;
    private ?string $niveau;

    public function __construct(?int $id_admin = null, ?string $niveau = null) {
        $this->id_admin = $id_admin;
        $this->niveau = $niveau;
    }

    public function getIdAdmin(): ?int { return $this->id_admin; }
    public function setIdAdmin(?int $id_admin): void { $this->id_admin = $id_admin; }

    public function getNiveau(): ?string { return $this->niveau; }
    public function setNiveau(?string $niveau): void { $this->niveau = $niveau; }
}
?>
