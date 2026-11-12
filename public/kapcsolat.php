<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

$valasztott = $_SESSION['valasztott'] ?? null;
$urlap = null;
$mentesiHiba = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lejárt munkamenet, hiányzó token vagy választott ajánlat nélkül nincs mit beküldeni.
    if ($valasztott === null || !Csrf::ervenyes($_POST['csrf'] ?? null)) {
        header('Location: kalkulator.php', true, 303);
        exit;
    }

    // A robot sikeres beküldést lát, így nem próbálkozik tovább, de semmi nem történik.
    if (KapcsolatUrlap::robot($_POST)) {
        $_SESSION['kapcsolat_siker'] = true;
        header('Location: kapcsolat.php', true, 303);
        exit;
    }

    $urlap = new KapcsolatUrlap($_POST);
    if ($urlap->ervenyes()) {
        // 1. Mentés: ha ez nem sikerül, a látogató hibaüzenetet kap, és újra próbálhatja.
        try {
            $db = Database::kapcsolat($config);
            $mentes = new LeadMentes($db);
            $leadId = $mentes->ment($valasztott, $urlap->adatok);
        } catch (Throwable $e) {
            error_log('Érdeklődés mentési hiba: ' . $e->getMessage());
            $mentesiHiba = true;
        }

        // 2. Értesítés: az érdeklődés ekkor már el van mentve, így egy sikertelen
        // e-mail küldés miatt nem vész el. A hiba a naplóba kerül.
        if (!$mentesiHiba) {
            try {
                if (empty($config['mail']['cimzett'])) {
                    throw new RuntimeException('Hiányzó e-mail beállítás a config.php fájlban.');
                }
                $intezmeny = $mentes->intezmenyNev((int) $valasztott['ajanlat']['intezmeny_id']);
                (new Ertesito($config['mail']))->kuld($leadId, $valasztott, $urlap->adatok, $intezmeny);
            } catch (Throwable $e) {
                error_log('Értesítő e-mail hiba (érdeklődés #' . $leadId . '): ' . $e->getMessage());
            }
        }
    }

    if ($urlap->ervenyes() && !$mentesiHiba) {
        $_SESSION['kapcsolat_siker'] = true;
        unset($_SESSION['valasztott']);
        // Átirányítás után a böngésző frissítése nem küldi be újra az űrlapot.
        header('Location: kapcsolat.php', true, 303);
        exit;
    }
}

$siker = !empty($_SESSION['kapcsolat_siker']);
unset($_SESSION['kapcsolat_siker']);

$ertek = static fn (string $mezo): string => $urlap?->adatok[$mezo] ?? '';
$hiba = static fn (string $mezo): ?string => $urlap?->hibak[$mezo] ?? null;

