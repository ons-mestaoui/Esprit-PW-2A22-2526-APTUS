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

    public static function desinscrire(int $idUser, int $idFormation)
    {
        $db = config::getConnexion();
        
        // Contraintes PHP : Bloquer si date passée ou commencée
        $stmtF = $db->prepare("SELECT date_formation FROM Formation WHERE id_formation = ?");
        $stmtF->execute([$idFormation]);
        $date_f = $stmtF->fetchColumn();

        if ($date_f && strtotime($date_f) <= strtotime(date('Y-m-d'))) {
            throw new Exception("Impossible de se désinscrire : la formation a déjà commencé ou est passée.");
        }

        // Contrainte PHP : Bloquer si le statut de l'inscription est 'Terminée'
        $stmtI = null;
        try {
            $stmtI = $db->prepare("SELECT statut FROM inscription WHERE id_formation = ? AND id_user = ?");
            $stmtI->execute([$idFormation, $idUser]);
        } catch (Exception $e) {
            $stmtI = $db->prepare("SELECT statut FROM Inscription WHERE id_formation = ? AND id_user = ?");
            $stmtI->execute([$idFormation, $idUser]);
        }
        
        $statut_actuel = $stmtI->fetchColumn();
        if ($statut_actuel === 'Terminée') {
            throw new Exception("Impossible de se désinscrire d'une formation déjà terminée.");
        }

        try {
            $delete = $db->prepare("DELETE FROM inscription WHERE id_formation = ? AND id_user = ?");
            $delete->execute([$idFormation, $idUser]);
            if ($delete->rowCount() == 0) {
                $delete = $db->prepare("DELETE FROM Inscription WHERE id_formation = ? AND id_user = ?");
                $delete->execute([$idFormation, $idUser]);
            }
        } catch (Exception $e) {
            throw new Exception("Erreur système lors de la désinscription.");
        }
    }

    public static function annulerParAdmin(int $idInscription)
    {
        $db = config::getConnexion();
        try {
            $update = $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_inscri = ?");
            $update->execute([$idInscription]);
        } catch(Exception $e) {
            $update = $db->prepare("UPDATE Inscription SET statut = 'annulée' WHERE id_inscri = ?");
            $update->execute([$idInscription]);
        }
    }

    public static function updateStatutAdmin(int $idInscription, string $statut)
    {
        $statuts_autorises = ['En attente', 'En cours', 'Terminée', 'annulée', 'shortlisté', 'refusé'];
        if (!in_array($statut, $statuts_autorises)) {
            throw new Exception("Statut invalide.");
        }

        $db = config::getConnexion();
        try {
            $update = $db->prepare("UPDATE inscription SET statut = ? WHERE id_inscri = ?");
            $update->execute([$statut, $idInscription]);
        } catch(Exception $e) {
            $update = $db->prepare("UPDATE Inscription SET statut = ? WHERE id_inscri = ?");
            $update->execute([$statut, $idInscription]);
        }
    }
}