<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

oldal_fej(
    'Ismertető: hogyan működik a lakástakarék?',
    'A lakástakarék-pénztár lakáscélú megtakarítási forma fix kamattal és kamatbónusszal. Megmutatjuk, hogyan működik, és mire használható fel.',
    'ismerteto.php'
);
?>
<section class="szakasz">
  <div class="tartalom">
    <p class="felcim">Ismertető</p>
    <h1>Lakástakarék, érthetően.</h1>
    <p class="alcim keskeny">A lakástakarék-pénztár egy lakáscélú megtakarítási forma. Rendszeresen félretesz, a pénztár pedig fix kamatot és a feltételek teljesítésekor kamatbónuszt ír jóvá.</p>
  </div>
</section>

<section class="szakasz szakasz-feher">
  <div class="tartalom">
    <h2>Hogyan működik?</h2>
    <div class="kettes">
      <article class="kartya">
        <p class="felcim">1. szakasz</p>
        <h3>Megtakarítás</h3>
        <p>Havonta befizet egy előre rögzített összeget. A pénztár erre a szerződésben meghatározott fix kamatot, a feltételek teljesítésekor pedig kamatbónuszt ír jóvá.</p>
      </article>
      <article class="kartya">
        <p class="felcim">2. szakasz</p>
        <h3>Felhasználás</h3>
        <p>A futamidő végén a megtakarítás a kamattal és a kamatbónusszal együtt felvehető, lakáscélra fordítva. A pontos feltételeket a szerződés rögzíti.</p>
      </article>
    </div>
    <ol class="idovonal">
      <li><strong>Szerződéskötés</strong><span>A feltételek rögzítése.</span></li>
      <li><strong>Havi befizetések</strong><span>Kiszámítható, fix összeg.</span></li>
      <li><strong>Lejárat</strong><span>Jóváírásra kerül a kamat és a bónusz.</span></li>
      <li><strong>Felhasználás</strong><span>Az otthonára fordítja.</span></li>
    </ol>
  </div>
</section>

<section class="szakasz">
  <div class="tartalom">
    <h2>Mire használható fel?</h2>
    <p class="alcim">Minden, ami az otthonához kapcsolódik.</p>
    <div class="harmas">
      <article class="kartya"><h3>Lakásvásárlás</h3><p>Új vagy használt ingatlan megvásárlása.</p></article>
      <article class="kartya"><h3>Építés</h3><p>Saját otthon felépítése az alapoktól.</p></article>
      <article class="kartya"><h3>Bővítés</h3><p>Még egy szoba, amikor nő a család.</p></article>
      <article class="kartya"><h3>Felújítás</h3><p>Fürdőszoba, konyha, burkolatok.</p></article>
      <article class="kartya"><h3>Korszerűsítés</h3><p>Szigetelés, nyílászárók, fűtés.</p></article>
      <article class="kartya"><h3>A pontos lista</h3><p>A felhasználási célokat mindig a szerződés rögzíti. Ebben is segítünk.</p></article>
    </div>
  </div>
</section>

<section class="szakasz szakasz-feher">
  <div class="tartalom keskeny">
    <h2>Gyakori kérdések</h2>
    <div class="gyik">
      <details>
        <summary>Mennyi ideig kell takarékoskodni?</summary>
        <p>A megtakarítási időt a szerződés rögzíti. A kalkulátorban három sáv közül választhat: 5 év vagy az alatt, 5 és 10 év között, illetve 10 év vagy fölötte.</p>
      </details>
      <details>
        <summary>Mi történik, ha korábban szükségem van a pénzre?</summary>
        <p>Idő előtti felmondásnál a kamatbónusz elveszhet. A pontos feltételeket a beszélgetés során, még szerződéskötés előtt elmondjuk.</p>
      </details>
      <details>
        <summary>Mi az EBKM, és miért ez alapján rangsoroltok?</summary>
        <p>Az EBKM (Egységesített Betéti Kamatláb Mutató) egy egységes mutató, amely a kamatot, a bónuszt és a költségeket is figyelembe veszi. Ezért ezzel lehet a különböző ajánlatokat igazságosan összehasonlítani.</p>
      </details>
      <details>
        <summary>Mennyibe kerül a számlanyitás?</summary>
        <p>A számlanyitás költségéről és feltételeiről a visszahíváskor, még szerződéskötés előtt pontos tájékoztatást adunk.</p>
      </details>
      <details>
        <summary>Miért nem látok banknevet?</summary>
        <p>Mert a döntés a számokon múlik. Az ajánlatokat kizárólag az EBKM alapján rangsoroljuk, a név nem befolyásolja a sorrendet.</p>
      </details>
    </div>
  </div>
</section>
<?php
oldal_lab();
