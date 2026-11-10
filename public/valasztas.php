<?php
declare(strict_types=1);

/*
 * Az "Ezt választom" gomb ide küldi a választott helyezést és a bankot.
 * Az ajánlat adatait nem a böngészőtől fogadjuk el, hanem a munkamenetben eltárolt
 * keresési eredményből vesszük, így a látogató nem tud kitalált számokat beküldeni.
 */

require __DIR__ . '/../src/bootstrap.php';

function vissza_a_kalkulatorhoz(): void
{
    header('Location: kalkulator.php', true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Csrf::ervenyes($_POST['csrf'] ?? null)) {
    vissza_a_kalkulatorhoz();
}

$helyezes = filter_var($_POST['helyezes'] ?? null, FILTER_VALIDATE_INT);
$bank = $_POST['bank'] ?? null;
$kereses = $_SESSION['ajanlatok'] ?? null;

if (!is_int($helyezes) || !Bankok::ervenyes($bank) || !is_array($kereses)) {
    vissza_a_kalkulatorhoz();
}

$ajanlat = null;
foreach ($kereses['ajanlatok'] as $a) {
    if ($a['helyezes'] === $helyezes) {
        $ajanlat = $a;
        break;
    }
}
if ($ajanlat === null) {
    vissza_a_kalkulatorhoz();
}

$_SESSION['valasztott'] = [
    'ajanlat' => $ajanlat,
    'bank'    => $bank,
    'sav'     => $kereses['sav'],
];

header('Location: kapcsolat.php', true, 303);
exit;
