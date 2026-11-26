<?php
declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';
require __DIR__ . '/../../src/admin_layout.php';

if (Admin::bejelentkezve()) {
    header('Location: index.php', true, 303);
    exit;
}

$hiba = null;
$felhasznalonev = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $felhasznalonev = is_string($_POST['felhasznalonev'] ?? null) ? trim($_POST['felhasznalonev']) : '';
    $jelszo = is_string($_POST['jelszo'] ?? null) ? $_POST['jelszo'] : '';

    if (!Csrf::ervenyes($_POST['csrf'] ?? null)) {
        $hiba = 'A munkamenet lejárt. Kérjük, próbálja újra.';
    } elseif (Admin::zarolva() > 0) {
        $hiba = 'Túl sok sikertelen próbálkozás. Kérjük, várjon ' . (int) ceil(Admin::zarolva() / 60) . ' percet.';
    } elseif ($felhasznalonev === '' || $jelszo === '') {
        $hiba = 'Kérjük, adja meg a felhasználónevet és a jelszót.';
    } else {
        try {
            if (Admin::bejelentkezes(Database::kapcsolat($config), $felhasznalonev, $jelszo)) {
                header('Location: index.php', true, 303);
                exit;
            }
            // Szándékosan nem mondjuk meg, melyik adat hibás.
            $hiba = Admin::zarolva() > 0
                ? 'Túl sok sikertelen próbálkozás. Kérjük, várjon ' . (int) ceil(Admin::zarolva() / 60) . ' percet.'
                : 'Hibás felhasználónév vagy jelszó.';
        } catch (Throwable $e) {
            error_log('Admin bejelentkezési hiba: ' . $e->getMessage());
            $hiba = 'Átmeneti hiba történt, kérjük próbálja újra később.';
        }
    }
}

admin_fej('Bejelentkezés');
?>
<section class="szakasz">
  <div class="tartalom belepes">
    <div class="kartya">
      <h1 class="kartya-cim">Admin bejelentkezés</h1>
      <?php if ($hiba !== null): ?>
        <p class="uzenet uzenet-hiba" role="alert"><?= e($hiba) ?></p>
      <?php endif; ?>
      <form class="urlap" action="belepes.php" method="post">
        <input type="hidden" name="csrf" value="<?= e(Csrf::token()) ?>">
        <div class="mezo">
          <label for="felhasznalonev">Felhasználónév</label>
          <input id="felhasznalonev" name="felhasznalonev" type="text" autocomplete="username" maxlength="50" required
                 value="<?= e($felhasznalonev) ?>" autofocus>
        </div>
        <div class="mezo">
          <label for="jelszo">Jelszó</label>
          <input id="jelszo" name="jelszo" type="password" autocomplete="current-password" maxlength="200" required>
        </div>
        <button type="submit" class="gomb gomb-teljes">Belépés</button>
      </form>
    </div>
  </div>
</section>
<?php
admin_lab();
