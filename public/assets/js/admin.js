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
