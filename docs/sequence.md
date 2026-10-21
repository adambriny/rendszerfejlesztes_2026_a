# Sequence diagram

A diagram az "Ajánlatok kalkulálása", az "Ajánlat kiválasztása" és az "Érdeklődés beküldése" használati esetek lépéseit mutatja be időrendben, a kamatbonusz.hu szereplői közötti üzenetekkel.

```mermaid
sequenceDiagram
    actor L as Látogató
    participant B as Böngésző
    participant S as Szerver (PHP)
    participant D as Adatbázis (MySQL)
    participant G as Gmail (megrendelő)

    Note over L,D: Ajánlatok kalkulálása
    L->>B: Kitölti a 3 kérdést, megnyomja a számolás gombot
    B->>S: Kalkulációs kérés (havi összeg, futamidő-sáv, jelenlegi bank)
    S->>S: Bemenetek ellenőrzése
    S->>D: Illeszkedő tarifasorok lekérdezése
    D-->>S: Tarifasorok
    S->>S: Lejáratkori összeg és EBKM számítása, intézményenként a legjobb sor, rangsorolás
    alt Van illeszkedő ajánlat
        S-->>B: 1-3 ajánlat intézménynév nélkül
        B-->>L: Ajánlati kártyák megjelenítése
    else Nincs illeszkedő ajánlat
        S-->>B: Üres eredmény
        B-->>L: "Nincs a feltételeknek megfelelő ajánlat" üzenet
    end

    Note over L,D: Ajánlat kiválasztása
    L->>B: "Ezt választom" gomb
    B-->>L: Kapcsolati űrlap a választott ajánlattal

    Note over L,G: Érdeklődés beküldése
    L->>B: Név, e-mail, telefon, adatkezelés elfogadása, küldés
    B->>S: Űrlap beküldése
    S->>S: Mezők ellenőrzése, spam szűrés
    alt Hibás adatok
        S-->>B: Hibaüzenet
        B-->>L: A hibás mezők megjelölése
    else Helyes adatok
        S->>D: Érdeklődés mentése ("új" állapottal)
        D-->>S: Mentés sikeres
        S->>G: E-mail értesítés (érdeklődő, ajánlat, intézmény)
        S-->>B: Sikeres beküldés
        B-->>L: Köszönő üzenet
    end
```

**Jelmagyarázat:** a függőleges szaggatott vonalak a szereplők életvonalai, fentről lefelé telik az idő. A folytonos nyíl egy kérés, a szaggatott nyíl a rá adott válasz. Az "alt" blokk két lehetséges kimenetet mutat, amelyek közül mindig csak az egyik történik meg.
