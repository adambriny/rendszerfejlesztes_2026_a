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

> Kitöltés alatt (felelős: Martin, issue #2)

### 2.3 Nem funkcionális követelmények

> Kitöltés alatt (felelős: Martin, issue #2)

## 4. Szervezeti felépítés és felelősségmegosztás

> Kitöltés alatt (felelős: Norbi, issue #3)

## 5. A munka feltételei

> Kitöltés alatt (felelős: Norbi, issue #3)
