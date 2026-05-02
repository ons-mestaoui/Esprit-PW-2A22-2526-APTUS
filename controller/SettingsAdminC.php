<?php
class SettingsAdminC {
    private $settingsFile;
    private $defaultSettings = [
        // Général
        'site_name' => 'Aptus',
        'site_url' => 'https://aptus.tn',
        'site_desc' => "Plateforme intelligente de recrutement et d'apprentissage propulsée par l'intelligence artificielle.",
        'language' => 'Français',
        'timezone' => 'Africa/Tunis (GMT+1)',
        
        // Apparence
        'primary_color' => '#6B34A3',
        'accent_color' => '#00A3DA',
        'font_family' => 'Inter',
        'border_radius' => 'medium',
        'default_theme' => 'system',
        'sidebar_style' => 'default',
        'admin_logo' => '',
        
        // Plateforme
        'reg_candidat' => true,
        'reg_entreprise' => true,
        'mod_cv' => true,
        'mod_formations' => true,
        'mod_veille' => true,
        'mod_matching' => true,
        
        // Emails
        'smtp_server' => 'smtp.aptus.tn',
        'smtp_port' => 587,
        'smtp_email' => 'noreply@aptus.tn',
        'smtp_name' => 'Aptus Platform',
        'notif_new_user' => true,
        'notif_report' => true,
        'notif_weekly' => false,
        
        // Sécurité
        'min_pass_length' => 8,
        'session_expiry' => 120,
        'force_2fa' => false,
        'block_after_5' => true,
        
        // Maintenance
        'maintenance_mode' => false,
        'maintenance_msg' => 'Le site est en cours de maintenance. Nous serons de retour très bientôt !'
    ];

    // Boolean fields list for proper type casting
    private $booleanFields = [
        'reg_candidat', 'reg_entreprise', 'mod_cv', 'mod_formations',
        'mod_veille', 'mod_matching', 'notif_new_user', 'notif_report',
        'notif_weekly', 'force_2fa', 'block_after_5', 'maintenance_mode'
    ];

    // Integer fields
    private $integerFields = ['smtp_port', 'min_pass_length', 'session_expiry'];

    public function __construct() {
        $this->settingsFile = __DIR__ . '/../platform_settings.json';
    }

    /**
     * Get all settings. Returns defaults merged with stored values.
     * FIXED: No longer calls saveSettings() to avoid infinite recursion.
     */
    public function getSettings() {
        if (!file_exists($this->settingsFile)) {
            return $this->defaultSettings;
        }
        $json = file_get_contents($this->settingsFile);
        $data = json_decode($json, true);
        return $data ? array_merge($this->defaultSettings, $data) : $this->defaultSettings;
    }

