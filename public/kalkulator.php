<?php
declare(strict_types=1);

require __DIR__ . '/../src/bootstrap.php';
require __DIR__ . '/../src/layout.php';

// A választható értékek egy helyről jönnek, így a kalkulátor és a szerver oldali ellenőrzés mindig egyezik.
$haviOsszegek = range(AjanlatKereso::HAVI_MIN, AjanlatKereso::HAVI_MAX, AjanlatKereso::HAVI_LEPES);
$alapHavi = 50000;
$bankok = ['erste' => 'Erste', 'otp' => 'OTP', 'egyeb' => 'Egyéb bank'];

oldal_fej(
    'Lakástakarék kalkulátor',
    'Számolja ki három kérdésben, mennyit érhet a megtakarítása lakástakarék-pénztárban, banknevek nélkül, EBKM szerint rangsorolva.',
    'kalkulator.php'
);
?>
<section class="szakasz">
  <div class="tartalom">
    <p class="felcim">Kalkulátor</p>
    <h1>Mennyit érhet a megtakarítása?</h1>
    <p class="alcim">Három kérdés, személyes adat nélkül.</p>

    <noscript>
      <p class="uzenet uzenet-hiba">A kalkulátor használatához engedélyezze a JavaScriptet a böngészőjében.</p>
    </noscript>

    <form id="kalkulator" class="kartya kalkulator" action="kalkulator.php" method="get" novalidate>

      <div class="kerdes">
        <label class="kerdes-cim" for="havi">1. Mennyit tenne félre havonta?</label>
        <!-- JavaScript nélkül ez a lista működik, JavaScripttel görgethető kerék váltja fel. -->
        <select id="havi" name="havi" class="kerek-forras">
          <?php foreach ($haviOsszegek as $osszeg): ?>
            <option value="<?= $osszeg ?>"<?= $osszeg === $alapHavi ? ' selected' : '' ?>><?= e(ft($osszeg)) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <fieldset class="kerdes">
        <legend class="kerdes-cim">2. Mennyi ideig takarékoskodna?</legend>
        <div class="szegmens">
          <?php foreach (AjanlatKereso::SAVOK as $szam => $sav): ?>
            <label>
              <input type="radio" name="sav" value="<?= $szam ?>" required>
              <span><?= e($sav['nev']) ?></span>
            </label>
          <?php endforeach; ?>
        </div>
        <p class="mezo-hiba" id="sav-hiba" hidden>Kérjük, válassza ki a futamidőt.</p>
      </fieldset>

      <div class="kerdes">
        <label class="kerdes-cim" for="bank">3. Hol vezeti a bankszámláját?</label>
        <select id="bank" name="bank" class="legordulo" required>
          <option value="" selected disabled>Válasszon</option>
          <?php foreach ($bankok as $kod => $nev): ?>
            <option value="<?= e($kod) ?>"><?= e($nev) ?></option>
          <?php endforeach; ?>
        </select>
        <p class="mezo-hiba" id="bank-hiba" hidden>Kérjük, válassza ki a bankját.</p>
      </div>

      <button type="submit" class="gomb gomb-teljes">Kalkuláció</button>
    </form>

    <div id="eredmeny" class="eredmeny" aria-live="polite"></div>
  </div>
</section>
<?php
oldal_lab(['assets/js/kerek.js', 'assets/js/kalkulator.js']);
