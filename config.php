<?php

/**
 * Classe Config : Gère la connexion à la base de données via PDO.
 * Applique le pattern Singleton pour assurer une seule instance de connexion.
 */
class config
{
  // Instance PDO statique pour être partagée dans toute l'application
  private static $pdo = null;

  /**
   * getConnexion() : Retourne l'instance de connexion active.
   * Crée la connexion si elle n'existe pas encore.
   */
  public static function getConnexion()
  {
    if (!isset(self::$pdo)) {
      try {
        self::$pdo = new PDO(
          'mysql:host=localhost;dbname=aptus',
          'root',
          '',
          [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
          ]
        );
      } catch (Exception $e) {
        die('Erreur: ' . $e->getMessage());
      }
    }
    return self::$pdo;
  }
}