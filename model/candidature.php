<?php
class candidature{
    private $id_candidature;
    private $id_candidat;
    private $id_offre;
    private $date_candidature;
    private $reponses_ques;
    private $cv_cand;
    private $note;
    private $statut;


    public function __construct($date_candidature, $reponses_ques, $cv_cand, $note, $statut) {
        $this->date_candidature = $date_candidature;
        $this->reponses_ques = $reponses_ques;
        $this->cv_cand = $cv_cand;
        $this->note = $note;
        $this->statut = $statut;
    }

    public function getDateCandidature() {
        return $this->date_candidature;
    }

    public function getReponsesQues() {
        return $this->reponses_ques;
    }

    public function getCvCand() {
        return $this->cv_cand;
    }

    public function getNote() {
        return $this->note;
    }

    public function getStatut() {
        return $this->statut;
    }

    public function setDateCandidature($date_candidature) {
        $this->date_candidature = $date_candidature;
    }

    public function setReponsesQues($reponses_ques) {
        $this->reponses_ques = $reponses_ques;
    }

    public function setCvCand($cv_cand) {
        $this->cv_cand = $cv_cand;
    }

    public function setNote($note) {
        $this->note = $note;
    }

    public function setStatut($statut) {
        $this->statut = $statut;
    }

}
    