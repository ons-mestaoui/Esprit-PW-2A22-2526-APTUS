<?php



class config
{
  private static $pdo = null;

  public static function getConnexion()
  {
    // Simple .env loader
    if (file_exists(__DIR__ . '/.env')) {
        $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) continue;
            $parts = explode('=', $line, 2);
            if (count($parts) === 2) {
                $_ENV[trim($parts[0])] = trim($parts[1]);
            }
        }
    }

    if (!isset(self::$pdo)) {
      try {
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $dbname = $_ENV['DB_NAME'] ?? 'aptus';
        $user = $_ENV['DB_USER'] ?? 'root';
        $pass = $_ENV['DB_PASS'] ?? '';

        self::$pdo = new PDO(
          "mysql:host=$host;dbname=$dbname",
          $user,
          $pass,
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
// Clé API gérée dans api_keys.php
