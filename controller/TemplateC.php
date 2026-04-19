<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../model/Template.php';

class TemplateC
{
    private function getTemplateDir()
    {
        $dir = __DIR__ . '/../view/assets/templates/html';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        return $dir;
    }

    private function processHtmlForStorage($rawHtml, $oldReference = null)
    {
        $dir = $this->getTemplateDir();
        $filename = '';
        
        if ($oldReference && strpos($oldReference, '[FILE]:') === 0) {
            $filename = str_replace('[FILE]:', '', $oldReference);
        } else {
            $filename = 'tpl_' . uniqid() . '.html';
        }
        
        $path = $dir . '/' . $filename;
        file_put_contents($path, $rawHtml);
        
        return '[FILE]:' . $filename;
    }

    private function processHtmlForOutput($storedData)
    {
        if ($storedData && isset($storedData['structureHtml']) && strpos($storedData['structureHtml'], '[FILE]:') === 0) {
            $filename = str_replace('[FILE]:', '', $storedData['structureHtml']);
            $path = $this->getTemplateDir() . '/' . $filename;
            if (file_exists($path)) {
                $storedData['structureHtml'] = file_get_contents($path);
            } else {
                $storedData['structureHtml'] = '<!-- Error: Fichier template introuvable ('.$filename.') -->';
            }
        }
        return $storedData;
    }

    public function addTemplate(Template $template)
    {
        $db = config::getConnexion();
        try {
            $fileReference = $this->processHtmlForStorage($template->getStructureHtml());

            $query = $db->prepare(
                'INSERT INTO templates (nom, description, urlMiniature, structureHtml, estPremium, dateCreation) 
                VALUES (:nom, :description, :urlMiniature, :structureHtml, :estPremium, NOW())'
            );
            $query->execute([
                'nom' => $template->getNom(),
                'description' => $template->getDescription(),
                'urlMiniature' => $template->getUrlMiniature(),
                'structureHtml' => $fileReference,
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
            $results = $query->fetchAll();
            foreach ($results as &$row) {
                $row = $this->processHtmlForOutput($row);
            }
            return $results;
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
            $row = $query->fetch();
            if ($row) {
                $row = $this->processHtmlForOutput($row);
            }
            return $row;
        } catch (Exception $e) {
            die('Error getting template: ' . $e->getMessage());
        }
    }

    public function updateTemplate($id, Template $template)
    {
        $db = config::getConnexion();
        try {
            $qOld = $db->prepare('SELECT structureHtml FROM templates WHERE id_template = :id');
            $qOld->execute(['id' => $id]);
            $oldTpl = $qOld->fetch();
            
            $fileReference = $this->processHtmlForStorage($template->getStructureHtml(), $oldTpl ? $oldTpl['structureHtml'] : null);

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
                'structureHtml' => $fileReference,
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
            $qOld = $db->prepare('SELECT structureHtml FROM templates WHERE id_template = :id');
            $qOld->execute(['id' => $id]);
            $oldTpl = $qOld->fetch();
            
            if ($oldTpl && strpos($oldTpl['structureHtml'], '[FILE]:') === 0) {
                $filename = str_replace('[FILE]:', '', $oldTpl['structureHtml']);
                $path = $this->getTemplateDir() . '/' . $filename;
                if (file_exists($path)) {
                    unlink($path);
                }
            }

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
