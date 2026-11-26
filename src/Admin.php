<?php
declare(strict_types=1);

/*
 * Az admin felület bejelentkezése (#23).
 * A jelszó csak hash-elve van az adatbázisban, a bejelentkezést a munkamenet tárolja.
 */
final class Admin
{
    // Ennyi perc tétlenség után a rendszer automatikusan kilépteti az admint.
    private const TETLENSEGI_LIMIT_PERC = 30;

    // Ennyi sikertelen próbálkozás után ennyi percig nem lehet újra próbálkozni.
    private const MAX_HIBAS_PROBA = 5;
    private const ZAROLAS_PERC = 5;

    /** Be van-e jelentkezve az admin. A tétlenségi idő lejártakor ki is lépteti. */
    public static function bejelentkezve(): bool
    {
        if (empty($_SESSION['admin'])) {
            return false;
        }
        if (time() - $_SESSION['admin']['utolso_aktivitas'] > self::TETLENSEGI_LIMIT_PERC * 60) {
            self::kijelentkezes();
            return false;
        }
        $_SESSION['admin']['utolso_aktivitas'] = time();
        return true;
    }

    /** Minden admin oldal ezzel kezdődik: bejelentkezés nélkül a belépő oldalra irányít. */
    public static function kotelezo(): void
    {
        if (!self::bejelentkezve()) {
            header('Location: belepes.php', true, 303);
            exit;
        }
    }

    public static function nev(): string
    {
        return (string) ($_SESSION['admin']['felhasznalonev'] ?? '');
    }

    /** Hátralévő zárolási idő másodpercben (0, ha lehet próbálkozni). */
    public static function zarolva(): int
    {
        $ig = (int) ($_SESSION['belepes_zarolva_ig'] ?? 0);
        return max(0, $ig - time());
    }

    /**
     * Felhasználónév és jelszó ellenőrzése. Siker esetén új munkamenet-azonosítót kap a
     * böngésző, így egy korábban ellopott azonosító a belépés után már nem használható.
     */
    public static function bejelentkezes(PDO $db, string $felhasznalonev, string $jelszo): bool
    {
        $stmt = $db->prepare('SELECT id, felhasznalonev, jelszo_hash FROM admin_felhasznalok WHERE felhasznalonev = :nev');
        $stmt->execute(['nev' => $felhasznalonev]);
        $sor = $stmt->fetch();

        // Nem létező felhasználónál is lefut egy jelszó-ellenőrzés, hogy a válaszidőből
        // ne lehessen kitalálni, létezik-e az adott felhasználónév.
        $hash = $sor['jelszo_hash'] ?? '$2y$10$N6Rl4kt.ll/sYRqN/hf3g.xRvHtlS3MZmqNEgV69e2mEub6EUPCZe';
        $jo = password_verify($jelszo, $hash) && $sor !== false;

        if (!$jo) {
            $_SESSION['belepes_hibak'] = (int) ($_SESSION['belepes_hibak'] ?? 0) + 1;
            if ($_SESSION['belepes_hibak'] >= self::MAX_HIBAS_PROBA) {
                $_SESSION['belepes_zarolva_ig'] = time() + self::ZAROLAS_PERC * 60;
                $_SESSION['belepes_hibak'] = 0;
            }
            // Rövid várakozás: a jelszó gépi találgatását jelentősen lassítja.
            sleep(1);
            return false;
        }

        // Ha a PHP időközben erősebb titkosítást használ alapból, a hash frissül.
        if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
            $db->prepare('UPDATE admin_felhasznalok SET jelszo_hash = :h WHERE id = :id')
               ->execute(['h' => password_hash($jelszo, PASSWORD_DEFAULT), 'id' => $sor['id']]);
        }

        session_regenerate_id(true);
        unset($_SESSION['belepes_hibak'], $_SESSION['belepes_zarolva_ig']);
        $_SESSION['admin'] = [
            'id'               => (int) $sor['id'],
            'felhasznalonev'   => $sor['felhasznalonev'],
            'utolso_aktivitas' => time(),
        ];
        return true;
    }

    public static function kijelentkezes(): void
    {
        unset($_SESSION['admin']);
        session_regenerate_id(true);
    }
}
