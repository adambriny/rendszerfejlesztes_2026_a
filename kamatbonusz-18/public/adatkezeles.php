<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

/*
 * FIGYELEM: a szögletes zárójeles részek helyére éles indítás előtt a megrendelő
 * valós adatai kerülnek, és a szöveget jogász nézi át.
 */

oldal_fej(
    'Adatkezelési nyilatkozat',
    'Milyen adatokat kezel a kamatbonusz.hu, miért, meddig, és milyen jogok illetik meg Önt.',
    'adatkezeles.php'
);
?>
<section class="szakasz">
  <div class="tartalom keskeny szoveg">
    <h1>Adatkezelési nyilatkozat</h1>
    <p class="alcim">Hatályos: [HATÁLYBALÉPÉS DÁTUMA]</p>
    <p>Ez a nyilatkozat bemutatja, milyen adatokat kezelünk, miért, meddig, és milyen jogok illetik meg Önt.</p>

    <h2>1. Az adatkezelő</h2>
    <ul class="adatlista">
      <li>Név: [ADATKEZELŐ NEVE]</li>
      <li>Székhely: [SZÉKHELY]</li>
      <li>Nyilvántartási szám: [NYILVÁNTARTÁSI SZÁM]</li>
      <li>Adószám: [ADÓSZÁM]</li>
      <li>E-mail: [ADATVÉDELMI E-MAIL CÍM]</li>
    </ul>

    <h2>2. Milyen adatokat kezelünk?</h2>
    <ul>
      <li>Név, e-mail cím és telefonszám, amelyet a kapcsolati űrlapon megad.</li>
      <li>A kalkulátorban adott válaszai (havi összeg, futamidő, jelenlegi bank) és a választott ajánlat adatai, ha visszahívást kér.</li>
    </ul>
    <p>A kalkulátor használatához nem kérünk személyes adatot. Ha nem kér visszahívást, a válaszait nem tároljuk.</p>

    <h2>3. Az adatkezelés célja és jogalapja</h2>
    <p>Az adatokat kizárólag a kért visszahívás és a megtakarítási lehetőségek bemutatása céljából kezeljük. Jogalap: az Ön hozzájárulása (GDPR 6. cikk (1) bekezdés a) pont), amelyet bármikor visszavonhat.</p>

    <h2>4. Az adatkezelés időtartama</h2>
    <p>Az adatokat [MEGŐRZÉSI IDŐ] ideig, vagy hozzájárulása visszavonásáig őrizzük meg, ezt követően töröljük.</p>

    <h2>5. Adattovábbítás</h2>
    <p>Adatait kizárólag az Ön kifejezett kérésére és hozzájárulásával továbbítjuk az ajánlatot nyújtó pénzügyi intézmény felé. Adatfeldolgozók: [ADATFELDOLGOZÓK LISTÁJA, például tárhelyszolgáltató és e-mail szolgáltató].</p>

    <h2>6. Az Ön jogai</h2>
    <ul>
      <li>Tájékoztatást és hozzáférést kérhet az adataihoz.</li>
      <li>Kérheti adatai helyesbítését vagy törlését.</li>
      <li>Kérheti az adatkezelés korlátozását.</li>
      <li>Élhet az adathordozhatósághoz való jogával.</li>
      <li>Tiltakozhat az adatkezelés ellen, és bármikor visszavonhatja hozzájárulását.</li>
    </ul>

    <h2>7. Jogorvoslat</h2>
    <p>Panaszával először forduljon hozzánk a fenti e-mail címen. Ezen túl a Nemzeti Adatvédelmi és Információszabadság Hatósághoz (NAIH, <a href="https://www.naih.hu" rel="noopener" target="_blank">www.naih.hu</a>) vagy bírósághoz fordulhat.</p>
  </div>
</section>
<?php
oldal_lab();
