<?php
declare(strict_types=1);

/*
 * Ajánlatok lekérése a kalkulátornak.
 * Hívás: api/ajanlatok.php?havi=50000&sav=1
 * Válasz: JSON, a rangsorolt ajánlatok intézménynév nélkül.
 *
 * A teljes találati lista (intézménnyel együtt) a munkamenetbe kerül, így amikor a látogató
 * később kiválaszt egy ajánlatot, a böngészőnek csak a helyezést kell visszaküldenie.
 */

require __DIR__ . '/../../src/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

function valasz(int $statusz, array $adat): void
{
    http_response_code($statusz);
    echo json_encode($adat, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    valasz(405, ['hiba' => 'Csak GET kérés engedélyezett.']);
}

// Csak egész számot fogadunk el, minden más (szöveg, tizedes, hiányzó érték) hibás bemenet.
$havi = filter_input(INPUT_GET, 'havi', FILTER_VALIDATE_INT);
$sav  = filter_input(INPUT_GET, 'sav', FILTER_VALIDATE_INT);

if (!is_int($havi) || !AjanlatKereso::ervenyesHavi($havi)) {
    valasz(400, ['hiba' => 'A havi összeg 10 000 és 100 000 Ft közötti, 10 000-rel osztható szám lehet.']);
}
if (!is_int($sav) || !AjanlatKereso::ervenyesSav($sav)) {
    valasz(400, ['hiba' => 'Érvénytelen futamidő-sáv.']);
}

try {
    $kereso = new AjanlatKereso(Database::kapcsolat($config));
    $ajanlatok = $kereso->keres($havi, $sav);
} catch (Throwable $e) {
    // A technikai részlet csak a szerver naplójába kerül, a látogató általános üzenetet kap.
    error_log('Ajánlatkeresés hiba: ' . $e->getMessage());
    valasz(500, ['hiba' => 'Átmeneti hiba történt, kérjük próbálja újra később.']);
}

$_SESSION['ajanlatok'] = [
    'havi'      => $havi,
    'sav'       => $sav,
    'ajanlatok' => $ajanlatok,
];

valasz(200, [
    'havi_megtakaritas' => $havi,
    'futamido_sav'      => AjanlatKereso::SAVOK[$sav]['nev'],
    'ajanlatok'         => AjanlatKereso::nyilvanosAdatok($ajanlatok),
]);
