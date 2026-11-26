<?php
declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';
require __DIR__ . '/../../src/admin_layout.php';

Admin::kotelezo();

const MAX_FAJLMERET = 1024 * 1024; // 1 MB, egy tarifatábla ennek töredéke

/** Üzenet a következő oldalbetöltésre (átirányítás után is megmarad). */
function uzenet_kesobbre(string $tipus, string $szoveg, array $reszletek = []): void
{
    $_SESSION['tarifa_uzenet'] = ['tipus' => $tipus, 'szoveg' => $szoveg, 'reszletek' => $reszletek];
}

/**
 * Sikertelen feltöltés egy meglévő intézményre: a régi tábla érintetlen marad, de lehet, hogy már
 * elavult. Ezt kimondjuk a hibaüzenetben, és felajánljuk a kivezetését, hogy az admin tudatosan
 * döntsön, ne maradjon kint észrevétlenül egy elavult tábla.
 */
function regi_tabla_figyelmeztetes(?TarifaKezelo $kezelo, ?int $intezmenyId): void
{
    if ($kezelo === null || $intezmenyId === null || !isset($_SESSION['tarifa_uzenet'])) {
        return;
    }
    try {
        $intezmeny = $kezelo->intezmeny($intezmenyId);
        $sorok = count($kezelo->tarifak($intezmenyId));
    } catch (Throwable $e) {
        return;
    }
    if ($intezmeny !== null && $sorok > 0) {
        $_SESSION['tarifa_uzenet']['regi'] = ['id' => $intezmenyId, 'nev' => $intezmeny['nev'], 'sorok' => $sorok];
    }
}

function vissza(): void
{
    header('Location: tarifak.php', true, 303);
    exit;
}

