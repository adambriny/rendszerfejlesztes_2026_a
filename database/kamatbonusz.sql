-- =====================================================================
-- kamatbonusz.hu adatbázis
-- Létrehozza a négy táblát, és feltölti az intézményeket és a tarifákat.
--
-- Használat: phpMyAdminban hozz létre egy üres adatbázist
-- (utf8mb4_hungarian_ci illesztéssel), jelöld ki, és importáld ezt a fájlt.
--
-- A fájl biztonságosan újrafuttatható: semmit nem töröl és semmit nem
-- ír felül. Ami már létezik, azt kihagyja, ami hiányzik, azt pótolja.
-- =====================================================================

SET NAMES utf8mb4;

-- ---------------------------------------------------------------------
-- Intézmények. A név csak az admin felületen és a megrendelőnek küldött
-- e-mailben jelenik meg, a nyilvános kalkulátor felé soha.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS intezmenyek (
  id  INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nev VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_intezmenyek_nev (nev)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ---------------------------------------------------------------------
-- Tarifák. Egy sor = egy módozat egy adott havi összegre.
-- Az EBKM a tarifatáblában közölt hivatalos érték, mert az a díjakat
-- (számlanyitás, számlavezetés) is tartalmazza, így nem számolható ki
-- pontosan a többi oszlopból.
-- A lejáratkor felvehető összeget nem tároljuk:
--   osszes_befizetes + kamatbonusz + beteti_kamat
-- Ha egy intézményt törölnek, a tarifái is törlődnek (CASCADE).
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tarifak (
  id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
  intezmeny_id      INT UNSIGNED NOT NULL,
  modozat           VARCHAR(100) NOT NULL,
  havi_megtakaritas INT UNSIGNED NOT NULL,          -- Ft
  befizetesek_szama SMALLINT UNSIGNED NOT NULL,     -- hónap
  osszes_befizetes  INT UNSIGNED NOT NULL,          -- Ft
  kamatbonusz       INT UNSIGNED NOT NULL DEFAULT 0, -- Ft
  beteti_kamat      INT UNSIGNED NOT NULL DEFAULT 0, -- Ft
  ebkm              DECIMAL(5,2) NOT NULL,          -- %
  PRIMARY KEY (id),
  -- Ugyanaz a módozat ugyanarra az összegre nem szerepelhet kétszer.
  UNIQUE KEY uq_tarifak_sor (intezmeny_id, modozat, havi_megtakaritas),
  -- A kalkulátor ezek alapján keres, ezért indexeljük.
  KEY idx_tarifak_kereses (havi_megtakaritas, befizetesek_szama),
  CONSTRAINT fk_tarifak_intezmeny
    FOREIGN KEY (intezmeny_id) REFERENCES intezmenyek (id)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT chk_tarifak_ertekek
    CHECK (havi_megtakaritas > 0 AND befizetesek_szama > 0 AND osszes_befizetes > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ---------------------------------------------------------------------
-- Érdeklődők (leadek). A választott ajánlat adatait a beküldés
-- pillanatában elmentjük, mert a tarifák később változhatnak.
-- Amíg egy intézményhez érdeklődő tartozik, az intézmény nem törölhető
-- (RESTRICT), így beérkezett érdeklődés nem veszhet el véletlenül.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leads (
  id                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
  nev                VARCHAR(100) NOT NULL,
  email              VARCHAR(254) NOT NULL,
  telefonszam        VARCHAR(30)  NOT NULL,
  intezmeny_id       INT UNSIGNED NOT NULL,
  modozat            VARCHAR(100) NOT NULL,
  havi_megtakaritas  INT UNSIGNED NOT NULL,
  befizetesek_szama  SMALLINT UNSIGNED NOT NULL,
  osszes_befizetes   INT UNSIGNED NOT NULL,
  lejaratkori_osszeg INT UNSIGNED NOT NULL,
  ebkm               DECIMAL(5,2) NOT NULL,       -- %
  jelenlegi_bank     VARCHAR(50)  NOT NULL,       -- csak tájékoztató adat
  allapot            ENUM('uj','lezart') NOT NULL DEFAULT 'uj',
  beerkezes_datuma   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_leads_datum (beerkezes_datuma),
  CONSTRAINT fk_leads_intezmeny
    FOREIGN KEY (intezmeny_id) REFERENCES intezmenyek (id)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- ---------------------------------------------------------------------
-- Admin felhasználók. A jelszó csak hash-elve kerül ide (PHP password_hash).
-- A teszt admin fiókot az admin bejelentkezés feladatnál adjuk hozzá.
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS admin_felhasznalok (
  id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
  felhasznalonev VARCHAR(50)  NOT NULL,
  jelszo_hash    VARCHAR(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_admin_felhasznalonev (felhasznalonev)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_hungarian_ci;

-- =====================================================================
-- Adatok. INSERT IGNORE: ha a sor már létezik, kihagyja, így egy
-- újrafuttatás nem írja felül az admin felületen módosított tarifákat.
-- =====================================================================

INSERT IGNORE INTO intezmenyek (id, nev) VALUES
  (1, 'Erste'),
  (2, 'OTP'),
  (3, 'Fundamenta');

-- Erste: valós adatok, forrás: Erste EgyOtthon kamatbónusz számítás,
-- érvényes 2026. június 1-től. A betéti kamat az "Összes saját befizetés
-- és kamata" oszlop és a befizetések különbsége, a kamatbónusz a
-- "Kamatbónusz és kamata" oszlop, az EBKM a táblában közölt érték.
INSERT IGNORE INTO tarifak (intezmeny_id, modozat, havi_megtakaritas, befizetesek_szama, osszes_befizetes, kamatbonusz, beteti_kamat, ebkm) VALUES
  (1, 'LTP 15% 5 év', 10000, 60, 600000, 90200, 2116, 3.19),
  (1, 'LTP 15% 5 év', 20000, 60, 1200000, 180375, 4220, 3.90),
  (1, 'LTP 15% 5 év', 30000, 60, 1800000, 270575, 6348, 4.14),
  (1, 'LTP 15% 5 év', 40000, 60, 2400000, 360750, 8452, 4.26),
  (1, 'LTP 15% 5 év', 50000, 60, 3000000, 450950, 10568, 4.34),
  (1, 'LTP 15% 5 év', 60000, 60, 3600000, 541125, 12662, 4.39),
  (1, 'LTP 15% 5 év', 70000, 60, 4200000, 631325, 14792, 4.42),
  (1, 'LTP 15% 5 év', 80000, 60, 4800000, 721500, 16902, 4.45),
  (1, 'LTP 15% 5 év', 90000, 60, 5400000, 811700, 19022, 4.47),
  (1, 'LTP 15% 5 év', 100000, 60, 6000000, 901875, 21126, 4.48),
  (1, 'LTP 30% 8 év', 10000, 96, 960000, 289032, 3972, 5.07),
  (1, 'LTP 30% 8 év', 20000, 96, 1920000, 578064, 7934, 5.52),
  (1, 'LTP 30% 8 év', 30000, 96, 2880000, 867096, 11918, 5.68),
  (1, 'LTP 30% 8 év', 40000, 96, 3840000, 1156128, 15874, 5.75),
  (1, 'LTP 30% 8 év', 50000, 96, 4800000, 1445160, 19848, 5.80),
  (1, 'LTP 30% 8 év', 60000, 96, 5760000, 1734192, 23800, 5.83),
  (1, 'LTP 30% 8 év', 70000, 96, 6720000, 2023226, 27784, 5.85),
  (1, 'LTP 30% 8 év', 80000, 96, 7680000, 2312258, 31750, 5.87),
  (1, 'LTP 30% 8 év', 90000, 96, 8640000, 2601302, 35730, 5.88),
  (1, 'LTP 30% 8 év', 100000, 96, 9600000, 2890334, 39686, 6.19),
  (1, 'LTP 30%+10% Rugalmas 10 év', 10000, 120, 1200000, 385664, 6170, 4.61),
  (1, 'LTP 30%+10% Rugalmas 10 év', 20000, 120, 2400000, 771328, 12330, 4.97),
  (1, 'LTP 30%+10% Rugalmas 10 év', 30000, 120, 3600000, 1156992, 18512, 5.10),
  (1, 'LTP 30%+10% Rugalmas 10 év', 40000, 120, 4800000, 1542658, 24672, 5.16),
  (1, 'LTP 30%+10% Rugalmas 10 év', 50000, 120, 6000000, 1928334, 30842, 5.20),
  (1, 'LTP 30%+10% Rugalmas 10 év', 60000, 120, 7200000, 2313998, 36990, 5.22),
  (1, 'LTP 30%+10% Rugalmas 10 év', 70000, 120, 8400000, 2699674, 43176, 5.24),
  (1, 'LTP 30%+10% Rugalmas 10 év', 80000, 120, 9600000, 3085338, 49344, 5.25),
  (1, 'LTP 30%+10% Rugalmas 10 év', 90000, 120, 10800000, 3471014, 55520, 5.26),
  (1, 'LTP 30%+10% Rugalmas 10 év', 100000, 120, 12000000, 3856678, 61678, 5.27);

-- OTP: FIKTÍV minta adatok a prototípushoz. Éles indítás előtt az admin
-- felületen valós tarifára cserélendő.
INSERT IGNORE INTO tarifak (intezmeny_id, modozat, havi_megtakaritas, befizetesek_szama, osszes_befizetes, kamatbonusz, beteti_kamat, ebkm) VALUES
  (2, 'Minta Lakásszámla 4 év', 10000, 48, 480000, 40800, 980, 3.86),
  (2, 'Minta Lakásszámla 4 év', 20000, 48, 960000, 81600, 1960, 3.86),
  (2, 'Minta Lakásszámla 4 év', 30000, 48, 1440000, 122400, 2940, 3.86),
  (2, 'Minta Lakásszámla 4 év', 40000, 48, 1920000, 163200, 3920, 3.86),
  (2, 'Minta Lakásszámla 4 év', 50000, 48, 2400000, 204000, 4900, 3.86),
  (2, 'Minta Lakásszámla 4 év', 60000, 48, 2880000, 244800, 5880, 3.86),
  (2, 'Minta Lakásszámla 4 év', 70000, 48, 3360000, 285600, 6860, 3.86),
  (2, 'Minta Lakásszámla 4 év', 80000, 48, 3840000, 326400, 7840, 3.86),
  (2, 'Minta Lakásszámla 4 év', 90000, 48, 4320000, 367200, 8820, 3.86),
  (2, 'Minta Lakásszámla 4 év', 100000, 48, 4800000, 408000, 9800, 3.86),
  (2, 'Minta Lakásszámla 7 év', 10000, 84, 840000, 210000, 2975, 5.03),
  (2, 'Minta Lakásszámla 7 év', 20000, 84, 1680000, 420000, 5950, 5.38),
  (2, 'Minta Lakásszámla 7 év', 30000, 84, 2520000, 630000, 8925, 5.49),
  (2, 'Minta Lakásszámla 7 év', 40000, 84, 3360000, 840000, 11900, 5.55),
  (2, 'Minta Lakásszámla 7 év', 50000, 84, 4200000, 1050000, 14875, 5.58),
  (2, 'Minta Lakásszámla 7 év', 60000, 84, 5040000, 1260000, 17850, 5.61),
  (2, 'Minta Lakásszámla 7 év', 70000, 84, 5880000, 1470000, 20825, 5.62),
  (2, 'Minta Lakásszámla 7 év', 80000, 84, 6720000, 1680000, 23800, 5.64),
  (2, 'Minta Lakásszámla 7 év', 90000, 84, 7560000, 1890000, 26775, 5.65),
  (2, 'Minta Lakásszámla 7 év', 100000, 84, 8400000, 2100000, 29750, 5.65),
  (2, 'Minta Lakásszámla 12 év', 10000, 144, 1440000, 518400, 8700, 4.36),
  (2, 'Minta Lakásszámla 12 év', 20000, 144, 2880000, 1036800, 17400, 4.54),
  (2, 'Minta Lakásszámla 12 év', 30000, 144, 4320000, 1555200, 26100, 4.60),
  (2, 'Minta Lakásszámla 12 év', 40000, 144, 5760000, 2073600, 34800, 4.63),
  (2, 'Minta Lakásszámla 12 év', 50000, 144, 7200000, 2592000, 43500, 4.65),
  (2, 'Minta Lakásszámla 12 év', 60000, 144, 8640000, 3110400, 52200, 4.66),
  (2, 'Minta Lakásszámla 12 év', 70000, 144, 10080000, 3628800, 60900, 4.67),
  (2, 'Minta Lakásszámla 12 év', 80000, 144, 11520000, 4147200, 69600, 4.67),
  (2, 'Minta Lakásszámla 12 év', 90000, 144, 12960000, 4665600, 78300, 4.68),
  (2, 'Minta Lakásszámla 12 év', 100000, 144, 14400000, 5184000, 87000, 4.68);

-- Fundamenta: FIKTÍV minta adatok a prototípushoz. Éles indítás előtt az
-- admin felületen valós tarifára cserélendő.
INSERT IGNORE INTO tarifak (intezmeny_id, modozat, havi_megtakaritas, befizetesek_szama, osszes_befizetes, kamatbonusz, beteti_kamat, ebkm) VALUES
  (3, 'Minta Otthonszámla 5 év', 10000, 60, 600000, 78000, 1525, 3.35),
  (3, 'Minta Otthonszámla 5 év', 20000, 60, 1200000, 156000, 3050, 3.89),
  (3, 'Minta Otthonszámla 5 év', 30000, 60, 1800000, 234000, 4575, 4.07),
  (3, 'Minta Otthonszámla 5 év', 40000, 60, 2400000, 312000, 6100, 4.16),
  (3, 'Minta Otthonszámla 5 év', 50000, 60, 3000000, 390000, 7625, 4.21),
  (3, 'Minta Otthonszámla 5 év', 60000, 60, 3600000, 468000, 9150, 4.25),
  (3, 'Minta Otthonszámla 5 év', 70000, 60, 4200000, 546000, 10675, 4.27),
  (3, 'Minta Otthonszámla 5 év', 80000, 60, 4800000, 624000, 12200, 4.29),
  (3, 'Minta Otthonszámla 5 év', 90000, 60, 5400000, 702000, 13725, 4.31),
  (3, 'Minta Otthonszámla 5 év', 100000, 60, 6000000, 780000, 15250, 4.32),
  (3, 'Minta Otthonszámla 9 év', 10000, 108, 1080000, 356400, 4905, 5.57),
  (3, 'Minta Otthonszámla 9 év', 20000, 108, 2160000, 712800, 9810, 5.74),
  (3, 'Minta Otthonszámla 9 év', 30000, 108, 3240000, 1069200, 14715, 5.80),
  (3, 'Minta Otthonszámla 9 év', 40000, 108, 4320000, 1425600, 19620, 5.82),
  (3, 'Minta Otthonszámla 9 év', 50000, 108, 5400000, 1782000, 24525, 5.84),
  (3, 'Minta Otthonszámla 9 év', 60000, 108, 6480000, 2138400, 29430, 5.85),
  (3, 'Minta Otthonszámla 9 év', 70000, 108, 7560000, 2494800, 34335, 5.86),
  (3, 'Minta Otthonszámla 9 év', 80000, 108, 8640000, 2851200, 39240, 5.86),
  (3, 'Minta Otthonszámla 9 év', 90000, 108, 9720000, 3207600, 44145, 5.87),
  (3, 'Minta Otthonszámla 9 év', 100000, 108, 10800000, 3564000, 49050, 5.87),
  (3, 'Minta Otthonszámla 13 év', 10000, 156, 1560000, 624000, 10205, 4.96),
  (3, 'Minta Otthonszámla 13 év', 20000, 156, 3120000, 1248000, 20410, 4.96),
  (3, 'Minta Otthonszámla 13 év', 30000, 156, 4680000, 1872000, 30615, 4.96),
  (3, 'Minta Otthonszámla 13 év', 40000, 156, 6240000, 2496000, 40820, 4.96),
  (3, 'Minta Otthonszámla 13 év', 50000, 156, 7800000, 3120000, 51025, 4.96),
  (3, 'Minta Otthonszámla 13 év', 60000, 156, 9360000, 3744000, 61230, 4.96),
  (3, 'Minta Otthonszámla 13 év', 70000, 156, 10920000, 4368000, 71435, 4.96),
  (3, 'Minta Otthonszámla 13 év', 80000, 156, 12480000, 4992000, 81640, 4.96),
  (3, 'Minta Otthonszámla 13 év', 90000, 156, 14040000, 5616000, 91845, 4.96),
  (3, 'Minta Otthonszámla 13 év', 100000, 156, 15600000, 6240000, 102050, 4.96);
