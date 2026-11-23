<?php
class Logger
{
    public static function info($message)
    {
        self::write('info.log', 'INFO', $message);
    }

    public static function error($message)
    {
        self::write('error.log', 'ERROR', $message);
    }

    private static function write($file, $level, $message)
    {
        $line = '[' . date('Y-m-d H:i:s') . "] $level $message" . PHP_EOL;
        // LOCK_EX: two requests at the same moment cannot mix their lines
        @file_put_contents(__DIR__ . '/../logs/' . $file, $line, FILE_APPEND | LOCK_EX);
    }
}
