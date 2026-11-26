<?php
declare(strict_types=1);

/*
 * Tarifatábla beolvasása és kiírása CSV formátumban (#24).
 *
 * A CSV egy egyszerű táblázatos szövegfájl, amit az Excel is meg tud nyitni és el tud menteni.
 * Elfogadjuk a magyar Excel szokásait is: pontosvessző elválasztó, tizedesvessző,
 * ezres szóköz, "Ft" és "%" jel, valamint a régi Windows-os (ANSI) karakterkódolás.
 */
final class TarifaCsv
{
    public const OSZLOPOK = [
        'modozat', 'havi_megtakaritas', 'befizetesek_szama', 'osszes_befizetes',
        'kamatbonusz', 'beteti_kamat', 'ebkm',
    ];

    public const MAX_SOR = 2000;

    /**
     * Beolvassa és ellenőrzi a fájl tartalmát.
     * Ha bármelyik sor hibás, a "hibak" lista nem üres, és semmit nem szabad menteni.
     *
     * @return array{sorok: array<int, array>, hibak: string[], figyelmeztetesek: string[]}
     */
    public static function beolvas(string $tartalom): array
    {
        $hibak = [];
        $figyelmeztetesek = [];
        $sorok = [];

        // Az Excel UTF-8 mentésnél egy láthatatlan jelet (BOM) tesz a fájl elejére, ezt levágjuk.
        $tartalom = preg_replace('/^\xEF\xBB\xBF/', '', $tartalom) ?? '';
        // Ha a fájl nem érvényes UTF-8, a magyar Windows alapértelmezett kódolásából alakítjuk át.
        if (!preg_match('//u', $tartalom)) {
            $atalakitott = @iconv('CP1250', 'UTF-8//IGNORE', $tartalom);
            $tartalom = $atalakitott === false ? '' : $atalakitott;
        }

        $tartalom = trim(str_replace(["\r\n", "\r"], "\n", $tartalom));
        if ($tartalom === '') {
            return ['sorok' => [], 'hibak' => ['A fájl üres.'], 'figyelmeztetesek' => []];
        }

        $elso = strtok($tartalom, "\n");
        $elvalaszto = substr_count($elso, ';') >= substr_count($elso, ',') ? ';' : ',';

        $fajl = fopen('php://temp', 'r+');
        fwrite($fajl, $tartalom);
        rewind($fajl);

        $fejlec = fgetcsv($fajl, 0, $elvalaszto, '"', '');
        $fejlec = array_map(static fn ($o) => strtolower(trim((string) $o)), $fejlec ?: []);
        $hianyzo = array_diff(self::OSZLOPOK, $fejlec);
        if ($hianyzo !== []) {
            fclose($fajl);
            return ['sorok' => [], 'hibak' => [
                'Hiányzó oszlop a fejlécben: ' . implode(', ', $hianyzo) . '. Az első sornak az oszlopneveket kell tartalmaznia.',
            ], 'figyelmeztetesek' => []];
        }
        $index = array_flip($fejlec);

        $sorszam = 1;
        $latott = [];
        $nemKalkulalhato = 0;
        while (($mezok = fgetcsv($fajl, 0, $elvalaszto, '"', '')) !== false) {
            $sorszam++;
            if ($mezok === [null] || implode('', $mezok) === '') {
                continue; // üres sor
            }
            if (count($sorok) >= self::MAX_SOR) {
                $hibak[] = 'A fájl legfeljebb ' . self::MAX_SOR . ' tarifasort tartalmazhat.';
                break;
            }

            $ertek = static fn (string $o): string => trim((string) ($mezok[$index[$o]] ?? ''));
            $sorHibak = [];

            $modozat = preg_replace('/\s+/u', ' ', $ertek('modozat')) ?? '';
            if ($modozat === '' || mb_strlen($modozat) > 100) {
                $sorHibak[] = 'a módozat neve kötelező, legfeljebb 100 karakter';
            }

            $szamok = [];
            foreach (['havi_megtakaritas', 'befizetesek_szama', 'osszes_befizetes', 'kamatbonusz', 'beteti_kamat'] as $o) {
                $szamok[$o] = self::egesz($ertek($o));
                if ($szamok[$o] === null) {
                    $sorHibak[] = "a(z) {$o} nem egész szám (\"{$ertek($o)}\")";
                }
            }
            $ebkm = self::tizedes($ertek('ebkm'));
            if ($ebkm === null) {
                $sorHibak[] = "az ebkm nem szám (\"{$ertek('ebkm')}\")";
            }

            if ($sorHibak === []) {
                foreach (['havi_megtakaritas', 'befizetesek_szama', 'osszes_befizetes'] as $o) {
                    if ($szamok[$o] <= 0) {
                        $sorHibak[] = "a(z) {$o} csak pozitív szám lehet";
                    }
                }
                foreach (['kamatbonusz', 'beteti_kamat'] as $o) {
                    if ($szamok[$o] < 0) {
                        $sorHibak[] = "a(z) {$o} nem lehet negatív";
                    }
                }
                if ($ebkm <= 0 || $ebkm >= 100) {
                    $sorHibak[] = 'az ebkm 0 és 100 közötti szám lehet';
                }
                if ($szamok['befizetesek_szama'] > 600) {
                    $sorHibak[] = 'a befizetések száma legfeljebb 600 hónap lehet';
                }
                if ($sorHibak === [] && $szamok['osszes_befizetes'] !== $szamok['havi_megtakaritas'] * $szamok['befizetesek_szama']) {
                    $sorHibak[] = 'az összes befizetés nem egyezik a havi összeg és a befizetések számának szorzatával';
                }
                $kulcs = mb_strtolower($modozat) . '|' . $szamok['havi_megtakaritas'];
                if (isset($latott[$kulcs])) {
                    $sorHibak[] = "ugyanez a módozat és havi összeg már szerepel a {$latott[$kulcs]}. sorban";
                }
                $latott[$kulcs] = $sorszam;
            }

            if ($sorHibak !== []) {
                $hibak[] = "{$sorszam}. sor: " . implode('; ', $sorHibak) . '.';
                continue;
            }

            if (!AjanlatKereso::ervenyesHavi($szamok['havi_megtakaritas'])) {
                $nemKalkulalhato++;
            }
            $sorok[] = ['modozat' => $modozat] + $szamok + ['ebkm' => round($ebkm, 2)];
        }
        fclose($fajl);

        if ($sorok === [] && $hibak === []) {
            $hibak[] = 'A fájl nem tartalmaz tarifasort, csak fejlécet.';
        }
        if ($nemKalkulalhato > 0) {
            $figyelmeztetesek[] = "{$nemKalkulalhato} sor havi összege nem 10 000 és 100 000 Ft közötti, 10 000-rel osztható szám. Ezek elmentődnek, de a kalkulátorban nem jelennek meg.";
        }

        return ['sorok' => $sorok, 'hibak' => $hibak, 'figyelmeztetesek' => $figyelmeztetesek];
    }

