<?php
declare(strict_types=1);

/*
 * Az érdeklődés (lead) mentése az adatbázisba (#22).
 * Az ajánlat adatai a munkamenetben tárolt keresési eredményből jönnek,
 * a személyes adatok a már ellenőrzött kapcsolati űrlapból.
 */
final class LeadMentes
{
    public function __construct(private PDO $db)
    {
    }

    /** Elmenti az érdeklődést "új" állapottal, és visszaadja az azonosítóját. */
    public function ment(array $valasztott, array $szemelyes): int
    {
        $a = $valasztott['ajanlat'];

        $stmt = $this->db->prepare(
            'INSERT INTO leads
               (nev, email, telefonszam, intezmeny_id, modozat, havi_megtakaritas,
                befizetesek_szama, osszes_befizetes, lejaratkori_osszeg, ebkm, jelenlegi_bank)
             VALUES
               (:nev, :email, :telefonszam, :intezmeny_id, :modozat, :havi,
                :befizetesek, :osszes, :lejaratkori, :ebkm, :bank)'
        );
        $stmt->execute([
            'nev'          => $szemelyes['nev'],
            'email'        => $szemelyes['email'],
            'telefonszam'  => $szemelyes['telefonszam'],
            'intezmeny_id' => (int) $a['intezmeny_id'],
            'modozat'      => $a['modozat'],
            'havi'         => (int) $a['havi_megtakaritas'],
            'befizetesek'  => (int) $a['befizetesek_szama'],
            'osszes'       => (int) $a['osszes_befizetes'],
            'lejaratkori'  => (int) $a['lejaratkori_osszeg'],
            'ebkm'         => number_format((float) $a['ebkm'], 2, '.', ''),
            'bank'         => Bankok::LISTA[$valasztott['bank']] ?? 'Ismeretlen',
        ]);

        return (int) $this->db->lastInsertId();
    }

    /** Az intézmény neve az e-mailhez. A nyilvános oldalakon soha nem jelenik meg. */
    public function intezmenyNev(int $id): string
    {
        $stmt = $this->db->prepare('SELECT nev FROM intezmenyek WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return (string) ($stmt->fetchColumn() ?: 'Ismeretlen intézmény');
    }
}
