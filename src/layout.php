<?php
declare(strict_types=1);

/*
 * Közös oldalkeret: fejléc, menü és lábléc. Minden nyilvános oldal ezt használja,
 * így a menü és a megjelenés egy helyen módosítható.
 */

/** Kimenet escape-elése HTML-be (XSS elleni védelem). Minden változó csak ezen keresztül kerül az oldalra. */
function e(string|int|float|null $ertek): string
{
    return htmlspecialchars((string) $ertek, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Forint formátum: 1 234 567 Ft (nem törhető szóközzel, hogy a szám ne essen szét két sorba). */
function ft(int $osszeg): string
{
    return number_format($osszeg, 0, ',', "\u{00A0}") . "\u{00A0}Ft";
}

// A kalkulátor nem menüpont, hanem a jobb felső kiemelt gomb vezet oda.
const MENU = [
    'ismerteto.php' => 'Ismertető',
    'garanciak.php' => 'Garanciák',
    'kapcsolat.php' => 'Kapcsolat',
];

function oldal_fej(string $cim, string $leiras, string $aktiv = ''): void
{
    // Biztonsági fejlécek: csak a saját domainről tölthető script és stílus, más oldal nem ágyazhat be minket.
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    ?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($cim) ?> | kamatbonusz.hu</title>
  <meta name="description" content="<?= e($leiras) ?>">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
  <header class="fejlec">
    <div class="fejlec-belso">
      <a class="logo" href="index.php">kamatbonusz<span>.hu</span></a>
      <nav class="menu" aria-label="Főmenü">
        <?php foreach (MENU as $url => $nev): ?>
          <a href="<?= e($url) ?>"<?= $url === $aktiv ? ' aria-current="page"' : '' ?>><?= e($nev) ?></a>
        <?php endforeach; ?>
      </nav>
      <a class="gomb gomb-kicsi fejlec-cta" href="kalkulator.php"<?= $aktiv === 'kalkulator.php' ? ' aria-current="page"' : '' ?>>Kalkuláció indítása</a>
    </div>
  </header>
  <main>
<?php
}

function oldal_lab(array $scriptek = []): void
{
    ?>
  </main>
  <footer class="lablec">
    <div class="lablec-belso">
      <span>&copy; <?= date('Y') ?> kamatbonusz.hu</span>
      <nav aria-label="Lábléc">
        <a href="adatkezeles.php">Adatkezelési nyilatkozat</a>
        <a href="kapcsolat.php">Kapcsolat</a>
      </nav>
    </div>
  </footer>
  <?php foreach ($scriptek as $script): ?>
  <script src="<?= e($script) ?>" defer></script>
  <?php endforeach; ?>
</body>
</html>
<?php
}
