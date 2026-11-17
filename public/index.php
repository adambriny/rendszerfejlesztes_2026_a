<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

// A példa a valódi tarifákból számol: havi 20 000 Ft, 10 év vagy fölötte, a legjobb ajánlat.
// Ha az adatbázis nem érhető el, a példa egyszerűen kimarad, az oldal többi része megjelenik.
$pelda = null;
try {
    $pelda = (new AjanlatKereso(Database::kapcsolat($config)))->keres(20000, 3)[0] ?? null;
} catch (Throwable $e) {
    error_log('Kezdőlap példa hiba: ' . $e->getMessage());
}

oldal_fej(
    'Lakástakarék-kalkulátor',
    'Tegyen félre havonta egy kiszámítható összeget, és nézze meg, mennyit ér ez fix kamattal és kamatbónusszal. Számolja ki 2 perc alatt, banknevek nélkül.',
    'index.php'
);
?>
<section class="szakasz">
  <div class="tartalom hos">
    <div>
      <p class="felcim">Lakástakarék-kalkulátor</p>
      <h1>A megtakarítás nem lemondás. Hanem döntés.</h1>
      <p class="alcim">Tegyen félre havonta egy kiszámítható összeget, és nézze meg, mennyit ér ez fix kamattal és kamatbónusszal néhány év múlva.</p>
      <div class="gombsor">
        <a class="gomb" href="kalkulator.php">Számolja ki 2 perc alatt</a>
        <a class="szoveges-link" href="ismerteto.php">Hogyan működik? ›</a>
      </div>
    </div>
    <?php if ($pelda !== null): ?>
      <div class="ajanlat-kartya ajanlat-elso hos-kartya" aria-label="Példa ajánlat">
        <span class="cimke">1. helyezett</span>
        <p class="ajanlat-felirat">Lejáratkor felvehető összeg</p>
        <p class="ajanlat-osszeg"><?= e(ft($pelda['lejaratkori_osszeg'])) ?></p>
        <p class="hos-kartya-also">Havi <?= e(ft((int) $pelda['havi_megtakaritas'])) ?> · <?= e($pelda['befizetesek_szama']) ?> hónap</p>
      </div>
    <?php endif; ?>
  </div>
</section>

<section class="szakasz szakasz-feher">
  <div class="tartalom">
    <h2>Miért épp lakástakarék?</h2>
    <p class="alcim">Három ok, amiért a kiszámíthatóság többet ér, mint a bizonytalan hozam.</p>
    <div class="harmas">
      <article class="kartya">
        <h3>Fix kamat</h3>
        <p>A szerződéskor rögzített kamat a teljes futamidő alatt változatlan. Már az első napon tudja, mire számíthat.</p>
      </article>
      <article class="kartya">
        <h3>Kamatbónusz</h3>
        <p>Ha végig kitart a megtakarítás mellett, a kamat mellé extra jóváírást kaphat. Ez a kitartás jutalma.</p>
      </article>
      <article class="kartya">
        <h3>OBA-védelem</h3>
        <p>Megtakarítása az Országos Betétbiztosítási Alap védelme alatt áll, 100 000 euró értékhatárig.</p>
      </article>
    </div>
  </div>
</section>

<?php if ($pelda !== null): ?>
<section class="szakasz">
  <div class="tartalom">
    <p class="felcim">Egy példa</p>
    <h2>Havi 20 000 forint. Tíz év.</h2>
    <p class="alcim">Ebből <?= e(ft((int) $pelda['osszes_befizetes'])) ?> az Ön befizetése. A többit a kamat és a kamatbónusz teszi hozzá.</p>
    <div class="harmas szamok">
      <div><strong><?= e(ft((int) $pelda['havi_megtakaritas'])) ?></strong><span>havi megtakarítás</span></div>
      <div><strong><?= e($pelda['befizetesek_szama']) ?></strong><span>hónap futamidő</span></div>
      <div><strong>+<?= e(ft($pelda['lejaratkori_osszeg'] - (int) $pelda['osszes_befizetes'])) ?></strong><span>kamat és kamatbónusz</span></div>
    </div>
    <p class="megjegyzes">Példa egy aktuális lakástakarék tarifa alapján. A tényleges feltételek ajánlatonként eltérnek.</p>
  </div>
</section>
<?php endif; ?>

<section class="szakasz szakasz-feher">
  <div class="tartalom">
    <h2>Három lépés. Semmi apró betű.</h2>
    <ol class="lepesek harmas">
      <li class="kartya">
        <h3>Válaszoljon 3 kérdésre</h3>
        <p>Havi összeg, futamidő, jelenlegi bank. Ehhez nem kérünk személyes adatot.</p>
      </li>
      <li class="kartya">
        <h3>Nézze meg a legjobbakat</h3>
        <p>Konkrét számokat lát: mennyit fizet be, és mennyit vehet fel a futamidő végén.</p>
      </li>
      <li class="kartya">
        <h3>Beszéljünk, ha szeretné</h3>
        <p>Egy rövid telefonhívás. Nincs kötelezettség, csak egy beszélgetés indul.</p>
      </li>
    </ol>
  </div>
</section>

<section class="szakasz">
  <div class="tartalom zaro">
    <h2>Kezdje el ma. A jövőbeli önmaga megköszöni.</h2>
    <a class="gomb" href="kalkulator.php">Kalkuláció indítása</a>
  </div>
</section>
<?php
oldal_lab();
