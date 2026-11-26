<?php
declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';
require __DIR__ . '/../../src/admin_layout.php';

Admin::kotelezo();

/** Csak ismert szűrőérték mehet tovább, minden más "összes"-nek számít. */
function szuro_ellenorzes(mixed $ertek): ?string
{
    return is_string($ertek) && array_key_exists($ertek, LeadKezelo::ALLAPOTOK) ? $ertek : null;
}

function oldal_ellenorzes(mixed $ertek): int
{
    $oldal = filter_var($ertek, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 100000]]);
    return is_int($oldal) ? $oldal : 1;
}

function lista_url(?string $allapot, int $oldal = 1): string
{
    $parameterek = array_filter(['allapot' => $allapot, 'oldal' => $oldal > 1 ? $oldal : null]);
    return 'leadek.php' . ($parameterek !== [] ? '?' . http_build_query($parameterek) : '');
}

/** Művelet után ugyanarra a szűrésre és oldalra térünk vissza (PRG: frissítéskor nem ismétlődik a művelet). */
function vissza_listara(): void
{
    $url = lista_url(szuro_ellenorzes($_POST['szuro'] ?? null), oldal_ellenorzes($_POST['oldal'] ?? 1));
    header('Location: ' . $url, true, 303);
    exit;
}

function lead_uzenet(string $tipus, string $szoveg): void
{
    $_SESSION['lead_uzenet'] = ['tipus' => $tipus, 'szoveg' => $szoveg];
}

