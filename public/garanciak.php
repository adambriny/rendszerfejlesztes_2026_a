<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

oldal_fej(
    'Garanciák: OBA-védelem és fix kamat',
    'A lakástakarék megtakarítás az OBA védelme alatt áll 100 000 euró értékhatárig, a kamat pedig a teljes futamidő alatt fix.',
    'garanciak.php'
);
?>
<section class="szakasz">
  <div class="tartalom">
    <p class="felcim">Garanciák</p>
    <h1>Biztonság, amit előre látni lehet.</h1>
    <p class="alcim keskeny">Két dolog, ami miatt nyugodtan alszik: a megtakarítása védett, a kamata pedig nem változik.</p>
    <div class="kettes">
      <article class="kartya ajanlat-elso">
        <p class="felcim">OBA-védelem</p>
        <p class="nagy-szam">100 000 €</p>
        <p>Megtakarítása az Országos Betétbiztosítási Alap (OBA) védelme alatt áll, ügyfelenként és intézményenként 100 000 euró értékhatárig.</p>
      </article>
      <article class="kartya">
        <p class="felcim">Fix kamat</p>
        <p class="nagy-szam">Fix.</p>
        <p>A szerződéskor rögzített kamat a teljes futamidő alatt nem változik. Nincs meglepetés, nem függ a piac hangulatától.</p>
      </article>
    </div>
  </div>
</section>

<section class="szakasz szakasz-feher">
  <div class="tartalom">
    <h2>És ami még számít.</h2>
    <div class="harmas">
      <article class="kartya"><h3>Felügyelt intézmények</h3><p>A lakástakarék-pénztárak a Magyar Nemzeti Bank felügyelete alatt működnek.</p></article>
      <article class="kartya"><h3>Összehasonlítható ajánlatok</h3><p>Az ajánlatokat az EBKM alapján rangsoroljuk, amely a költségeket is tartalmazza.</p></article>
      <article class="kartya"><h3>Adatai védelme</h3><p>Elérhetőségét kizárólag a kapcsolatfelvételhez használjuk. Részletek az <a href="adatkezeles.php">Adatkezelési nyilatkozatban</a>.</p></article>
      <article class="kartya"><h3>Banknevek nélkül</h3><p>Csak a számokat hasonlítjuk össze.</p></article>
      <article class="kartya"><h3>Nincs kötelezettség</h3><p>Csak egy beszélgetés indul.</p></article>
      <article class="kartya"><h3>Személyes adat nélkül</h3><p>A kalkulátorhoz nem kérünk személyes adatot.</p></article>
    </div>
    <div class="zaro">
      <a class="gomb" href="kalkulator.php">Kalkuláció indítása</a>
      <p class="megjegyzes">Az oldalon szereplő számítások tájékoztató jellegűek, nem minősülnek ajánlattételnek. A pontos feltételeket szerződéskötés előtt minden esetben egyeztetjük.</p>
    </div>
  </div>
</section>
<?php
oldal_lab();
