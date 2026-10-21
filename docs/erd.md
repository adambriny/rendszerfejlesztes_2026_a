# Entity Relationship Diagram

A diagram a kamatbonusz.hu által tárolt összes adatot és azok kapcsolatait mutatja be.

```mermaid
erDiagram
    INTEZMENYEK ||--o{ TARIFAK : "kínál"
    INTEZMENYEK ||--o{ LEADS : "választott ajánlat intézménye"

    INTEZMENYEK {
        int id PK
        varchar nev "csak az admin felületen látszik"
    }

    TARIFAK {
        int id PK
        int intezmeny_id FK
        varchar modozat "pl. Otthontervező 58"
        int havi_megtakaritas "Ft, 10 000 és 100 000 között"
        int befizetesek_szama "havi befizetések száma (hó)"
        int osszes_befizetes "összes saját befizetés (Ft)"
        int kamatbonusz "Ft"
        int beteti_kamat "Ft"
    }

    LEADS {
        int id PK
        varchar nev
        varchar email
        varchar telefonszam
        int intezmeny_id FK
        varchar modozat "a választott ajánlat adatai a beküldés pillanatában"
        int havi_megtakaritas
        int befizetesek_szama
        int osszes_befizetes
        int lejaratkori_osszeg
        decimal ebkm
        varchar jelenlegi_bank "csak tájékoztató adat"
        enum allapot "új vagy lezárt"
        datetime beerkezes_datuma
    }

    ADMIN_FELHASZNALOK {
        int id PK
        varchar felhasznalonev UK
        varchar jelszo_hash "hash-elt jelszó"
    }
```

## Kapcsolatok

| Kapcsolat | Típus | Jelentés |
|---|---|---|
| intezmenyek → tarifak | 1 : N | Egy intézménynek több tarifasora lehet, egy tarifasor pontosan egy intézményhez tartozik. |
| intezmenyek → leads | 1 : N | Egy intézményt több érdeklődő is választhat, egy érdeklődés pontosan egy intézmény ajánlatára vonatkozik. |
| admin_felhasznalok | önálló | Az admin felhasználók tábla nem kapcsolódik a többihez, csak a bejelentkezéshez kell. |

## Számított értékek

Két értéket nem tárolunk a `tarifak` táblában, mert a többi adatból kiszámolhatók:

- **Lejáratkor felvehető összeg** = összes saját befizetés + kamatbónusz + betéti kamat
- **EBKM** = a havi befizetésekből és a lejáratkor felvehető összegből számított éves hozam

A `leads` tábla ezzel szemben elmenti a választott ajánlat összes adatát, mert a tarifák később változhatnak, a megrendelőnek pedig azt kell látnia, amit az érdeklődő a beküldéskor választott.

**Jelmagyarázat:** PK (elsődleges kulcs) az egyedi azonosító. FK (idegen kulcs) egy másik tábla azonosítójára mutat. UK (egyedi kulcs) nem ismétlődhet.