    /** A tarifákat a magyar Excel által közvetlenül megnyitható CSV szövegként adja vissza. */
    public static function kiir(array $tarifak): string
    {
        $fajl = fopen('php://temp', 'r+');
        fputcsv($fajl, self::OSZLOPOK, ';', '"', '');
        foreach ($tarifak as $t) {
            fputcsv($fajl, [
                self::cellaVedelem((string) $t['modozat']),
                $t['havi_megtakaritas'], $t['befizetesek_szama'], $t['osszes_befizetes'],
                $t['kamatbonusz'], $t['beteti_kamat'],
                number_format((float) $t['ebkm'], 2, ',', ''),
            ], ';', '"', '');
        }
        rewind($fajl);
        $csv = stream_get_contents($fajl);
        fclose($fajl);
        // A BOM jelzi az Excelnek, hogy a fájl UTF-8, így az ékezetek helyesen jelennek meg.
        return "\xEF\xBB\xBF" . $csv;
    }

    // "10 000 Ft" -> 10000. Csak egész szám fogadható el, a negatív is (azt a hívó utasítja el).
    private static function egesz(string $szoveg): ?int
    {
        $tiszta = preg_replace('/[\s\x{00A0}]|ft$/iu', '', $szoveg) ?? '';
        return preg_match('/^-?\d{1,10}$/', $tiszta) ? (int) $tiszta : null;
    }

    // "3,19%" -> 3.19
    private static function tizedes(string $szoveg): ?float
    {
        $tiszta = str_replace(',', '.', preg_replace('/[\s\x{00A0}%]/u', '', $szoveg) ?? '');
        return preg_match('/^-?\d{1,3}(\.\d{1,4})?$/', $tiszta) ? (float) $tiszta : null;
    }

    // Az Excel a =, +, -, @ jellel kezdődő cellát képletként futtatná. Egy aposztróf ezt megakadályozza.
    private static function cellaVedelem(string $ertek): string
    {
        return preg_match('/^[=+\-@\t\r]/', $ertek) ? "'" . $ertek : $ertek;
    }
}
