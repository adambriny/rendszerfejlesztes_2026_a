<?php
declare(strict_types=1);

use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . '/vendor/PHPMailer/Exception.php';
require_once __DIR__ . '/vendor/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/vendor/PHPMailer/SMTP.php';

/*
 * E-mail értesítés a megrendelőnek minden új érdeklődésről (#22).
 * A küldés SMTP-n keresztül, PHPMailer könyvtárral történik.
 */
final class Ertesito
{
    public function __construct(private array $beallitas)
    {
    }

    /**
     * Elküldi az értesítést. Hiba esetén kivételt dob, a hívó dönti el, mi legyen.
     * Az adatok már az adatbázisban vannak, így egy sikertelen küldés miatt nem vész el érdeklődés.
     */
    public function kuld(int $leadId, array $valasztott, array $szemelyes, string $intezmeny): void
    {
        $b = $this->beallitas;
        $a = $valasztott['ajanlat'];

        $sorok = [
            'Név'                         => $szemelyes['nev'],
            'E-mail'                      => $szemelyes['email'],
            'Telefonszám'                 => $szemelyes['telefonszam'],
            'Intézmény'                   => $intezmeny,
            'Módozat'                     => $a['modozat'],
            'Havi megtakarítás'           => self::ft((int) $a['havi_megtakaritas']),
            'Havi befizetések száma'      => $a['befizetesek_szama'] . ' hó',
            'Befizetett összeg'           => self::ft((int) $a['osszes_befizetes']),
            'Lejáratkor felvehető összeg' => self::ft((int) $a['lejaratkori_osszeg']),
            'EBKM'                        => number_format((float) $a['ebkm'], 2, ',', '') . '%',
            'Helyezés a kalkulátorban'    => $a['helyezes'] . '. helyezett',
            'Jelenlegi bank'              => Bankok::LISTA[$valasztott['bank']] ?? 'Ismeretlen',
            'Érdeklődés azonosítója'      => '#' . $leadId,
        ];

        $mail = new PHPMailer(true);
        $mail->CharSet = PHPMailer::CHARSET_UTF8;
        $mail->isSMTP();
        $mail->Host = $b['host'];
        $mail->Port = (int) $b['port'];
        $mail->SMTPAuth = $b['felhasznalo'] !== '';
        $mail->Username = $b['felhasznalo'];
        $mail->Password = $b['jelszo'];
        $mail->SMTPSecure = $b['titkositas'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS
            : ($b['titkositas'] === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : '');
        $mail->SMTPAutoTLS = $b['titkositas'] !== '';
        $mail->Timeout = 15;

        $mail->setFrom($b['felhasznalo'] ?: $b['cimzett'], $b['felado_nev']);
        $mail->addAddress($b['cimzett']);
        // A megrendelő a "Válasz" gombbal közvetlenül az érdeklődőnek írhat.
        $mail->addReplyTo($szemelyes['email'], $szemelyes['nev']);

        $mail->Subject = 'Új érdeklődés: ' . $szemelyes['nev'] . ', ' . $intezmeny;
        $mail->isHTML(true);
        $mail->Body = self::html($sorok);
        $mail->AltBody = self::szoveg($sorok);

        $mail->send();
    }

    // Minden érték escape-elve kerül a HTML levélbe, így a beírt szöveg nem futhat kódként.
    private static function html(array $sorok): string
    {
        $tabla = '';
        foreach ($sorok as $cimke => $ertek) {
            $tabla .= '<tr><td style="padding:6px 16px 6px 0;color:#555">' . self::e($cimke) . '</td>'
                . '<td style="padding:6px 0;font-weight:600">' . self::e((string) $ertek) . '</td></tr>';
        }
        return '<div style="font-family:Arial,sans-serif;font-size:15px;color:#111">'
            . '<h2 style="margin:0 0 12px">Új érdeklődés érkezett a kamatbonusz.hu oldalról</h2>'
            . '<table style="border-collapse:collapse">' . $tabla . '</table>'
            . '<p style="color:#888;font-size:13px">Az érdeklődő hozzájárult adatai kezeléséhez a kapcsolatfelvétel céljából.</p>'
            . '</div>';
    }

    private static function szoveg(array $sorok): string
    {
        $szoveg = "Új érdeklődés érkezett a kamatbonusz.hu oldalról\n\n";
        foreach ($sorok as $cimke => $ertek) {
            $szoveg .= $cimke . ': ' . $ertek . "\n";
        }
        return $szoveg;
    }

    private static function e(string $ertek): string
    {
        return htmlspecialchars($ertek, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private static function ft(int $osszeg): string
    {
        return number_format($osszeg, 0, ',', ' ') . ' Ft';
    }
}
