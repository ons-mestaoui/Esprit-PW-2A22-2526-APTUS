<?php
/**
 * Controller TwoFactorC : Handles TOTP (Time-based One-Time Password) logic
 * for Two-Factor Authentication.
 */
class TwoFactorC {
    private static $base32chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generates a random 16-character base32 secret.
     */
    public static function generateSecret($length = 16) {
        $secret = '';
        try {
            $randomBytes = random_bytes($length);
            for ($i = 0; $i < $length; $i++) {
                $secret .= self::$base32chars[ord($randomBytes[$i]) & 31];
            }
        } catch (Exception $e) {
            // Fallback for environments without random_bytes
            for ($i = 0; $i < $length; $i++) {
                $secret .= self::$base32chars[rand(0, 31)];
            }
        }
        return $secret;
    }

    /**
     * Calculates the TOTP code for a given secret and time slice.
     */
    public static function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretKey = self::base32Decode($secret);
        // Pack time into 8-byte binary string
        $time = pack('N', 0) . pack('N', $timeSlice);
        $hmac = hash_hmac('SHA1', $time, $secretKey, true);
        $offset = ord(substr($hmac, -1)) & 0x0F;
        $hash = unpack('N', substr($hmac, $offset, 4));
        $value = $hash[1] & 0x7FFFFFFF;
        $modulo = pow(10, 6);
        return str_pad($value % $modulo, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Verifies a 6-digit TOTP code.
     * $discrepancy allows for clock drift (1 = +/- 30 seconds).
     */
    public static function verifyCode($secret, $code, $discrepancy = 1) {
        $currentTimeSlice = floor(time() / 30);
        for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
            if (self::getCode($secret, $currentTimeSlice + $i) == $code) {
                return true;
            }
        }
        return false;
    }

    /**
     * Decodes a base32 string into binary.
     */
    private static function base32Decode($base32) {
        if (empty($base32)) return '';
        $base32 = strtoupper($base32);
        $base32lookup = array_flip(str_split(self::$base32chars));
        $out = '';
        $buffer = 0;
        $bufferSize = 0;
        foreach (str_split($base32) as $char) {
            if (!isset($base32lookup[$char])) continue;
            $buffer = ($buffer << 5) | $base32lookup[$char];
            $bufferSize += 5;
            if ($bufferSize >= 8) {
                $bufferSize -= 8;
                $out .= chr(($buffer >> $bufferSize) & 0xFF);
            }
        }
        return $out;
    }

    /**
     * Generates an otpauth:// URL for QR code generation.
     */
    public static function getOtpAuthUrl($label, $secret, $issuer = 'Aptus') {
        return 'otpauth://totp/' . rawurlencode($issuer) . ':' . rawurlencode($label) . '?secret=' . $secret . '&issuer=' . rawurlencode($issuer);
    }
}
?>
