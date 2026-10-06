# kamatbonusz.hu

Rendszerfejlesztés 2026, A csoport. Háromfős csapatprojekt, valós megrendelés alapján.

## 1. Összefoglaló

A kamatbonusz.hu egy magyar nyelvű, mobilbarát weboldal, amelynek központi eleme egy lakástakarék-pénztári (LTP) megtakarítási kalkulátor. A látogató három egyszerű kérdésre válaszol, a rendszer pedig az adatbázisban tárolt tarifatáblák alapján megkeresi a hozzá illő ajánlatokat, és EBKM szerint rangsorolja őket.

Az eredményoldalon legfeljebb három ajánlati kártya jelenik meg, intézménynév nélkül ("1. helyezett", "2. helyezett"). Ha a látogató kiválaszt egy ajánlatot, egy rövid kapcsolati űrlapon megadhatja az elérhetőségét. Az érdeklődés (lead) az adatbázisba kerül, a megrendelő pedig e-mailben értesítést kap a pontos ajánlattal együtt.

A projekt célja, hogy a megrendelő hideghívás nélkül, a látogatók önkéntes érdeklődése alapján jusson ügyfelekhez. A tarifatáblák és a beérkezett érdeklődések egy jelszóval védett admin felületen kezelhetők.

**Fogalmak:**

- **LTP (lakástakarék-pénztár):** állami szabályozás alatt álló, hosszú távú megtakarítási forma.
- **EBKM (Egységesített Betéti Kamatláb Mutató):** egységes mutató, amellyel a különböző megtakarítási ajánlatok hozama összehasonlítható.
- **Lead:** egy érdeklődő elérhetősége és az általa választott ajánlat adatai.

> A projekt jelen fázisban egyetemi demonstrációs célt szolgál, nem minősül működő pénzügyi közvetítő szolgáltatásnak. Éles indítás előtt jogi és megfelelőségi ellenőrzés szükséges.

## 2. A projekt bemutatása

A rendszer két részből áll: egy nyilvános weboldalból, amelyet bárki használhat, és egy admin felületből, amelyet csak bejelentkezés után lehet elérni.

### 2.1 Rendszerspecifikáció

**A felhasználói folyamat:**

1. A látogató a kezdőlapra érkezik, ahol motiváló tartalmat olvas a megtakarításról.
2. Az Ismertető és a Garanciák aloldalakon tájékozódhat az LTP-kről és a garanciális feltételekről.
3. A kalkulátorban három kérdésre válaszol: havi megtakarítás összege, futamidő (3 fix sávból), jelenlegi számlavezető bank.
4. A szerver az adatbázisból kiolvassa az illeszkedő tarifasorokat, és EBKM szerint csökkenő sorrendbe rendezi őket.
5. Megjelenik 1-3 ajánlati kártya, intézménynév nélkül.
6. A látogató kiválaszt egy kártyát, és kitölti a kapcsolati űrlapot (név, e-mail, telefonszám).
7. A rendszer elmenti az érdeklődést, és e-mailt küld a megrendelőnek.

**Oldaltérkép:**

| Nyilvános oldalak | Admin oldalak (bejelentkezés után) |
|---|---|
| Kezdőlap | Bejelentkezés |
| Ismertető | Tarifatábla-kezelő |
| Garanciák | Leadkezelő |
| Kalkulátor | |
| Kapcsolat | |
| Adatkezelési nyilatkozat | |

**Technológiák:**

| Réteg | Technológia |
|---|---|
| Frontend | HTML, CSS, JavaScript |
| Backend | PHP |
| Adatbázis | MySQL |
| E-mail küldés | PHPMailer, SMTP |

**Megjelenés:** letisztult, fekete-fehér, lekerekített kártyás design, reszponzív kialakítás minden aloldalon.

### 2.2 Funkcionális követelmények

A funkcionális követelmények azt írják le, **mit tud** a rendszer.

