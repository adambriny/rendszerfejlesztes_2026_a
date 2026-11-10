/*
 * Görgethető számkerék (az iPhone időzítő mintájára).
 * Egy meglévő <select class="kerek-forras"> elemet alakít át: a kerék csak megjelenítés,
 * az érték mindig a rejtett <select>-ben van, így az űrlap JavaScript nélkül is működik.
 */
(function () {
  'use strict';

  const SOR_MAGASSAG = 44; // px, egyeznie kell a CSS-ben megadott értékkel

  function kerekLetrehozasa(select) {
    const opciok = Array.from(select.options);

    const kerek = document.createElement('div');
    kerek.className = 'kerek';
    kerek.tabIndex = 0;
    kerek.setAttribute('role', 'listbox');
    kerek.setAttribute('aria-label', select.labels[0] ? select.labels[0].textContent : 'Érték kiválasztása');

    const lista = document.createElement('ul');
    lista.className = 'kerek-lista';
    opciok.forEach(function (opcio, index) {
      const elem = document.createElement('li');
      elem.className = 'kerek-elem';
      elem.textContent = opcio.textContent;
      elem.setAttribute('role', 'option');
      elem.id = select.id + '-opcio-' + index;
      elem.addEventListener('click', function () { ugras(index, true); });
      lista.appendChild(elem);
    });
    kerek.appendChild(lista);

    select.classList.add('rejtett');
    select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    select.insertAdjacentElement('afterend', kerek);

    const elemek = lista.children;

    function kijeloles(index) {
      Array.from(elemek).forEach(function (elem, i) {
        const aktiv = i === index;
        elem.classList.toggle('kerek-aktiv', aktiv);
        elem.setAttribute('aria-selected', aktiv ? 'true' : 'false');
      });
      kerek.setAttribute('aria-activedescendant', elemek[index].id);
      if (select.selectedIndex !== index) {
        select.selectedIndex = index;
        select.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }

    function ugras(index, simitott) {
      index = Math.max(0, Math.min(opciok.length - 1, index));
      kerek.scrollTo({ top: index * SOR_MAGASSAG, behavior: simitott ? 'smooth' : 'auto' });
      kijeloles(index);
    }

    // Görgetés közben folyamatosan frissítjük a kijelölést, a középső sor a kiválasztott.
    let idozito = null;
    kerek.addEventListener('scroll', function () {
      const index = Math.round(kerek.scrollTop / SOR_MAGASSAG);
      if (elemek[index]) { kijeloles(index); }
      clearTimeout(idozito);
      idozito = setTimeout(function () { ugras(select.selectedIndex, true); }, 150);
    }, { passive: true });

    kerek.addEventListener('keydown', function (esemeny) {
      const lepes = { ArrowUp: -1, ArrowDown: 1, PageUp: -3, PageDown: 3 }[esemeny.key];
      if (lepes) {
        esemeny.preventDefault();
        ugras(select.selectedIndex + lepes, true);
      } else if (esemeny.key === 'Home' || esemeny.key === 'End') {
        esemeny.preventDefault();
        ugras(esemeny.key === 'Home' ? 0 : opciok.length - 1, true);
      }
    });

    ugras(select.selectedIndex, false);
  }

  document.querySelectorAll('select.kerek-forras').forEach(kerekLetrehozasa);
})();