    /**
     * Save settings. Merges with current settings and writes to JSON.
     * FIXED: Reads file directly instead of calling getSettings() to break recursion.
     */
    public function saveSettings($settings) {
        // Read current file directly (no recursion risk)
        $current = $this->defaultSettings;
        if (file_exists($this->settingsFile)) {
            $json = file_get_contents($this->settingsFile);
            $data = json_decode($json, true);
            if ($data) {
                $current = array_merge($current, $data);
            }
        }

        // Filter out non-setting keys (like 'action', 'type', etc.)
        $knownKeys = array_keys($this->defaultSettings);
        $filtered = [];
        foreach ($settings as $k => $v) {
            if (in_array($k, $knownKeys)) {
                $filtered[$k] = $v;
            }
        }

        $updated = array_merge($current, $filtered);

        // Cast booleans
        foreach ($this->booleanFields as $field) {
            if (isset($updated[$field])) {
                $updated[$field] = filter_var($updated[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Cast integers
        foreach ($this->integerFields as $field) {
            if (isset($updated[$field])) {
                $updated[$field] = intval($updated[$field]);
            }
        }

        file_put_contents($this->settingsFile, json_encode($updated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }

    /**
     * Handle logo upload
     */
    public function uploadLogo($file) {
        $uploadDir = __DIR__ . '/../view/assets/img/uploads/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $allowedTypes = ['image/png', 'image/jpeg', 'image/svg+xml', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            return ['success' => false, 'message' => 'Type de fichier non autorisé. Utilisez PNG, JPG, SVG ou WebP.'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'message' => 'Le fichier est trop volumineux. Maximum 2 Mo.'];
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'admin_logo_' . time() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $logoPath = '/aptus_first_official_version/view/assets/img/uploads/' . $filename;
            $this->saveSettings(['admin_logo' => $logoPath]);
            return ['success' => true, 'path' => $logoPath];
        }
        return ['success' => false, 'message' => 'Erreur lors du téléchargement.'];
    }

    /**
     * Remove custom logo
     */
    public function removeLogo() {
        $settings = $this->getSettings();
        if (!empty($settings['admin_logo'])) {
            $filePath = __DIR__ . '/..' . str_replace('/aptus_first_official_version', '', $settings['admin_logo']);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $this->saveSettings(['admin_logo' => '']);
        }
        return true;
    }

    public function clearCache() {
        // Simuler le vidage du cache
        return true;
    }

    public function backupDB() {
        // Simuler la sauvegarde de la base de données
        return true;
    }

    public function resetSettings() {
        file_put_contents($this->settingsFile, json_encode($this->defaultSettings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        return true;
    }

    /**
     * Get CSS custom properties generated from appearance settings.
     * Used by layouts to apply admin-configured theme.
     */
    public function getAppearanceCSS() {
        $s = $this->getSettings();
        $css = '';

        // Primary color override
        if (!empty($s['primary_color']) && $s['primary_color'] !== '#6B34A3') {
            $hex = $s['primary_color'];
            $css .= "  --accent-primary: {$hex};\n";
            $css .= "  --accent-primary-light: {$hex}1a;\n";
            $css .= "  --accent-primary-dark: {$hex};\n";
        }

        // Accent (secondary) color override
        if (!empty($s['accent_color']) && $s['accent_color'] !== '#00A3DA') {
            $hex = $s['accent_color'];
            $css .= "  --accent-secondary: {$hex};\n";
            $css .= "  --accent-secondary-light: {$hex}1a;\n";
        }

        // Font family override
        $fontMap = [
            'Inter' => "'Inter', sans-serif",
            'Roboto' => "'Roboto', sans-serif",
            'Outfit' => "'Outfit', sans-serif",
            'Poppins' => "'Poppins', sans-serif",
            'DM Sans' => "'DM Sans', sans-serif",
            'Plus Jakarta Sans' => "'Plus Jakarta Sans', sans-serif",
        ];
        if (!empty($s['font_family']) && isset($fontMap[$s['font_family']])) {
            $css .= "  --font-family: {$fontMap[$s['font_family']]};\n";
        }

        // Border radius override
        $radiusMap = [
            'none' => ['0px', '0px', '0px', '0px', '0px', '0px', '0px'],
            'small' => ['2px', '4px', '6px', '8px', '10px', '12px', '9999px'],
            'medium' => ['4px', '8px', '12px', '16px', '20px', '24px', '9999px'],
            'large' => ['6px', '12px', '16px', '20px', '24px', '28px', '9999px'],
            'full' => ['8px', '16px', '20px', '24px', '28px', '32px', '9999px'],
        ];
        if (!empty($s['border_radius']) && isset($radiusMap[$s['border_radius']])) {
            $r = $radiusMap[$s['border_radius']];
            $css .= "  --radius-xs: {$r[0]};\n";
            $css .= "  --radius-sm: {$r[1]};\n";
            $css .= "  --radius-md: {$r[2]};\n";
            $css .= "  --radius-lg: {$r[3]};\n";
            $css .= "  --radius-xl: {$r[4]};\n";
            $css .= "  --radius-2xl: {$r[5]};\n";
            $css .= "  --radius-full: {$r[6]};\n";
        }

        if (empty($css)) {
            return '';
        }

        return "<style id=\"admin-appearance-overrides\">\n:root {\n{$css}}\n</style>";
    }
}
?>
