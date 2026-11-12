<?php
/*
 * Minta beállítások.
 * Használat: másold le ugyanebbe a mappába config.php néven, és írd át
 * a saját adataidra. A config.php nem kerül fel a GitHubra (.gitignore).
 * Az adatbázis értékei az XAMPP alapértelmezett beállításai.
 */
return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'kamatbonusz',
        'user'     => 'root',
        'password' => '',
    ],

    // E-mail küldés Gmailen keresztül. A jelszó a Google fiók alkalmazásjelszava
    // (16 karakter), nem a fiók saját jelszava.
    'mail' => [
        'host'       => 'smtp.gmail.com',
        'port'       => 587,
        'titkositas' => 'tls',
        'felhasznalo' => 'kuldo.fiok@gmail.com',
        'jelszo'     => 'ALKALMAZASJELSZO',
        'felado_nev' => 'kamatbonusz.hu',
        // Ide érkeznek az érdeklődésekről szóló értesítések (a megrendelő címe).
        'cimzett'    => 'megrendelo@gmail.com',
    ],

    // true: fejlesztés közben a hibák részletei a naplóba kerülnek.
    // Élesben is false-on marad a megjelenítés, a látogató soha nem lát technikai részletet.
    'debug' => false,
];
