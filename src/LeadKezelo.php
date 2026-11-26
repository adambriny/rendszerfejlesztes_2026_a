<?php
declare(strict_types=1);

/*
 * Az érdeklődések (leadek) kezelése az admin felületen (#25):
 * listázás legújabbal kezdve, állapotváltás, végleges törlés (GDPR).
 */
final class LeadKezelo
{
    /** Az adatbázis ENUM értékei és a képernyőn látható nevük. */
    public const ALLAPOTOK = [
        'uj'     => 'Új',
        'lezart' => 'Lezárt',
    ];

    public const OLDALMERET = 20;

    public function __construct(private PDO $db)
    {
    }

    /** Darabszám állapotonként, a szűrőgombokhoz: ['uj' => 3, 'lezart' => 5, 'osszes' => 8]. */
    public function darabok(): array
    {
        $eredmeny = ['uj' => 0, 'lezart' => 0];
        foreach ($this->db->query('SELECT allapot, COUNT(*) AS db FROM leads GROUP BY allapot') as $sor) {
            $eredmeny[$sor['allapot']] = (int) $sor['db'];
        }
        $eredmeny['osszes'] = $eredmeny['uj'] + $eredmeny['lezart'];
        return $eredmeny;
    }

    /**
     * Egy oldalnyi érdeklődés, a legújabbal kezdve.
     * Az id másodlagos rendezés: azonos másodpercben érkezett két lead sorrendje is stabil marad.
     */
    public function lista(?string $allapot, int $oldal): array
    {
        $feltetel = '';
        $parameterek = [];
        if ($allapot !== null) {
            $feltetel = 'WHERE l.allapot = :allapot';
            $parameterek['allapot'] = $allapot;
        }

        $stmt = $this->db->prepare(
            "SELECT l.*, i.nev AS intezmeny_nev
               FROM leads l
               LEFT JOIN intezmenyek i ON i.id = l.intezmeny_id
               {$feltetel}
              ORDER BY l.beerkezes_datuma DESC, l.id DESC
              LIMIT :limit OFFSET :offset"
        );
        foreach ($parameterek as $nev => $ertek) {
            $stmt->bindValue($nev, $ertek);
        }
        $stmt->bindValue('limit', self::OLDALMERET, PDO::PARAM_INT);
        $stmt->bindValue('offset', max(0, $oldal - 1) * self::OLDALMERET, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** A lead neve a visszajelző üzenethez, vagy null, ha nem létezik. */
    public function nev(int $id): ?string
    {
        $stmt = $this->db->prepare('SELECT nev FROM leads WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $nev = $stmt->fetchColumn();
        return $nev === false ? null : (string) $nev;
    }

    public function allapotValtas(int $id, string $allapot): bool
    {
        if (!array_key_exists($allapot, self::ALLAPOTOK)) {
            throw new InvalidArgumentException('Ismeretlen állapot.');
        }
        $stmt = $this->db->prepare('UPDATE leads SET allapot = :allapot WHERE id = :id');
        $stmt->execute(['allapot' => $allapot, 'id' => $id]);
        return $stmt->rowCount() > 0;
    }

    /** Végleges törlés. Nincs "lomtár": a GDPR szerinti törlési kérés után az adat ne maradjon meg. */
    public function torles(int $id): bool
    {
        $stmt = $this->db->prepare('DELETE FROM leads WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->rowCount() > 0;
    }
}
