<?php
declare(strict_types=1);

/*
 * Ajánlatok keresése és rangsorolása (#19).
 *
 * A megadott havi összeghez és futamidő-sávhoz megkeresi az illeszkedő
 * tarifasorokat, intézményenként a legjobbat tartja meg, és EBKM szerint
 * csökkenő sorrendbe rendezi őket.
 */
final class AjanlatKereso
{
    // A kalkulátor havi összeg választéka: 10 000 és 100 000 Ft között, 10 000 Ft-os lépésekben.
    public const HAVI_MIN = 10000;
    public const HAVI_MAX = 100000;
    public const HAVI_LEPES = 10000;

    // Futamidő-sávok hónapban. A 60 hónap még az első, a 120 hónap már a harmadik sávba tartozik.
    public const SAVOK = [
        1 => ['nev' => '5 év vagy az alatt',  'min' => 1,   'max' => 60],
        2 => ['nev' => '5 és 10 év között',   'min' => 61,  'max' => 119],
        3 => ['nev' => '10 év vagy fölötte',  'min' => 120, 'max' => 65535],
    ];

    public function __construct(private PDO $db)
    {
    }

    public static function ervenyesHavi(int $havi): bool
    {
        return $havi >= self::HAVI_MIN && $havi <= self::HAVI_MAX && $havi % self::HAVI_LEPES === 0;
    }

    public static function ervenyesSav(int $sav): bool
    {
        return isset(self::SAVOK[$sav]);
    }

    /**
     * Visszaadja a rangsorolt ajánlatokat (legfeljebb intézményenként egyet).
     * Az eredmény tartalmazza az intézmény azonosítóját is, ezért csak szerver oldalon
     * használható. A böngészőnek a nyilvanosAdatok() kimenete megy.
     */
    public function keres(int $havi, int $sav): array
    {
        if (!self::ervenyesHavi($havi) || !self::ervenyesSav($sav)) {
            throw new InvalidArgumentException('Érvénytelen havi összeg vagy futamidő-sáv.');
        }

        $stmt = $this->db->prepare(
            'SELECT id, intezmeny_id, modozat, havi_megtakaritas, befizetesek_szama,
                    osszes_befizetes, kamatbonusz, beteti_kamat, ebkm
               FROM tarifak
              WHERE havi_megtakaritas = :havi
                AND befizetesek_szama BETWEEN :min AND :max'
        );
        $stmt->execute([
            'havi' => $havi,
            'min'  => self::SAVOK[$sav]['min'],
            'max'  => self::SAVOK[$sav]['max'],
        ]);

        // Intézményenként a legjobb sor marad meg.
        $legjobb = [];
        foreach ($stmt->fetchAll() as $sor) {
            $sor['lejaratkori_osszeg'] = (int) $sor['osszes_befizetes'] + (int) $sor['kamatbonusz'] + (int) $sor['beteti_kamat'];
            $sor['ebkm'] = (float) $sor['ebkm'];
            $kulcs = (int) $sor['intezmeny_id'];
            if (!isset($legjobb[$kulcs]) || self::jobb($sor, $legjobb[$kulcs])) {
                $legjobb[$kulcs] = $sor;
            }
        }

        $ajanlatok = array_values($legjobb);
        usort($ajanlatok, static fn (array $a, array $b): int => self::jobb($a, $b) ? -1 : 1);

        foreach ($ajanlatok as $i => &$ajanlat) {
            $ajanlat['helyezes'] = $i + 1;
        }
        unset($ajanlat);

        return $ajanlatok;
    }

    /**
     * A böngészőnek küldhető adatok: sem az intézmény, sem a módozat neve nem szerepel benne,
     * mert a módozat nevéből is kitalálható lenne az intézmény.
     */
    public static function nyilvanosAdatok(array $ajanlatok): array
    {
        return array_map(static fn (array $a): array => [
            'helyezes'           => $a['helyezes'],
            'havi_megtakaritas'  => (int) $a['havi_megtakaritas'],
            'befizetesek_szama'  => (int) $a['befizetesek_szama'],
            'befizetett_osszeg'  => (int) $a['osszes_befizetes'],
            'lejaratkori_osszeg' => $a['lejaratkori_osszeg'],
        ], $ajanlatok);
    }

    // Magasabb EBKM nyer. Egyenlőségnél a nagyobb végösszeg, utána a kisebb azonosító,
    // hogy ugyanarra a kérdésre mindig ugyanaz a sorrend jöjjön ki.
    private static function jobb(array $a, array $b): bool
    {
        if ($a['ebkm'] !== $b['ebkm']) {
            return $a['ebkm'] > $b['ebkm'];
        }
        if ($a['lejaratkori_osszeg'] !== $b['lejaratkori_osszeg']) {
            return $a['lejaratkori_osszeg'] > $b['lejaratkori_osszeg'];
        }
        return (int) $a['id'] < (int) $b['id'];
    }
}
