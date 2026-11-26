-- Kitalált tesztérdeklődők az admin "Érdeklődők" oldal kipróbálásához.
-- Csak fejlesztői gépen futtasd, éles adatbázisba ne kerüljön.
-- Előfeltétel: a kamatbonusz.sql már be van töltve. Többször futtatva újabb példányokat szúr be.
SET NAMES utf8mb4;

INSERT INTO leads (nev, email, telefonszam, intezmeny_id, modozat, havi_megtakaritas,
                   befizetesek_szama, osszes_befizetes, lejaratkori_osszeg, ebkm,
                   jelenlegi_bank, allapot, beerkezes_datuma) VALUES
  ('Kovács Anna', 'kovacs.anna@example.com', '+36 30 123 4567', 1, 'LTP 30%+10% Rugalmas 10 év',
   20000, 120, 2400000, 3183658, 4.97, 'OTP', 'uj', '2026-11-25 09:12:00'),
  ('Nagy Péter', 'nagy.peter@example.com', '06 20 987 6543', 3, 'Minta Otthonszámla 13 év',
   50000, 156, 7800000, 10971025, 4.96, 'Egyéb bank', 'uj', '2026-11-26 14:40:00'),
  ('Szabó Éva', 'szabo.eva@example.com', '+36 70 555 1234', 1, 'LTP 15% 5 év',
   10000, 60, 600000, 692316, 3.19, 'Erste', 'lezart', '2026-11-20 18:03:00');
