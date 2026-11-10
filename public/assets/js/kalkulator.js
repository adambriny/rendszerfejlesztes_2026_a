/*
 * Kalkulátor űrlap: ellenőrzi a válaszokat, lekéri az ajánlatokat a szervertől,
 * és ajánlati kártyákként megjeleníti őket. Az ellenőrzést a szerver is elvégzi,
 * ez csak gyorsabb visszajelzés a látogatónak.
 */
(function () {
  'use strict';

  const urlap = document.getElementById('kalkulator');
  const eredmeny = document.getElementById('eredmeny');
  if (!urlap || !eredmeny) { return; }

  const csrf = urlap.dataset.csrf;
  const bankMezo = document.getElementById('bank');
  const forint = new Intl.NumberFormat('hu-HU', { maximumFractionDigits: 0 });
  const ft = function (szam) { return forint.format(szam) + ' Ft'; };

  function mezoHiba(id, lathato) {
    document.getElementById(id).hidden = !lathato;
  }

  // Szövegként, nem HTML-ként tesszük az oldalra az adatot, így semmilyen beérkező szöveg nem futhat kódként.
  function elem(tag, osztaly, szoveg) {
    const e = document.createElement(tag);
    if (osztaly) { e.className = osztaly; }
    if (szoveg !== undefined) { e.textContent = szoveg; }
    return e;
  }

  function rejtettMezo(nev, ertek) {
    const input = elem('input');
    input.type = 'hidden';
    input.name = nev;
    input.value = ertek;
    return input;
  }

  function uzenet(szoveg, hiba) {
    eredmeny.replaceChildren(elem('p', 'uzenet' + (hiba ? ' uzenet-hiba' : ''), szoveg));
  }

  function adatSor(cimke, ertek) {
    const sor = elem('div', 'ajanlat-adat');
    sor.append(elem('dt', null, cimke), elem('dd', null, ertek));
    return sor;
  }

  function kartya(a) {
    const k = elem('article', 'ajanlat-kartya' + (a.helyezes === 1 ? ' ajanlat-elso' : ''));
    const adatok = elem('dl', 'ajanlat-adatok');
    adatok.append(
      adatSor('Havi megtakarítás', ft(a.havi_megtakaritas)),
      adatSor('Havi befizetések száma', a.befizetesek_szama + ' hó'),
      adatSor('Befizetett összeg', ft(a.befizetett_osszeg)),
      adatSor('Kamat és kamatbónusz', '+' + ft(a.lejaratkori_osszeg - a.befizetett_osszeg))
    );

    // A kiválasztás csak a helyezést küldi el, az ajánlat adatait a szerver a saját tárolt eredményéből veszi.
    const valaszto = elem('form');
    valaszto.method = 'post';
    valaszto.action = 'valasztas.php';
    valaszto.append(
      rejtettMezo('csrf', csrf),
      rejtettMezo('helyezes', a.helyezes),
      rejtettMezo('bank', bankMezo.value)
    );
    const gomb = elem('button', 'gomb gomb-teljes' + (a.helyezes === 1 ? ' gomb-vilagos' : ''), 'Ezt választom');
    gomb.type = 'submit';
    gomb.setAttribute('aria-label', 'Ezt választom: ' + a.helyezes + '. helyezett');
    valaszto.appendChild(gomb);

    k.append(
      elem('span', 'cimke', a.helyezes + '. helyezett'),
      elem('p', 'ajanlat-felirat', 'Lejáratkor felvehető összeg'),
      elem('p', 'ajanlat-osszeg', ft(a.lejaratkori_osszeg)),
      adatok,
      valaszto
    );
    return k;
  }

  function megjelenites(adat) {
    const racs = elem('div', 'ajanlat-racs');
    adat.ajanlatok.forEach(function (a) { racs.appendChild(kartya(a)); });
    eredmeny.replaceChildren(
      elem('h2', null, 'Az Ön legjobb lehetőségei.'),
      elem('p', 'alcim', ft(adat.havi_megtakaritas) + '/hó, ' + adat.futamido_sav + '. EBKM szerint rangsorolva, banknevek nélkül.'),
      racs,
      elem('p', 'megjegyzes', 'Tájékoztató jellegű számítás. A pontos feltételeket szerződéskötés előtt minden esetben egyeztetjük.')
    );
  }

  // Ha a látogató a kártyák megjelenése után módosítja a bankot, a kártyák már az újat küldik el.
  bankMezo.addEventListener('change', function () {
    eredmeny.querySelectorAll('input[name="bank"]').forEach(function (input) { input.value = bankMezo.value; });
  });

  urlap.addEventListener('submit', async function (esemeny) {
    esemeny.preventDefault();

    const adatok = new FormData(urlap);
    const savOk = adatok.get('sav') !== null;
    const bankOk = Boolean(adatok.get('bank'));
    mezoHiba('sav-hiba', !savOk);
    mezoHiba('bank-hiba', !bankOk);
    if (!savOk || !bankOk) { return; }

    const gomb = urlap.querySelector('button[type="submit"]');
    gomb.disabled = true;
    uzenet('Számolunk...', false);

    try {
      const parameterek = new URLSearchParams({ havi: adatok.get('havi'), sav: adatok.get('sav') });
      const valasz = await fetch('api/ajanlatok.php?' + parameterek.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin'
      });
      const adat = await valasz.json();
      if (!valasz.ok) { throw new Error(adat.hiba || 'Ismeretlen hiba'); }
      megjelenites(adat);
      eredmeny.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } catch (hiba) {
      uzenet(hiba.message && hiba.message !== 'Failed to fetch'
        ? hiba.message
        : 'Átmeneti hiba történt, kérjük próbálja újra később.', true);
    } finally {
      gomb.disabled = false;
    }
  });
})();
