<?php
declare(strict_types=1);

/*
 * Minden belépési pont (oldal vagy API) ezzel kezdődik.
 * Betölti a beállításokat és az osztályokat, és biztonságosan elindítja a munkamenetet.
 */

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Hiányzik a config/config.php fájl. Másold le a config.example.php fájlt config.php néven.');
}
$config = require $configFile;

// Egyszerű betöltő: az osztály neve megegyezik a src mappában lévő fájl nevével.
spl_autoload_register(static function (string $osztaly): void {
    $fajl = __DIR__ . '/' . basename(str_replace('\\', '/', $osztaly)) . '.php';
    if (is_file($fajl)) {
        require $fajl;
    }
});

// A munkamenet sütije JavaScriptből nem olvasható, és más oldalról indított kérésekkel nem küldődik el.
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_httponly' => true,
        'cookie_samesite' => 'Lax',
        'use_strict_mode' => true,
    ]);
}
