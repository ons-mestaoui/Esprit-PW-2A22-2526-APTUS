<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Template.php';

class TemplateC
{
    public function addTemplate(Template $template)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'INSERT INTO templates (nom, description, urlMiniature, structureHtml, estPremium, dateCreation) 
                VALUES (:nom, :description, :urlMiniature, :structureHtml, :estPremium, NOW())'
            );
            $query->execute([
                'nom' => $template->getNom(),
                'description' => $template->getDescription(),
                'urlMiniature' => $template->getUrlMiniature(),
                'structureHtml' => $template->getStructureHtml(),
                'estPremium' => $template->getEstPremium()
            ]);
        } catch (Exception $e) {
            die('Error adding template: ' . $e->getMessage());
        }
    }

    public function listeTemplates()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT * FROM templates ORDER BY id_template DESC');
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Error listing templates: ' . $e->getMessage());
        }
    }

    public function getTemplateById($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('SELECT * FROM templates WHERE id_template = :id');
            $query->execute(['id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Error getting template: ' . $e->getMessage());
        }
    }

    public function updateTemplate($id, Template $template)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE templates SET 
                    nom = :nom, 
                    description = :description, 
                    urlMiniature = :urlMiniature, 
                    structureHtml = :structureHtml, 
                    estPremium = :estPremium 
                WHERE id_template = :id'
            );
            $query->execute([
                'nom' => $template->getNom(),
                'description' => $template->getDescription(),
                'urlMiniature' => $template->getUrlMiniature(),
                'structureHtml' => $template->getStructureHtml(),
                'estPremium' => $template->getEstPremium(),
                'id' => $id
            ]);
        } catch (Exception $e) {
            die('Error updating template: ' . $e->getMessage());
        }
    }

    public function deleteTemplate($id)
    {
        $db = config::getConnexion();
        try {
            $query = $db->prepare('DELETE FROM templates WHERE id_template = :id');
            $query->execute(['id' => $id]);
        } catch (Exception $e) {
            die('Error deleting template: ' . $e->getMessage());
        }
    }

    public function getTotalTemplates()
    {
        $db = config::getConnexion();
        try {
            $query = $db->query('SELECT COUNT(*) as total FROM templates');
            $result = $query->fetch();
            return $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }
}
