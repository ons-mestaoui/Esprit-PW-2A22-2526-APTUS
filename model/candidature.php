<?php
class candidature{
    private $id_candidature;
    private $id_candidat;
    private $id_offre;
    private $nom;
    private $prenom;
    private $email;
    private $date_candidature;
    private $reponses_ques;
    private $cv_cand;
    private $note;
    private $statut;

    public function __construct($id_candidat, $id_offre, $nom, $prenom, $email, $date_candidature, $reponses_ques, $cv_cand, $note, $statut) {
        $this->id_candidat = $id_candidat;
        $this->id_offre = $id_offre;
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->date_candidature = $date_candidature;
        $this->reponses_ques = $reponses_ques;
        $this->cv_cand = $cv_cand;
        $this->note = $note;
        $this->statut = $statut;
    }

    public function getIdCandidature() { return $this->id_candidature; }
    public function getIdCandidat() { return $this->id_candidat; }
    public function getIdOffre() { return $this->id_offre; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getDateCandidature() { return $this->date_candidature; }
    public function getReponsesQues() { return $this->reponses_ques; }
    public function getCvCand() { return $this->cv_cand; }
    public function getNote() { return $this->note; }
    public function getStatut() { return $this->statut; }

    public function setIdCandidat($id_candidat) { $this->id_candidat = $id_candidat; }
    public function setIdOffre($id_offre) { $this->id_offre = $id_offre; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setDateCandidature($date_candidature) { $this->date_candidature = $date_candidature; }
    public function setReponsesQues($reponses_ques) { $this->reponses_ques = $reponses_ques; }
    public function setCvCand($cv_cand) { $this->cv_cand = $cv_cand; }
    public function setNote($note) { $this->note = $note; }
    public function setStatut($statut) { $this->statut = $statut; }
}
?>