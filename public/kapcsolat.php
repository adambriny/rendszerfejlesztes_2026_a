<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

$valasztott = $_SESSION['valasztott'] ?? null;

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
      <h2 class="kartya-cim">Kérjen visszahívást</h2>

      <?php if ($valasztott === null): ?>
        <p>A visszahíváshoz először válasszon ajánlatot a kalkulátorban.</p>
        <a class="gomb gomb-teljes" href="kalkulator.php">Kalkuláció indítása</a>
      <?php else: $a = $valasztott['ajanlat']; ?>
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
        <!-- Ide kerül a kapcsolati űrlap (#21). -->
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
oldal_lab();