try {
    $kezelo = new TarifaKezelo(Database::kapcsolat($config));
} catch (Throwable $e) {
    error_log('Tarifakezelő hiba: ' . $e->getMessage());
    $kezelo = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kezelo !== null) {
    if (!Csrf::ervenyes($_POST['csrf'] ?? null)) {
        uzenet_kesobbre('hiba', 'A munkamenet lejárt. Kérjük, próbálja újra.');
        vissza();
    }
    $muvelet = $_POST['muvelet'] ?? '';
    $intezmenyId = null; // a hibakezelő is látja, ha a mentés közben történik hiba

    try {
        if ($muvelet === 'torles') {
            $id = filter_var($_POST['intezmeny'] ?? null, FILTER_VALIDATE_INT);
            $intezmeny = is_int($id) ? $kezelo->intezmeny($id) : null;
            if ($intezmeny === null) {
                uzenet_kesobbre('hiba', 'Ismeretlen intézmény.');
            } else {
                $db = $kezelo->torles($id);
                uzenet_kesobbre('siker', "{$intezmeny['nev']}: {$db} tarifasor törölve.");
            }
            vissza();
        }

        if ($muvelet === 'feltoltes') {
            // 1. Melyik intézmény? Meglévő vagy új.
            $valasztas = $_POST['intezmeny'] ?? '';
            $ujNev = trim(preg_replace('/\s+/u', ' ', (string) ($_POST['uj_nev'] ?? '')) ?? '');
            $intezmenyId = null;
            if ($valasztas === 'uj') {
                if (mb_strlen($ujNev) < 2 || mb_strlen($ujNev) > 100) {
                    uzenet_kesobbre('hiba', 'Az új intézmény neve 2 és 100 karakter közötti lehet.');
                    vissza();
                }
            } else {
                $id = filter_var($valasztas, FILTER_VALIDATE_INT);
                if (!is_int($id) || $kezelo->intezmeny($id) === null) {
                    uzenet_kesobbre('hiba', 'Kérjük, válasszon intézményt.');
                    vissza();
                }
                $intezmenyId = $id;
            }

            // 2. A feltöltött fájl ellenőrzése.
            $fajl = $_FILES['csv'] ?? null;
            if (!is_array($fajl) || ($fajl['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
                uzenet_kesobbre('hiba', 'Kérjük, válasszon ki egy CSV fájlt.');
                regi_tabla_figyelmeztetes($kezelo, $intezmenyId);
                vissza();
            }
            if ($fajl['error'] !== UPLOAD_ERR_OK || $fajl['size'] > MAX_FAJLMERET || !is_uploaded_file($fajl['tmp_name'])) {
                uzenet_kesobbre('hiba', 'A fájl feltöltése nem sikerült. A fájl legfeljebb 1 MB lehet.');
                regi_tabla_figyelmeztetes($kezelo, $intezmenyId);
                vissza();
            }
            if (strtolower(pathinfo((string) $fajl['name'], PATHINFO_EXTENSION)) !== 'csv') {
                uzenet_kesobbre('hiba', 'Csak .csv kiterjesztésű fájl tölthető fel. Excelben: Fájl, Mentés másként, CSV (pontosvesszővel tagolt).');
                regi_tabla_figyelmeztetes($kezelo, $intezmenyId);
                vissza();
            }

            // 3. Tartalom ellenőrzése. Egyetlen hibás sor esetén sem ment semmit.
            $eredmeny = TarifaCsv::beolvas((string) file_get_contents($fajl['tmp_name']));
            if ($eredmeny['hibak'] !== []) {
                $osszes = count($eredmeny['hibak']);
                uzenet_kesobbre('hiba', "A feltöltés NEM történt meg, mert a fájl hibás. Hibák száma: {$osszes}.", array_slice($eredmeny['hibak'], 0, 15));
                regi_tabla_figyelmeztetes($kezelo, $intezmenyId);
                vissza();
            }

            // 4. Mentés.
            if ($intezmenyId === null) {
                $intezmenyId = $kezelo->intezmenyFelvetele($ujNev);
            }
            $kezelo->csere($intezmenyId, $eredmeny['sorok']);
            $nev = $kezelo->intezmeny($intezmenyId)['nev'];
            uzenet_kesobbre('siker', "{$nev}: " . count($eredmeny['sorok']) . ' tarifasor feltöltve. A kalkulátor már az új tarifákkal számol.', $eredmeny['figyelmeztetesek']);
            vissza();
        }
    } catch (Throwable $e) {
        error_log('Tarifa mentési hiba: ' . $e->getMessage());
        uzenet_kesobbre('hiba', 'Átmeneti hiba miatt a művelet nem sikerült, semmi nem változott.');
        if ($muvelet === 'feltoltes') {
            regi_tabla_figyelmeztetes($kezelo, $intezmenyId);
        }
        vissza();
    }
    vissza();
}

$uzenet = $_SESSION['tarifa_uzenet'] ?? null;
unset($_SESSION['tarifa_uzenet']);

$osszesites = [];
$reszlet = null;
if ($kezelo !== null) {
    try {
        $osszesites = $kezelo->osszesites();
        $megtekint = filter_input(INPUT_GET, 'intezmeny', FILTER_VALIDATE_INT);
        if (is_int($megtekint) && ($i = $kezelo->intezmeny($megtekint)) !== null) {
            $reszlet = ['intezmeny' => $i, 'tarifak' => $kezelo->tarifak($megtekint)];
        }
    } catch (Throwable $e) {
        error_log('Tarifa lekérdezési hiba: ' . $e->getMessage());
        $kezelo = null;
    }
}

$csrf = Csrf::token();
admin_fej('Tarifák', 'tarifak.php');
?>
<section class="szakasz">
  <div class="tartalom">
    <div class="szakasz-fej">
      <h1>Tarifák</h1>
      <p class="alcim">Intézményenként egy tarifatábla. Feltöltéskor az intézmény összes korábbi tarifája lecserélődik.</p>
    </div>

    <?php if ($uzenet !== null): ?>
      <div class="uzenet <?= $uzenet['tipus'] === 'siker' ? 'uzenet-siker' : 'uzenet-hiba' ?>" role="status">
        <p><?= e($uzenet['szoveg']) ?></p>
        <?php if ($uzenet['reszletek'] !== []): ?>
          <ul><?php foreach ($uzenet['reszletek'] as $r): ?><li><?= e($r) ?></li><?php endforeach; ?></ul>
        <?php endif; ?>
        <?php if (isset($uzenet['regi'])): $regi = $uzenet['regi']; ?>
          <div class="regi-tabla">
            <p><strong>Figyelem:</strong> a(z) <?= e($regi['nev']) ?> korábbi tarifatáblája (<?= e($regi['sorok']) ?> sor) továbbra is látszik a kalkulátorban. Ha ez már elavult, vezesse ki most, és töltse fel a javított fájlt, amint elkészült.</p>
            <form action="tarifak.php" method="post" data-megerosites="Biztosan kivezeti a(z) <?= e($regi['nev']) ?> régi tarifatábláját? A javított fájl feltöltéséig a kalkulátor nem ajánlja ezt az intézményt.">
              <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
              <input type="hidden" name="muvelet" value="torles">
              <input type="hidden" name="intezmeny" value="<?= e($regi['id']) ?>">
              <button type="submit" class="gomb gomb-kicsi gomb-veszelyes">Régi tábla kivezetése most</button>
            </form>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

    <?php if ($kezelo === null): ?>
      <p class="uzenet uzenet-hiba">Átmeneti hiba miatt a tarifák nem tölthetők be.</p>
    <?php else: ?>

    <div class="tabla-keret">
      <table class="tabla">
        <thead>
          <tr><th>Intézmény</th><th>Tarifasorok</th><th>Módozatok</th><th>Havi összeg</th><th>Futamidő</th><th>Műveletek</th></tr>
        </thead>
        <tbody>
          <?php foreach ($osszesites as $sor): ?>
            <tr>
              <td><strong><?= e($sor['nev']) ?></strong></td>
              <td><?= e($sor['sorok']) ?></td>
              <td><?= e($sor['modozatok']) ?></td>
              <td><?= $sor['sorok'] > 0 ? e(ft((int) $sor['havi_min']) . ' és ' . ft((int) $sor['havi_max']) . ' között') : 'nincs tarifa' ?></td>
              <td><?= $sor['sorok'] > 0 ? e($sor['ho_min'] . ' és ' . $sor['ho_max'] . ' hó között') : '' ?></td>
              <td class="muveletek">
                <?php if ($sor['sorok'] > 0): ?>
                  <a href="tarifak.php?intezmeny=<?= e($sor['id']) ?>#reszlet">Megtekintés</a>
                  <a href="tarifak_letoltes.php?intezmeny=<?= e($sor['id']) ?>">Letöltés (CSV)</a>
                  <form action="tarifak.php" method="post" data-megerosites="Biztosan törli a(z) <?= e($sor['nev']) ?> összes tarifáját? A kalkulátor ezután nem ajánlja ezt az intézményt.">
                    <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                    <input type="hidden" name="muvelet" value="torles">
                    <input type="hidden" name="intezmeny" value="<?= e($sor['id']) ?>">
                    <button type="submit" class="link-gomb veszelyes">Törlés</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="kettes">
      <form class="kartya urlap" action="tarifak.php" method="post" enctype="multipart/form-data">
        <h2 class="kartya-cim">Tarifatábla feltöltése</h2>
        <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="muvelet" value="feltoltes">
        <div class="mezo">
          <label for="intezmeny">Intézmény</label>
          <select id="intezmeny" name="intezmeny" class="legordulo" required>
            <option value="" selected disabled>Válasszon</option>
            <?php foreach ($osszesites as $sor): ?>
              <option value="<?= e($sor['id']) ?>"><?= e($sor['nev']) ?></option>
            <?php endforeach; ?>
            <option value="uj">Új intézmény...</option>
          </select>
        </div>
        <div class="mezo" id="uj-nev-mezo">
          <label for="uj_nev">Új intézmény neve (csak új intézménynél)</label>
          <input id="uj_nev" name="uj_nev" type="text" maxlength="100">
        </div>
        <div class="mezo">
          <label for="csv">CSV fájl (legfeljebb 1 MB)</label>
          <input id="csv" name="csv" type="file" accept=".csv,text/csv" required>
        </div>
        <button type="submit" class="gomb gomb-teljes">Feltöltés</button>
        <p class="megjegyzes">Ha a fájlban akár egy hibás sor is van, semmi nem változik, és a hibás sorokat felsoroljuk. Ilyenkor felajánljuk a régi tábla kivezetését is.</p>
      </form>

      <div class="kartya">
        <h2 class="kartya-cim">A CSV fájl formátuma</h2>
        <p>Az első sor az oszlopnevek, utána soronként egy módozat egy havi összegre:</p>
        <pre class="kod">modozat;havi_megtakaritas;befizetesek_szama;osszes_befizetes;kamatbonusz;beteti_kamat;ebkm
LTP 15% 5 év;10000;60;600000;90200;2116;3,19</pre>
        <ul>
          <li>Összegek forintban, egész számmal. A szóköz és a "Ft" elhagyható.</li>
          <li>Az összes befizetés = havi összeg × befizetések száma.</li>
          <li>Az EBKM a tarifatáblában közölt hivatalos érték, tizedesvesszővel is írható.</li>
          <li><strong>Módosításhoz:</strong> töltse le a meglévő táblát, javítsa Excelben, mentse CSV-ként, és töltse fel újra.</li>
          <li><strong>Más formátumú banki tábla (PDF, Excel):</strong> előbb alakítsa át erre a szerkezetre, például AI segítségével. Feltöltés előtt néhány sort vessen össze az eredeti táblával, mert a rendszer csak az ellentmondásokat szűri ki, egy elírt, de hihető számot nem.</li>
        </ul>
      </div>
    </div>

    <?php if ($reszlet !== null): ?>
      <div id="reszlet" class="reszlet">
        <h2><?= e($reszlet['intezmeny']['nev']) ?> tarifái (<?= count($reszlet['tarifak']) ?> sor)</h2>
        <div class="tabla-keret">
          <table class="tabla tabla-szamok">
            <thead>
              <tr><th>Módozat</th><th>Havi összeg</th><th>Hónap</th><th>Összes befizetés</th><th>Kamatbónusz</th><th>Betéti kamat</th><th>Lejáratkor</th><th>EBKM</th></tr>
            </thead>
            <tbody>
              <?php foreach ($reszlet['tarifak'] as $t): ?>
                <tr>
                  <td><?= e($t['modozat']) ?></td>
                  <td><?= e(ft((int) $t['havi_megtakaritas'])) ?></td>
                  <td><?= e($t['befizetesek_szama']) ?></td>
                  <td><?= e(ft((int) $t['osszes_befizetes'])) ?></td>
                  <td><?= e(ft((int) $t['kamatbonusz'])) ?></td>
                  <td><?= e(ft((int) $t['beteti_kamat'])) ?></td>
                  <td><?= e(ft((int) $t['osszes_befizetes'] + (int) $t['kamatbonusz'] + (int) $t['beteti_kamat'])) ?></td>
                  <td><?= e(number_format((float) $t['ebkm'], 2, ',', '')) ?>%</td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    <?php endif; ?>

    <?php endif; ?>
  </div>
</section>
<?php
admin_lab();
