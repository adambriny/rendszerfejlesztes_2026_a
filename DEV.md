# Fejlesztői útmutató

## Előfeltételek

Az alkalmazás Windows, macOS és Linux rendszeren is fut. Telepítsd:

* **XAMPP** (PHP 8.0 vagy újabb, MariaDB, Apache): [apachefriends.org](https://www.apachefriends.org/download.html)
* **Git**: [git-scm.com/downloads](https://git-scm.com/downloads)

Ellenőrzés (Windows parancssor):

```bat
C:\\xampp\\php\\php.exe -v
C:\\xampp\\mysql\\bin\\mysql.exe --version
git --version
```

## Helyi telepítés

Töltsd le a kódot az XAMPP webes mappájába, majd az XAMPP Control Panelben indítsd el az **Apache**-ot és a **MySQL**-t.

```bat
cd C:\\xampp\\htdocs
git clone https://github.com/adambriny/rendszerfejlesztes\_2026\_a.git kamatbonusz
cd kamatbonusz
```

### Adatbázis

Hozd létre az adatbázist, és töltsd be a táblákat és a tarifákat:

```bat
C:\\xampp\\mysql\\bin\\mysql.exe -u root -e "CREATE DATABASE kamatbonusz CHARACTER SET utf8mb4 COLLATE utf8mb4\_hungarian\_ci"
C:\\xampp\\mysql\\bin\\mysql.exe -u root --default-character-set=utf8mb4 kamatbonusz < database\\kamatbonusz.sql
```

* `database/kamatbonusz.sql` létrehozza a négy táblát, és feltölti a tarifákat (Erste: valós, OTP és Fundamenta: minta adatok).
* A fájl újrafuttatható, semmit nem töröl és nem ír felül.

Ugyanez grafikusan: `http://localhost/phpmyadmin`, új `kamatbonusz` adatbázis `utf8mb4\_hungarian\_ci` illesztéssel, majd **Importálás**.

### Beállítások

```bat
copy config\\config.example.php config\\config.php
```

Az adatbázis az XAMPP alapbeállításaival (`root`, jelszó nélkül) azonnal működik. Az e-mail értesítéshez a `'mail'` részbe egy Gmail fiók és annak alkalmazásjelszava kell ([myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords)). A `config.php` soha nem kerül a repositoryba.

## Futtatás

Az alkalmazás elérhető:

* **Weboldal:** http://localhost/kamatbonusz/
* **Kalkulátor:** http://localhost/kamatbonusz/public/kalkulator.php
* **Ajánlat API:** http://localhost/kamatbonusz/public/api/ajanlatok.php?havi=20000\&sav=3

Ellenőrzés: havi 20 000 Ft, "10 év vagy fölötte" esetén az 1. helyezett lejáratkor 3 183 658 Ft.

## Hasznos tudnivalók

* Beérkezett érdeklődések: phpMyAdmin, `kamatbonusz` adatbázis, `leads` tábla.
* Hibanapló: `C:\\xampp\\apache\\logs\\error.log`
* "Átmeneti hiba" az oldalon: nem fut a MySQL, vagy hiányzik az adatbázis.

