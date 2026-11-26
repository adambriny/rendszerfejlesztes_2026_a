<?php
declare(strict_types=1);

require __DIR__ . '/../../src/bootstrap.php';
require __DIR__ . '/../../src/admin_layout.php';

Admin::kotelezo();

$adatok = null;
try {
    $db = Database::kapcsolat($config);
    $adatok = [
        'uj'        => (int) $db->query("SELECT COUNT(*) FROM leads WHERE allapot = 'uj'")->fetchColumn(),
        'osszes'    => (int) $db->query('SELECT COUNT(*) FROM leads')->fetchColumn(),
        'tarifak'   => (int) $db->query('SELECT COUNT(*) FROM tarifak')->fetchColumn(),
        'intezmeny' => (int) $db->query('SELECT COUNT(*) FROM intezmenyek')->fetchColumn(),
    ];
} catch (Throwable $e) {
    error_log('Admin áttekintés hiba: ' . $e->getMessage());
}

admin_fej('Áttekintés', 'index.php');
?>
<section class="szakasz">
  <div class="tartalom">
    <div class="szakasz-fej">
      <h1>Áttekintés</h1>
      <p class="alcim">Üdvözöljük, <?= e(Admin::nev()) ?>.</p>
    </div>
    <?php if ($adatok === null): ?>
      <p class="uzenet uzenet-hiba">Átmeneti hiba miatt az adatok nem tölthetők be.</p>
    <?php else: ?>
      <div class="kettes">
        <a class="kartya admin-csempe" href="leadek.php">
          <p class="felcim">Érdeklődők</p>
          <p class="nagy-szam"><?= e($adatok['uj']) ?></p>
          <p>új érdeklődés, összesen <?= e($adatok['osszes']) ?></p>
        </a>
        <a class="kartya admin-csempe" href="tarifak.php">
          <p class="felcim">Tarifák</p>
          <p class="nagy-szam"><?= e($adatok['tarifak']) ?></p>
          <p>tarifasor, <?= e($adatok['intezmeny']) ?> intézménynél</p>
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>
<?php
admin_lab();
