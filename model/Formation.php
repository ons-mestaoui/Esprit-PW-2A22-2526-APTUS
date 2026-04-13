<?php
class Formation
{
    private ?int $id_formation;
    private string $titre;
    private string $description;
    private string $domaine;
    private string $niveau;
    private string $duree;
    private string $date_formation;
    private string $image_base64;
    private ?int $id_tuteur;
    private int $is_online;
    private string $lien_api_room;

    public function __construct(
        string $titre,
        string $description,
        string $domaine,
        string $niveau,
        string $duree,
        string $date_formation,
        string $image_base64,
        ?int $id_tuteur,
        int $is_online,
        string $lien_api_room
    ) {
        $this->titre = $titre;
        $this->description = $description;
        $this->domaine = $domaine;
        $this->niveau = $niveau;
        $this->duree = $duree;
        $this->date_formation = $date_formation;
        $this->image_base64 = $image_base64;
        $this->id_tuteur = $id_tuteur;
        $this->is_online = $is_online;
        $this->lien_api_room = $lien_api_room;
    }

    // Getters
    public function getIdFormation()
    {
        return $this->id_formation;
    }
    public function getTitre()
    {
        return $this->titre;
    }
    public function getDescription()
    {
        return $this->description;
    }
    public function getDomaine()
    {
        return $this->domaine;
    }
    public function getNiveau()
    {
        return $this->niveau;
    }
    public function getDuree()
    {
        return $this->duree;
    }
    public function getDateFormation()
    {
        return $this->date_formation;
    }
    public function getImageBase64()
    {
        return $this->image_base64;
    }
    public function getIdTuteur()
    {
        return $this->id_tuteur;
    }
    public function getIsOnline()
    {
        return $this->is_online;
    }
    public function getLienApiRoom()
    {
        return $this->lien_api_room;
    }

    // Setters (Exemples)
    public function setTitre(string $titre)
    {
        $this->titre = $titre;
    }
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    // ... tu peux ajouter les autres setters si nécessaire

    public static function annulerFormation(int $id)
    {
        $db = config::getConnexion();
        try {
            $db->beginTransaction();

            $stmtF = $db->prepare("UPDATE Formation SET statut = 'annulée' WHERE id_formation = ?");
            if (!$stmtF->execute([$id])) {
                throw new Exception("Erreur lors de l'annulation de la formation dans la db.");
            }

            try {
                $stmtI = $db->prepare("UPDATE inscription SET statut = 'annulée' WHERE id_formation = ?");
                $stmtI->execute([$id]);
            } catch (Exception $e) {
                $stmtI = $db->prepare("UPDATE Inscription SET statut = 'annulée' WHERE id_formation = ?");
                $stmtI->execute([$id]);
            }

            $db->commit();
            return true;
        } catch (Exception $e) {
            $db->rollBack();
            throw new Exception("Erreur lors de la transaction d'annulation : " . $e->getMessage());
        }
    }
}