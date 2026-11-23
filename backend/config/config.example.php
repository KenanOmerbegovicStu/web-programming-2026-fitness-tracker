<?php
// Copy this file to config.php and put your real values there. config.php is never committed.
class Config
{
    public static function DB_HOST()     { return self::get_env('DB_HOST', '127.0.0.1'); }
    public static function DB_PORT()     { return self::get_env('DB_PORT', 3306); }
    public static function DB_NAME()     { return self::get_env('DB_NAME', 'fitness_tracker'); }
    public static function DB_USER()     { return self::get_env('DB_USER', 'root'); }
    public static function DB_PASSWORD() { return self::get_env('DB_PASSWORD', ''); }
    public static function JWT_SECRET()  { return self::get_env('JWT_SECRET', 'change-me'); }


    public static function WGER_BASE_URL() { return self::get_env('WGER_BASE_URL', 'https://wger.de/api/v2'); }
    public static function WGER_TIMEOUT()  { return (int) self::get_env('WGER_TIMEOUT', 5); }
    public static function WGER_API_KEY()  { return self::get_env('WGER_API_KEY', ''); }

    // Environment variables (set on the server) win over the default values above.
    public static function get_env($name, $default)
    {
        $value = getenv($name);
        return ($value !== false && trim($value) !== '') ? $value : $default;
    }
}
