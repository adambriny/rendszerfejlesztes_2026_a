<?php
declare(strict_types=1);

/*
 * Tarifák kezelése az admin felületen (#24): áttekintés, csere, törlés.
 * Minden módosítás azonnal érvényes a kalkulátorban, mert az mindig közvetlenül
 * az adatbázisból keres.
 */
final class TarifaKezelo
{
    public function __construct(private PDO $db)
    {
    }

    /** Intézményenként a tarifák összesítése az áttekintő táblázathoz. */
    public function osszesites(): array
    {
        return $this->db->query(
            'SELECT i.id, i.nev,
                    COUNT(t.id) AS sorok,
                    COUNT(DISTINCT t.modozat) AS modozatok,
                    MIN(t.havi_megtakaritas) AS havi_min,
                    MAX(t.havi_megtakaritas) AS havi_max,
                    MIN(t.befizetesek_szama) AS ho_min,
                    MAX(t.befizetesek_szama) AS ho_max
               FROM intezmenyek i
               LEFT JOIN tarifak t ON t.intezmeny_id = i.id
              GROUP BY i.id, i.nev
              ORDER BY i.nev'
        )->fetchAll();
    }

    public function intezmeny(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT id, nev FROM intezmenyek WHERE id = :id');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function tarifak(int $intezmenyId): array
    {
        $stmt = $this->db->prepare(
            'SELECT modozat, havi_megtakaritas, befizetesek_szama, osszes_befizetes, kamatbonusz, beteti_kamat, ebkm
               FROM tarifak WHERE intezmeny_id = :id
              ORDER BY befizetesek_szama, modozat, havi_megtakaritas'
        );
        $stmt->execute(['id' => $intezmenyId]);
        return $stmt->fetchAll();
    }

    /** Új intézményt hoz létre (vagy a meglévőt adja vissza, ha ilyen nevű már van). */
    public function intezmenyFelvetele(string $nev): int
    {
        $this->db->prepare('INSERT IGNORE INTO intezmenyek (nev) VALUES (:nev)')->execute(['nev' => $nev]);
        $stmt = $this->db->prepare('SELECT id FROM intezmenyek WHERE nev = :nev');
        $stmt->execute(['nev' => $nev]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Egy intézmény összes tarifáját lecseréli az új táblára.
     * Egy tranzakcióban fut: vagy minden sor bekerül, vagy semmi nem változik.
     */
    public function csere(int $intezmenyId, array $sorok): void
    {
        $this->db->beginTransaction();
        try {
            $this->db->prepare('DELETE FROM tarifak WHERE intezmeny_id = :id')->execute(['id' => $intezmenyId]);
            $beszuras = $this->db->prepare(
                'INSERT INTO tarifak (intezmeny_id, modozat, havi_megtakaritas, befizetesek_szama,
                                      osszes_befizetes, kamatbonusz, beteti_kamat, ebkm)
                 VALUES (:id, :modozat, :havi, :ho, :osszes, :bonusz, :kamat, :ebkm)'
            );
            foreach ($sorok as $s) {
                $beszuras->execute([
                    'id'      => $intezmenyId,
                    'modozat' => $s['modozat'],
                    'havi'    => $s['havi_megtakaritas'],
                    'ho'      => $s['befizetesek_szama'],
                    'osszes'  => $s['osszes_befizetes'],
                    'bonusz'  => $s['kamatbonusz'],
                    'kamat'   => $s['beteti_kamat'],
                    'ebkm'    => number_format($s['ebkm'], 2, '.', ''),
                ]);
            }
            $this->db->commit();
        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /** Egy intézmény összes tarifáját törli. Az intézmény és a hozzá tartozó érdeklődések megmaradnak. */
    public function torles(int $intezmenyId): int
    {
        $stmt = $this->db->prepare('DELETE FROM tarifak WHERE intezmeny_id = :id');
        $stmt->execute(['id' => $intezmenyId]);
        return $stmt->rowCount();
    }
}
