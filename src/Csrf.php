<?php
declare(strict_types=1);

/*
 * Védelem a más oldalról indított űrlapbeküldés (CSRF) ellen.
 * Minden POST űrlap tartalmaz egy véletlen tokent, amit csak a mi oldalunk ismer.
 */
final class Csrf
{
    public static function token(): string
    {
        if (empty($_SESSION['csrf'])) {
            $_SESSION['csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf'];
    }

    public static function ervenyes(mixed $token): bool
    {
        return is_string($token)
            && isset($_SESSION['csrf'])
            && hash_equals($_SESSION['csrf'], $token);
    }
}