| ID | Megnevezés | Leírás |
|---|---|---|
| F01 | Kalkulátor kérdések | A látogató megadja a havi megtakarítás összegét (Ft), kiválaszt egy futamidő-sávot ("5 év vagy az alatt", "5-10 év között", "10 év fölött"), és megadja a jelenlegi számlavezető bankját. |
| F02 | Ajánlatkeresés | A szerver az adatbázisból kiolvassa minden intézmény azon tarifasorát, amely illeszkedik a megadott összegre és a választott futamidő-sávra. |
| F03 | Rangsorolás | Az illeszkedő ajánlatokat a rendszer EBKM szerint csökkenő sorrendbe rendezi. Ha egy intézménynek több tarifasora is illeszkedik, a legmagasabb EBKM-ű számít. |
| F04 | Eredménykártyák | 1-3 ajánlati kártya jelenik meg, intézménynév nélkül. Egy kártya mutatja: helyezés, havi megtakarítás, lejáratkor felvehető összeg, kamat, befizetett összeg. |
| F05 | Ajánlat kiválasztása | Az "Ezt választom" gombra kattintva a látogató a kapcsolati űrlapra jut, a választott ajánlat adataival. |
| F06 | Kapcsolati űrlap | Látható mezők: név, e-mail, telefonszám. A választott ajánlat adatai automatikusan kapcsolódnak az érdeklődőhöz. |
| F07 | Adatvédelmi hozzájárulás | Az űrlap csak az Adatkezelési nyilatkozat elfogadása után küldhető el. |
| F08 | Érdeklődés mentése | A beküldött érdeklődés az adatbázisba kerül "új" állapottal és a beérkezés dátumával. |
| F09 | E-mail értesítés | Minden új érdeklődésről a megrendelő e-mailt kap: az érdeklődő elérhetősége, a választott ajánlat pontos adatai és az érintett intézmény. |
| F10 | Admin bejelentkezés | Az admin felület csak felhasználónévvel és jelszóval érhető el. |
| F11 | Tarifatábla-kezelés | Az admin intézményeket és tarifasorokat hozhat létre, módosíthat és törölhet. |
| F12 | Leadkezelés | Az admin listázhatja az érdeklődéseket, állapotukat "új"-ról "lezárt"-ra válthatja, és kérésre törölheti őket. |
| F13 | Tájékoztató oldalak | A weboldal tartalmaz kezdőlapot, Ismertető, Garanciák és Adatkezelési nyilatkozat aloldalt. |

### 2.3 Nem funkcionális követelmények

A nem funkcionális követelmények azt írják le, **mennyire jól és milyen feltételek mellett** működik a rendszer.

| ID | Terület | Követelmény |
|---|---|---|
| NF01 | Biztonság | Minden felhasználói bemenet escape-elve kerül megjelenítésre és e-mailbe (XSS elleni védelem). |
| NF02 | Biztonság | Az adatbázis-műveletek kizárólag paraméterezett lekérdezésekkel (prepared statements) történnek (SQL injection elleni védelem). |
| NF03 | Biztonság | Minden űrlapmezőt a szerver oldalon (PHP) is ellenőrizni kell, nem elég a böngészőben futó ellenőrzés. |
| NF04 | Biztonság | Az admin jelszó hash-elve tárolódik, az admin felületet munkamenet-kezelés (session) védi. |
| NF05 | Biztonság | A kapcsolati űrlapot honeypot mező vagy kérésszám-korlátozás védi a spam ellen. |
| NF06 | Adatvédelem | A nyilvános felületre és a böngészőnek küldött válaszba intézménynév nem kerül ki, azt csak a szerver és az admin felület ismeri. |
| NF07 | Adatvédelem | A megrendelő neve és személye a weboldalon sehol nem jelenik meg. |
| NF08 | Adatvédelem | Az érdeklődők adatai kérésre törölhetők (GDPR). |
| NF09 | Használhatóság | Az oldal reszponzív: mobilon, tableten és asztali gépen is jól használható. |
| NF10 | Használhatóság | Az oldal magyar nyelvű, letisztult, egységes megjelenésű. |
| NF11 | Keresőoptimalizálás | Minden oldal egyedi címmel és leírással rendelkezik, szemantikus HTML szerkezetet használ. |
| NF12 | Technológia | A rendszer kizárólag HTML, CSS, JavaScript, PHP és MySQL technológiákra épül. |
| NF13 | Megbízhatóság | Az e-mailek SMTP-n keresztül, PHPMailer könyvtárral kerülnek kiküldésre. |
| NF14 | Jogi | A rendszer egyetemi demonstrációs célú, éles indítás előtt jogi és megfelelőségi ellenőrzés szükséges. |


