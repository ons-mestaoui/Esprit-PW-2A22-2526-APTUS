<?php



class config

{

  private static $pdo = null;



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

// Configuration pour Aptus AI integration
define('GEMINI_API_KEY', 'AIzaSyCk0VfvESPggLbCk9bV8bZimzCIbWEk3Pk');