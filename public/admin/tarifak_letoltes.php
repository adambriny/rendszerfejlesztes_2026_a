<?php
declare(strict_types=1);

/*
 * Egy intézmény tarifáinak letöltése CSV fájlként. Ez egyben a feltöltés mintája is:
 * a letöltött fájl Excelben módosítható, és változtatás nélkül visszatölthető.
 */

require __DIR__ . '/../../src/bootstrap.php';

Admin::kotelezo();

$id = filter_input(INPUT_GET, 'intezmeny', FILTER_VALIDATE_INT);
try {
    $kezelo = new TarifaKezelo(Database::kapcsolat($config));
    $intezmeny = is_int($id) ? $kezelo->intezmeny($id) : null;
    if ($intezmeny === null) {
        http_response_code(404);
        exit('Ismeretlen intézmény.');
    }
    $csv = TarifaCsv::kiir($kezelo->tarifak($id));
} catch (Throwable $e) {
    error_log('Tarifa letöltési hiba: ' . $e->getMessage());
    http_response_code(500);
    exit('Átmeneti hiba, kérjük próbálja újra később.');
}

// A fájlnévben csak ékezet nélküli betű, szám és kötőjel marad.
$nev = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', iconv('UTF-8', 'ASCII//TRANSLIT', $intezmeny['nev']) ?: 'intezmeny') ?? '', '-'));
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="tarifak-' . ($nev ?: 'intezmeny') . '-' . date('Y-m-d') . '.csv"');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo $csv;
