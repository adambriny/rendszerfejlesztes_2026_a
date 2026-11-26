<?php
declare(strict_types=1);

/*
 * Az admin felület közös kerete. A nyilvános oldalak keretétől külön van,
 * mert más a menüje, és a keresők elől rejtve marad.
 */

require_once __DIR__ . '/layout.php';

const ADMIN_MENU = [
    'index.php'   => 'Áttekintés',
    'tarifak.php' => 'Tarifák',
    'leadek.php'  => 'Érdeklődők',
];

function admin_fej(string $cim, string $aktiv = ''): void
{
    header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    // Az admin oldalak tartalma személyes adat, a böngésző ne tárolja el.
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
    header('Expires: 0');
    ?>
<!doctype html>
<html lang="hu">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="robots" content="noindex, nofollow">
  <title><?= e($cim) ?> | kamatbonusz.hu admin</title>
  <link rel="stylesheet" href="../assets/css/style.css">
  <script src="../assets/js/admin.js"></script>
</head>
<body class="admin">
  <header class="fejlec">
    <div class="fejlec-belso">
      <a class="logo" href="index.php">kamatbonusz<span>.hu admin</span></a>
      <?php if (Admin::bejelentkezve()): ?>
        <nav class="menu" aria-label="Admin menü">
          <?php foreach (ADMIN_MENU as $url => $nev): ?>
            <a href="<?= e($url) ?>"<?= $url === $aktiv ? ' aria-current="page"' : '' ?>><?= e($nev) ?></a>
          <?php endforeach; ?>
        </nav>
        <form class="fejlec-cta" action="kilepes.php" method="post">
          <input type="hidden" name="csrf" value="<?= e(Csrf::token()) ?>">
          <button type="submit" class="gomb gomb-kicsi">Kijelentkezés</button>
        </form>
      <?php endif; ?>
    </div>
  </header>
  <main>
<?php
}

function admin_lab(): void
{
    ?>
  </main>
  <footer class="lablec">
    <div class="lablec-belso">
      <span>kamatbonusz.hu admin</span>
      <?php if (Admin::bejelentkezve()): ?><span>Bejelentkezve: <?= e(Admin::nev()) ?></span><?php endif; ?>
    </div>
  </footer>
</body>
</html>
<?php
}
