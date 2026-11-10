<?php
declare(strict_types=1);

/*
 * Az adatbázis-kapcsolat egyetlen helye. Egy kérésen belül csak egyszer kapcsolódunk.
 */
final class Database
{
    private static ?PDO $kapcsolat = null;

    public static function kapcsolat(array $config): PDO
    {
        if (self::$kapcsolat === null) {
            $db = $config['db'];
            $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $db['host'], $db['port'], $db['name']);
            self::$kapcsolat = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                // Valódi előkészített lekérdezések: az adat és az SQL utasítás külön utazik,
                // ez véd az SQL injection ellen.
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$kapcsolat;
    }
}
