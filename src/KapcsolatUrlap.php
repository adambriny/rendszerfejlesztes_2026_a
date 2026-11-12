<?php
declare(strict_types=1);

/*
 * A kapcsolati űrlap szerver oldali ellenőrzése (#21).
 * A böngészőben futó ellenőrzés kikapcsolható, ezért minden mezőt itt is ellenőrzünk.
 */
final class KapcsolatUrlap
{
    /** A megtisztított, ellenőrzött értékek. */
    public array $adatok = ['nev' => '', 'email' => '', 'telefonszam' => ''];

    /** Mezőnként a hibaüzenet (mezőnév => szöveg). */
    public array $hibak = [];

    public function __construct(array $bemenet)
    {
        $this->adatok['nev'] = self::szoveg($bemenet['nev'] ?? '');
        $this->adatok['email'] = self::szoveg($bemenet['email'] ?? '');
        $this->adatok['telefonszam'] = self::szoveg($bemenet['telefonszam'] ?? '');

        $this->nevEllenorzes();
        $this->emailEllenorzes();
        $this->telefonEllenorzes();

        if (($bemenet['adatkezeles'] ?? '') !== '1') {
            $this->hibak['adatkezeles'] = 'A visszahíváshoz el kell fogadnia az Adatkezelési nyilatkozatot.';
        }
    }

    public function ervenyes(): bool
    {
        return $this->hibak === [];
    }

    /**
     * Honeypot: egy ember számára láthatatlan mező. Ha ki van töltve, a beküldést
     * egy automatikus program (spam robot) végezte.
     */
    public static function robot(array $bemenet): bool
    {
        return trim((string) ($bemenet['weboldal'] ?? '')) !== '';
    }

    private static function szoveg(mixed $ertek): string
    {
        // Tömb vagy más típus helyett üres szöveg, a felesleges szóközök eltávolítva.
        return is_string($ertek) ? trim(preg_replace('/\s+/u', ' ', $ertek) ?? '') : '';
    }

    private function nevEllenorzes(): void
    {
        $nev = $this->adatok['nev'];
        if ($nev === '') {
            $this->hibak['nev'] = 'Kérjük, adja meg a nevét.';
        } elseif (mb_strlen($nev) < 2 || mb_strlen($nev) > 100) {
            $this->hibak['nev'] = 'A név 2 és 100 karakter közötti lehet.';
        } elseif (!preg_match("/^\p{L}[\p{L} .'\-]*$/u", $nev)) {
            $this->hibak['nev'] = 'A név csak betűket, szóközt, pontot és kötőjelet tartalmazhat.';
        }
    }

    private function emailEllenorzes(): void
    {
        $email = $this->adatok['email'];
        if ($email === '') {
            $this->hibak['email'] = 'Kérjük, adja meg az e-mail címét.';
        } elseif (strlen($email) > 254 || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->hibak['email'] = 'Kérjük, valós e-mail címet adjon meg, például: pelda@email.hu';
        }
    }

    private function telefonEllenorzes(): void
    {
        $telefon = $this->adatok['telefonszam'];
        // A szokásos elválasztók (szóköz, kötőjel, zárójel, perjel) elhagyhatók, a + csak az elején állhat.
        $tiszta = preg_replace('/[\s\-\/()]/', '', $telefon) ?? '';
        if ($telefon === '') {
            $this->hibak['telefonszam'] = 'Kérjük, adja meg a telefonszámát.';
        } elseif (!preg_match('/^\+?\d{9,15}$/', $tiszta)) {
            $this->hibak['telefonszam'] = 'Kérjük, valós telefonszámot adjon meg, például: +36 30 123 4567';
        } else {
            $this->adatok['telefonszam'] = $tiszta;
        }
    }
}