oldal_fej(
    'Kapcsolat',
    'Kérjen visszahívást: egy rövid beszélgetésben végigvesszük a választott lakástakarék ajánlat számait.',
    'kapcsolat.php'
);
?>
<section class="szakasz">
  <div class="tartalom kapcsolat">
    <div>
      <p class="felcim">Kapcsolat</p>
      <h1>Beszéljünk a megtakarításáról.</h1>
      <p class="alcim">Hagyja meg elérhetőségét, és egy rövid hívásban végigvesszük a számokat.</p>
      <p class="kiemelt-doboz">Nincs kötelezettség, csak egy beszélgetés indul.</p>
      <p class="uzenet">Akár 1 órán belül felvesszük Önnel a kapcsolatot.</p>
    </div>

    <div class="kartya">
      <?php if ($siker): ?>
        <h2 class="kartya-cim">Köszönjük!</h2>
        <p class="uzenet uzenet-siker" role="status">Megkaptuk az érdeklődését. Hamarosan felvesszük Önnel a kapcsolatot a megadott telefonszámon.</p>
        <a class="gomb gomb-teljes" href="index.php">Vissza a kezdőlapra</a>

      <?php elseif ($valasztott === null): ?>
        <h2 class="kartya-cim">Kérjen visszahívást</h2>
        <p>A visszahíváshoz először válasszon ajánlatot a kalkulátorban.</p>
        <a class="gomb gomb-teljes" href="kalkulator.php">Kalkuláció indítása</a>

      <?php else: $a = $valasztott['ajanlat']; ?>
        <h2 class="kartya-cim">Kérjen visszahívást</h2>
        <div class="valasztott">
          <div class="valasztott-fej">
            <strong>A választott ajánlat: <?= e($a['helyezes']) ?>. helyezett</strong>
            <a href="kalkulator.php">Módosítás</a>
          </div>
          <dl>
            <div><dt>Havi megtakarítás</dt><dd><?= e(ft((int) $a['havi_megtakaritas'])) ?></dd></div>
            <div><dt>Havi befizetések száma</dt><dd><?= e($a['befizetesek_szama']) ?> hó</dd></div>
            <div><dt>Befizetett összeg</dt><dd><?= e(ft((int) $a['osszes_befizetes'])) ?></dd></div>
            <div><dt>Lejáratkor felvehető összeg</dt><dd><?= e(ft((int) $a['lejaratkori_osszeg'])) ?></dd></div>
          </dl>
        </div>

        <?php if ($urlap !== null && !$urlap->ervenyes()): ?>
          <p class="uzenet uzenet-hiba" role="alert">Kérjük, javítsa a megjelölt mezőket.</p>
        <?php elseif ($mentesiHiba): ?>
          <p class="uzenet uzenet-hiba" role="alert">Átmeneti hiba miatt nem sikerült elküldeni az érdeklődését. Kérjük, próbálja újra néhány perc múlva.</p>
        <?php endif; ?>

        <form class="urlap" action="kapcsolat.php" method="post">
          <input type="hidden" name="csrf" value="<?= e(Csrf::token()) ?>">

          <?php foreach ([
              'nev'         => ['Név', 'text', 'name', 'Teljes név', 100],
              'email'       => ['E-mail cím', 'email', 'email', 'pelda@email.hu', 254],
              'telefonszam' => ['Telefonszám', 'tel', 'tel', '+36 30 123 4567', 30],
          ] as $mezo => [$cimke, $tipus, $autocomplete, $minta, $max]): ?>
            <div class="mezo">
              <label for="<?= $mezo ?>"><?= e($cimke) ?></label>
              <input id="<?= $mezo ?>" name="<?= $mezo ?>" type="<?= $tipus ?>" autocomplete="<?= $autocomplete ?>"
                     placeholder="<?= e($minta) ?>" maxlength="<?= $max ?>" required
                     value="<?= e($ertek($mezo)) ?>"
                     <?= $hiba($mezo) ? 'aria-invalid="true" aria-describedby="' . $mezo . '-hiba"' : '' ?>>
              <?php if ($hiba($mezo)): ?>
                <p class="mezo-hiba" id="<?= $mezo ?>-hiba"><?= e($hiba($mezo)) ?></p>
              <?php endif; ?>
            </div>
          <?php endforeach; ?>

          <!-- Honeypot: ember számára láthatatlan, csak a spam robotok töltik ki. -->
          <div class="csapda" aria-hidden="true">
            <label for="weboldal">Weboldal</label>
            <input id="weboldal" name="weboldal" type="text" tabindex="-1" autocomplete="off">
          </div>

          <div class="jelolo">
            <input id="adatkezeles" name="adatkezeles" type="checkbox" value="1" required
                   <?= $hiba('adatkezeles') ? 'aria-invalid="true" aria-describedby="adatkezeles-hiba"' : '' ?>>
            <label for="adatkezeles">Elolvastam és elfogadom az <a href="adatkezeles.php" target="_blank" rel="noopener">Adatkezelési nyilatkozatot</a>.</label>
          </div>
          <?php if ($hiba('adatkezeles')): ?>
            <p class="mezo-hiba" id="adatkezeles-hiba"><?= e($hiba('adatkezeles')) ?></p>
          <?php endif; ?>

          <button type="submit" class="gomb gomb-teljes">Kérem a visszahívást</button>
          <p class="megjegyzes">Adatait kizárólag a kapcsolatfelvételhez használjuk.</p>
        </form>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
oldal_lab();
