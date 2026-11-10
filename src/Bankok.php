<?php
declare(strict_types=1);

/*
 * A kalkulátor 3. kérdésének választható bankjai. A lista egy helyen van,
 * így az űrlap és a szerver oldali ellenőrzés mindig egyezik.
 */
final class Bankok
{
    public const LISTA = [
        'erste' => 'Erste',
        'otp'   => 'OTP',
        'egyeb' => 'Egyéb bank',
    ];

    public static function ervenyes(mixed $kod): bool
    {
        return is_string($kod) && isset(self::LISTA[$kod]);
    }
}