## 4. Szervezeti felépítés és felelősségmegosztás

A projektet egy háromfős csapat valósítja meg egy külső, valós megrendelő számára. A csapat munkáját a projektmenedzser hangolja össze, aki a kapcsolattartó is az oktató felé.

### 4.1 Projektcsapat

| Név | GitHub felhasználónév | Szerepkör | Fő felelősségi terület |
|---|---|---|---|
| Paragi Ádám | @adamparagi | Projektmenedzser, fejlesztő | Feladatok kiosztása, ütemezés, kapcsolattartás, Adatbázis |
| Görög Martin | @gorogmartin | Fejlesztő | Backend |
| Mag Norbert | @norbertmag2001-max | Fejlesztő | Frontend |

### 4.2 Külső szereplők

| Szereplő | Szerep |
|---|---|
| Megrendelő | A weboldal valós megrendelője, az érdeklődések fogadója. Nevét a nyilvános dokumentáció nem tartalmazza. |
| Oktató | A mérföldkövek elfogadása, visszajelzés a bemutatókon. |

## 5. A munka feltételei

### 5.1 Munkakörnyezet

| Terület | Eszköz |
|---|---|
| Verziókezelés és feladatkezelés | GitHub (repository, issue-k, pull requestek, projekttábla) |
| Csapatkommunikáció | Discord |
| Kódszerkesztő | Visual Studio Code |
| Helyi fejlesztői környezet | XAMPP: Apache, PHP, MySQL |
| Bemutatási környezet | Egyetemi szerver |

**Munkamódszer:** minden feladat egy GitHub issue. Minden változtatás pull requesten keresztül kerül a fő ágba, amelyet egy másik csapattag átnéz, mielőtt elfogadják.

### 5.2 Rizikómenedzsment

| Kockázat | Hatás | Megelőzés, kezelés |
|---|---|---|
| Egy csapattag kiesik (betegség, egyéb elfoglaltság) | Csúszik a határidő | A feladatokat a projektmenedzser átosztja, a dokumentáció mindenki számára elérhető. |
| Határidő csúszása | Mérföldkő nem teljesül | A feladatokat a határidő előtt legalább egy nappal "In Review" állapotba kell hozni. |
| A tarifatáblák késve érkeznek a megrendelőtől | A kalkulátor nem tesztelhető valós adatokkal | Fejlesztés közben minta adatokkal dolgozunk. |
| Az SMTP hozzáférés nem áll rendelkezésre | Az e-mail értesítés nem működik | Tesztelés közben teszt e-mail szolgáltatást használunk. |
| Ütköző változtatások ugyanabban a fájlban | Elveszett vagy összekevert munka | Egy fájlon egyszerre lehetőleg egy ember dolgozik, a pull requesteket egymás után fogadjuk el. |
| Jelszó vagy hozzáférési adat kerül a nyilvános repositoryba | Biztonsági incidens | Hozzáférési adat soha nem kerül a repositoryba, külön, a verziókezelésből kizárt konfigurációs fájlban tároljuk. |
| Jogi, megfelelőségi kérdések éles indítás esetén | A rendszer nem indítható élesben | A projekt demonstrációs célú, éles indítás előtt a megrendelő jogi ellenőrzést végeztet. |