try {
    $kezelo = new LeadKezelo(Database::kapcsolat($config));
} catch (Throwable $e) {
    error_log('Leadkezelő hiba: ' . $e->getMessage());
    $kezelo = null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $kezelo !== null) {
    if (!Csrf::ervenyes($_POST['csrf'] ?? null)) {
        lead_uzenet('hiba', 'A munkamenet lejárt. Kérjük, próbálja újra.');
        vissza_listara();
    }

    $id = filter_var($_POST['id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    $muvelet = $_POST['muvelet'] ?? '';
    try {
        $nev = is_int($id) ? $kezelo->nev($id) : null;
        if ($nev === null) {
            lead_uzenet('hiba', 'Ez az érdeklődés már nem létezik.');
        } elseif ($muvelet === 'lezaras') {
            $kezelo->allapotValtas($id, 'lezart');
            lead_uzenet('siker', "{$nev} érdeklődése lezárva.");
        } elseif ($muvelet === 'ujranyitas') {
            $kezelo->allapotValtas($id, 'uj');
            lead_uzenet('siker', "{$nev} érdeklődése újra \"Új\" állapotú.");
        } elseif ($muvelet === 'torles') {
            $kezelo->torles($id);
            lead_uzenet('siker', "{$nev} adatai véglegesen törölve.");
        } else {
            lead_uzenet('hiba', 'Ismeretlen művelet.');
        }
    } catch (Throwable $e) {
        error_log('Lead művelet hiba: ' . $e->getMessage());
        lead_uzenet('hiba', 'Átmeneti hiba miatt a művelet nem sikerült, semmi nem változott.');
    }
    vissza_listara();
}

$uzenet = $_SESSION['lead_uzenet'] ?? null;
unset($_SESSION['lead_uzenet']);

$szuro = szuro_ellenorzes($_GET['allapot'] ?? null);
$oldal = oldal_ellenorzes($_GET['oldal'] ?? 1);
$darabok = null;
$leadek = [];
if ($kezelo !== null) {
    try {
        $darabok = $kezelo->darabok();
        $talalat = $darabok[$szuro ?? 'osszes'];
        $oldalak = max(1, (int) ceil($talalat / LeadKezelo::OLDALMERET));
        $oldal = min($oldal, $oldalak);
        $leadek = $kezelo->lista($szuro, $oldal);
    } catch (Throwable $e) {
        error_log('Lead lekérdezési hiba: ' . $e->getMessage());
        $kezelo = null;
    }
}

$csrf = Csrf::token();
$szurok = ['' => 'Összes', 'uj' => 'Új', 'lezart' => 'Lezárt'];
admin_fej('Érdeklődők', 'leadek.php');
?>
<section class="szakasz">
  <div class="tartalom">
    <div class="szakasz-fej">
      <h1>Érdeklődők</h1>
      <p class="alcim">A visszahívást kérő látogatók, a legújabbal kezdve.</p>
    </div>

    <?php if ($uzenet !== null): ?>
      <p class="uzenet <?= $uzenet['tipus'] === 'siker' ? 'uzenet-siker' : 'uzenet-hiba' ?>" role="status"><?= e($uzenet['szoveg']) ?></p>
    <?php endif; ?>

    <?php if ($kezelo === null): ?>
      <p class="uzenet uzenet-hiba">Átmeneti hiba miatt az érdeklődések nem tölthetők be.</p>
    <?php else: ?>

    <nav class="szurok" aria-label="Szűrés állapot szerint">
      <?php foreach ($szurok as $kulcs => $felirat): ?>
        <?php $aktiv = ($szuro ?? '') === $kulcs; ?>
        <a href="<?= e(lista_url($kulcs === '' ? null : $kulcs)) ?>"<?= $aktiv ? ' aria-current="page"' : '' ?>>
          <?= e($felirat) ?> <span><?= e($darabok[$kulcs === '' ? 'osszes' : $kulcs]) ?></span>
        </a>
      <?php endforeach; ?>
    </nav>

    <?php if ($leadek === []): ?>
      <p class="ures-lista">Nincs megjeleníthető érdeklődés.</p>
    <?php else: ?>
      <div class="lead-lista">
        <?php foreach ($leadek as $l): ?>
          <?php
            $uj = $l['allapot'] === 'uj';
            $lejarat = (int) $l['lejaratkori_osszeg'];
            // A tel: linkbe csak számjegy és + kerülhet, így a telefon tárcsázója is jól értelmezi.
            $tel = preg_replace('/[^0-9+]/', '', (string) $l['telefonszam']) ?? '';
          ?>
          <article class="kartya lead<?= $uj ? ' lead-uj' : '' ?>">
            <header class="lead-fej">
              <div>
                <h2><?= e($l['nev']) ?></h2>
                <p class="lead-datum"><?= e(date('Y. m. d. H:i', strtotime((string) $l['beerkezes_datuma']))) ?></p>
              </div>
              <span class="cimke<?= $uj ? ' cimke-uj' : '' ?>"><?= e(LeadKezelo::ALLAPOTOK[$l['allapot']] ?? $l['allapot']) ?></span>
            </header>

            <p class="lead-kapcsolat">
              <a href="mailto:<?= e($l['email']) ?>"><?= e($l['email']) ?></a>
              <a href="tel:<?= e($tel) ?>"><?= e($l['telefonszam']) ?></a>
            </p>

            <dl class="lead-adatok">
              <dt>Intézmény</dt><dd><?= e($l['intezmeny_nev'] ?? 'Ismeretlen') ?></dd>
              <dt>Módozat</dt><dd><?= e($l['modozat']) ?></dd>
              <dt>Havi megtakarítás</dt><dd><?= e(ft((int) $l['havi_megtakaritas'])) ?></dd>
              <dt>Futamidő</dt><dd><?= e($l['befizetesek_szama']) ?> hó</dd>
              <dt>Befizetett összeg</dt><dd><?= e(ft((int) $l['osszes_befizetes'])) ?></dd>
              <dt>Lejáratkor</dt><dd><strong><?= e(ft($lejarat)) ?></strong></dd>
              <dt>EBKM</dt><dd><?= e(number_format((float) $l['ebkm'], 2, ',', '')) ?>%</dd>
              <dt>Jelenlegi bankja</dt><dd><?= e($l['jelenlegi_bank']) ?></dd>
            </dl>

            <div class="lead-muveletek">
              <form action="leadek.php" method="post">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= e($l['id']) ?>">
                <input type="hidden" name="szuro" value="<?= e($szuro ?? '') ?>">
                <input type="hidden" name="oldal" value="<?= e($oldal) ?>">
                <?php if ($uj): ?>
                  <button type="submit" name="muvelet" value="lezaras" class="gomb gomb-kicsi">Lezárás</button>
                <?php else: ?>
                  <button type="submit" name="muvelet" value="ujranyitas" class="link-gomb">Visszaállítás "Új" állapotba</button>
                <?php endif; ?>
              </form>
              <form action="leadek.php" method="post" data-megerosites="Biztosan véglegesen törli <?= e($l['nev']) ?> összes adatát? Ez nem vonható vissza.">
                <input type="hidden" name="csrf" value="<?= e($csrf) ?>">
                <input type="hidden" name="id" value="<?= e($l['id']) ?>">
                <input type="hidden" name="szuro" value="<?= e($szuro ?? '') ?>">
                <input type="hidden" name="oldal" value="<?= e($oldal) ?>">
                <button type="submit" name="muvelet" value="torles" class="link-gomb veszelyes">Törlés (GDPR)</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

      <?php if ($oldalak > 1): ?>
        <nav class="lapozo" aria-label="Lapozás">
          <?php if ($oldal > 1): ?><a href="<?= e(lista_url($szuro, $oldal - 1)) ?>">Előző</a><?php endif; ?>
          <span><?= e($oldal) ?>. oldal / <?= e($oldalak) ?></span>
          <?php if ($oldal < $oldalak): ?><a href="<?= e(lista_url($szuro, $oldal + 1)) ?>">Következő</a><?php endif; ?>
        </nav>
      <?php endif; ?>
    <?php endif; ?>

    <p class="megjegyzes lead-gdpr">A törlés végleges, nem vonható vissza. Akkor használja, ha az érdeklődő kéri adatai törlését, vagy ha a lezárt érdeklődésre már nincs szükség.</p>

    <?php endif; ?>
  </div>
</section>
<?php
admin_lab();
