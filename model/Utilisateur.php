<?php
class Utilisateur {
    private ?int $id_utilisateur;
    private string $nom;
    private string $prenom;
    private string $email;
    private string $motDePasse;
    private string $role;
    private ?string $telephone;
    private ?string $photo;

    public function __construct(?int $id_utilisateur, string $nom, string $prenom, string $email, string $motDePasse, string $role, ?string $telephone = null, ?string $photo = null) {
        $this->id_utilisateur = $id_utilisateur;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->motDePasse = $motDePasse;
        $this->role = $role;
        $this->telephone = $telephone;
        $this->photo = $photo;
    }

    // Getters
    public function getIdUtilisateur(): ?int {
        return $this->id_utilisateur;
    }

    public function getNom(): string {
        return $this->nom;
    }

    public function getPrenom(): string {
        return $this->prenom;
    }

    public function getEmail(): string {
        return $this->email;
    }

    public function getMotDePasse(): string {
        return $this->motDePasse;
    }

    public function getRole(): string {
        return $this->role;
    }

    public function getTelephone(): ?string {
        return $this->telephone;
    }

    // Setters
    public function setIdUtilisateur(?int $id_utilisateur): void {
        $this->id_utilisateur = $id_utilisateur;
    }

    public function setNom(string $nom): void {
        $this->nom = $nom;
    }

    public function setPrenom(string $prenom): void {
        $this->prenom = $prenom;
    }

    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function setMotDePasse(string $motDePasse): void {
        $this->motDePasse = $motDePasse;
    }

    public function setRole(string $role): void {
        $this->role = $role;
    }

    public function setTelephone(?string $telephone): void {
        $this->telephone = $telephone;
    }

    public function getPhoto(): ?string {
        return $this->photo;
    }

    public function setPhoto(?string $photo): void {
        $this->photo = $photo;
    }
}
?>
