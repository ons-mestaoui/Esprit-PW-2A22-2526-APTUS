<?php

class EnvLoader
{
    public static function load($path)
    {
        if (!file_exists($path)) {
            // Fallback: try to find it if we are in a subdirectory
            $path = __DIR__ . '/../.env';
            if (!file_exists($path)) return false;
        }

        $content = file_get_contents($path);
        
        // Strip UTF-8 BOM if present
        if (strpos($content, "\xEF\xBB\xBF") === 0) {
            $content = substr($content, 3);
        }

        // Split by any newline character
        $lines = preg_split("/\r\n|\n|\r/", $content);

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) continue;

            if (strpos($line, '=') !== false) {
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                // Remove quotes
                $value = trim($value, "\"' \t\n\r\0\x0B");

                if (!empty($name)) {
                    putenv("$name=$value");
                    $_ENV[$name] = $value;
                    $_SERVER[$name] = $value;
                }
            }
        }
        return true;
    }
}
