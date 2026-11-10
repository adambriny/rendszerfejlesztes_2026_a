<?php
/*
 * Minta beállítások.
 * Használat: másold le ugyanebbe a mappába config.php néven, és írd át
 * a saját adataidra. A config.php nem kerül fel a GitHubra (.gitignore).
 * Az alábbi értékek az XAMPP alapértelmezett beállításai.
 */
return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'kamatbonusz',
        'user'     => 'root',
        'password' => '',
    ],
    // true: fejlesztés közben a hibák részletei a naplóba kerülnek.
    // Élesben is false-on marad a megjelenítés, a látogató soha nem lát technikai részletet.
    'debug' => false,
];
