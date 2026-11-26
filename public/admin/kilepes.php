<?php
declare(strict_types=1);

/*
 * Kijelentkezés. Csak POST kéréssel és érvényes tokennel működik, így egy idegen oldal
 * nem tudja egy rejtett linkkel kiléptetni az admint.
 */

require __DIR__ . '/../../src/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && Csrf::ervenyes($_POST['csrf'] ?? null)) {
    Admin::kijelentkezes();
}

header('Location: belepes.php', true, 303);
exit;
