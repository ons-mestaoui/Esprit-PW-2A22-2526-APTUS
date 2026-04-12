<?php
class Inscription
{
    private ?int $id_inscri;
    private int $id_user;
    private int $id_formation;
    private string $date_inscription;
    private string $statut;
    private int $progression;

    public function __construct(int $id_user, int $id_formation, string $statut = 'En cours', int $progression = 0)
    {
        $this->id_user = $id_user;
        $this->id_formation = $id_formation;
        $this->statut = $statut;
        $this->progression = $progression;
    }

    // Getters
    public function getIdInscri()
    {
        return $this->id_inscri;
    }
    public function getIdUser()
    {
        return $this->id_user;
    }
    public function getIdFormation()
    {
        return $this->id_formation;
    }
    public function getDateInscription()
    {
        return $this->date_inscription;
    }
    public function getStatut()
    {
        return $this->statut;
    }
    public function getProgression()
    {
        return $this->progression;
    }

    // Setters
    public function setStatut(string $statut)
    {
        $this->statut = $statut;
    }
    public function setProgression(int $progression)
    {
        $this->progression = $progression;
    }
}