/*
 * Az admin oldalak a "Vissza" gombbal se jelenjenek meg a böngésző memóriájából.
 * Ha a böngésző egy korábban megjelenített oldalt mutatna vissza (például kijelentkezés után),
 * újratöltjük, így a szerver dönti el, be van-e még jelentkezve az admin.
 */
window.addEventListener('pageshow', function (esemeny) {
  if (esemeny.persisted) {
    window.location.reload();
  }
});

/*
 * Megerősítés a visszavonhatatlan műveletek előtt (például törlés).
 * A szöveget az űrlap data-megerosites attribútuma adja.
 */
document.addEventListener('submit', function (esemeny) {
  const szoveg = esemeny.target.getAttribute('data-megerosites');
  if (szoveg && !window.confirm(szoveg)) {
    esemeny.preventDefault();
  }
});

// Az "Új intézmény neve" mező csak akkor látszik, ha az "Új intézmény..." van kiválasztva.
document.addEventListener('DOMContentLoaded', function () {
  const valaszto = document.getElementById('intezmeny');
  const mezo = document.getElementById('uj-nev-mezo');
  if (!valaszto || !mezo) { return; }
  const frissit = function () {
    const uj = valaszto.value === 'uj';
    mezo.hidden = !uj;
    document.getElementById('uj_nev').required = uj;
  };
  valaszto.addEventListener('change', frissit);
  frissit();
});
