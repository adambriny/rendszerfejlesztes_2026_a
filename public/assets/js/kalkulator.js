/*
 * Kalkulátor űrlap: ellenőrzi a válaszokat, lekéri az ajánlatokat a szervertől,
 * és megjeleníti őket. Az ellenőrzést a szerver is elvégzi, ez csak gyorsabb visszajelzés.
 */
(function () {
  'use strict';

  const urlap = document.getElementById('kalkulator');
  const eredmeny = document.getElementById('eredmeny');
  if (!urlap || !eredmeny) { return; }

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

  function uzenet(szoveg, hiba) {
    eredmeny.replaceChildren(elem('p', 'uzenet' + (hiba ? ' uzenet-hiba' : ''), szoveg));
  }

  function megjelenites(adat) {
    const lista = elem('ol', 'ajanlat-lista');
    adat.ajanlatok.forEach(function (a) {
      const sor = elem('li', 'ajanlat-sor');
      sor.append(
        elem('strong', null, a.helyezes + '. helyezett'),
        elem('span', null, 'Lejáratkor felvehető: ' + ft(a.lejaratkori_osszeg)),
        elem('span', null, a.befizetesek_szama + ' hónap, befizetett összeg: ' + ft(a.befizetett_osszeg))
      );
      lista.appendChild(sor);
    });
    eredmeny.replaceChildren(
      elem('h2', null, 'Az Ön legjobb lehetőségei'),
      elem('p', 'alcim', ft(adat.havi_megtakaritas) + '/hó, ' + adat.futamido_sav + '. EBKM szerint rangsorolva, banknevek nélkül.'),
      lista
    );
  }

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
