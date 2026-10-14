# Beszerzés modul – terv

## 1. Cél

Külön admin felület a termékek beszerzésének (megrendelésének) rögzítésére. Egy beszerzés egy
eladó ügyféltől (Customer, `is_seller = true`) történő megrendelés, amit a saját (pl. devizás)
rendelési devizában rögzítünk, a termékek mellé pedig rögzítjük a felmerülő egyéb költségeket
(vám, adó, szállítás stb.), majd ezeket a termékek között arányosan szétosztva eltároljuk, hogy
tételenként lássuk a termék tényleges, mindent-egybevett (HUF) beszerzési önköltségét.

Mintaként a már létező `StockMovement` / `StockMovementItem` fej-tétel szerkezetet követjük
(lásd `app/Models/StockMovement.php`, `app/Models/StockMovementItem.php`), illetve a `Customer`
modell meglévő `is_seller` mezőjét használjuk az eladó/beszállító kiválasztásához – nincs
szükség külön "Supplier" entitásra.

## 2. Fogalmak

- **Alapértelmezett deviza**: a bolt könyvelési pénzneme, jelenleg HUF (ld. `products.price`,
  `order_items.unit_price` mind `unsignedInteger`, tizedesjegy nélkül). Ebben a devizában
  tároljuk a végső, arányosított önköltséget.
- **Beszerzési deviza**: a beszerzés devizája (pl. EUR, USD), amiben a beszállítóval
  elszámolunk. Tételenként ebben rögzítjük az egységárat, és az egyéb költségek összege is
  ebben a devizában van megadva.
- **Egyéb költség**: a beszerzéshez kapcsolódó, de nem a termékek listaárában szereplő
  költség (vám, ÁFA/import adó, fuvardíj, bankköltség stb.), típussal, megjegyzéssel és a
  beszerzés devizájában megadott összeggel – csak könyvelési/informatív bontás, a
  számításban nem veszünk részt vele külön (ld. lent).
- **Kifizetett végösszeg (HUF)**: a beszerzésre ténylegesen kifizetett, forintban ismert
  teljes összeg, ami *mindent* tartalmaz (a termékek ára és az összes egyéb költség együtt).
  Nincs szükség árfolyam tárolására: a tételek idegen devizás sorösszegéből számítható a
  teljes idegen devizás rendelésérték, a kifizetett forint végösszeget pedig ennek az
  arányában osztjuk szét a tételek között (ld. 5.1).
- **Arányosítás**: a kifizetett forint végösszeget a tételek idegen devizás sorösszege
  (mennyiség × egységár) arányában szétosztjuk a tételek között – így kapjuk meg
  tételenként a termék tényleges, mindent-egybevett (egyéb költségeket is tartalmazó) HUF
  önköltségét, anélkül hogy külön árfolyamot kellene tárolni vagy számolni.

## 3. Adatbázis terv

### 3.1 `currencies`

Lookup tábla, a `countries` tábla mintájára (`code`, `name`, `sort_order`).

| mező | típus | megjegyzés |
|---|---|---|
| id | id | |
| code | string(3), unique | ISO 4217 kód, pl. `HUF`, `EUR`, `USD` |
| name | string | megjelenítendő név |
| is_default | boolean, default false | pontosan egy sor `true` legyen (alkalmazás szinten ellenőrizve) |
| sort_order | integer, default 0 | |
| timestamps | | |

Seeder: `HUF` (is_default=true), `EUR`, `USD` induláshoz.

### 3.2 `purchase_statuses`

Lookup tábla a beszerzés állapotainak (ld. 5.2), az `order_statuses` tábla egyszerűsített
mintájára – itt nincs szükség `slug`/`color`/`is_final` mezőkre, a `name` maga a program által
is használt, stabil kulcs (angol, kisbetűs azonosító), a `description` pedig a magyar,
felhasználónak szóló magyarázat.

| mező | típus | megjegyzés |
|---|---|---|
| id | id | |
| name | string, unique | stabil azonosító kulcs, pl. `ordered`, `received`, `cancelled` – ezt használja a kód (pl. `Purchase::isReceived()`) |
| description | text, nullable | magyar nyelvű leírás, admin felületen jelenik meg |
| timestamps | | |

Seeder (`PurchaseStatusSeeder`, az `OrderStatusSeeder` mintájára):

| name | description |
|---|---|
| `ordered` | Megrendelve és kifizetve a beszállítónál, áru még nem érkezett |
| `received` | Beérkezett |
| `cancelled` | Sztornózva |

### 3.3 `purchase_cost_types`

Lookup tábla az egyéb költségtípusoknak (vám, adó, szállítás, egyéb).

| mező | típus | megjegyzés |
|---|---|---|
| id | id | |
| name | string | pl. "Vám", "Import ÁFA", "Szállítási költség", "Egyéb" |
| sort_order | integer, default 0 | |
| timestamps | | |

### 3.4 `purchases` (beszerzés fejléc)

| mező | típus | megjegyzés |
|---|---|---|
| id | id | |
| customer_id | foreignId → customers, restrictOnDelete | az eladó ügyfél (`is_seller = true`) |
| destination_site_id | foreignId → sites, restrictOnDelete | melyik telephelyre érkezik az áru – létrehozáskor a konkrét régió (polc/raktárrész) még nem ismert, az majd a beérkezéskor (5.2) dől el |
| currency_id | foreignId → currencies, restrictOnDelete | a beszerzés devizája (a tételek ebben vannak rögzítve) |
| description | string, nullable | rövid leírás/cím a beszerzésről (listában is megjelenik) |
| url | string, nullable | a megrendelés linkje (pl. a beszállító weboldalán a rendelés/visszaigazolás URL-je) – admin felületen kattintható |
| ordered_at | timestamp | a megrendelés időpontja (dátum + idő) |
| expected_arrival_date_min | date, nullable | várható érkezés – legkorábbi dátum |
| expected_arrival_date_max | date, nullable | várható érkezés – legkésőbbi dátum |
| received_at | timestamp, nullable | az áru tényleges beérkezésének időpontja – ez zárja le a beszerzést, nincs külön `closed_at` (ld. 5.2) |
| received_by | foreignId → users, nullOnDelete | ki rögzítette a beérkezést |
| stock_movement_id | foreignId → stock_movements, nullable, nullOnDelete | a beérkeztetéskor automatikusan létrehozott készletmozgás (`StockMovement::TYPE_IN`) azonosítója – addig `null`, amíg a beszerzés nincs beérkeztetve (ld. 5.2) |
| purchase_status_id | foreignId → purchase_statuses, restrictOnDelete | a beszerzés állapota (ld. 3.2, 5.2) – létrehozáskor az `ordered` sorra állítva (a rögzítés pillanatában a rendelés és a kifizetés a partnernél már megtörtént, ld. 5.2) |
| paid_amount_default_currency | unsignedInteger, nullable | a beszerzésre ténylegesen kifizetett **teljes** összeg forintban – ez tartalmazza az egyéb költségeket is, ebből történik a tételek közötti arányosítás (ld. 5.1) |
| note | text, nullable | megjegyzés |
| created_by | foreignId → users, nullOnDelete | |
| timestamps | | |

### 3.5 `purchase_items` (beszerzés tételek)

| mező | típus | megjegyzés |
|---|---|---|
| id | id | |
| purchase_id | foreignId → purchases, cascadeOnDelete | |
| product_id | foreignId → products, restrictOnDelete | |
| quantity | unsignedInteger | |
| unit_price | decimal(12,4) | egységár a beszerzés devizájában (a fejléc `purchases.currency_id` szerint – az egész beszerzés egy devizában van, nincs külön tétel-szintű deviza) |
| unit_cost_default_currency | unsignedInteger, nullable | a termék ténylegesen kiszámított, egyéb költségeket is arányosan tartalmazó HUF **egységára**. Nem szerkeszthető mező: a beszerzés mentésekor (fej- vagy tételmódosításkor, ld. 5.1) automatikusan újraszámolódik és **ténylegesen tárolódik**, hogy később (riportok, készletérték-nyilvántartás) join/újraszámolás nélkül lekérdezhető legyen. Amíg `purchases.paid_amount_default_currency` nincs kitöltve, `null` |
| timestamps | | |

Nincs `product_name` pillanatfelvétel (a `product_id`-n keresztül mindig az aktuális
terméknevet jelenítjük meg) és nincs tárolt `line_total` sem (`quantity * unit_price`-ként
mindig kiszámolható, csak megjelenítési accessor) – ez nem tévesztendő össze a fent tárolt
`unit_cost_default_currency` **egységárral**, ami nem sorösszeg.

### 3.6 `purchase_costs` (beszerzés egyéb költségei)

| mező | típus | megjegyzés |
|---|---|---|
| id | id | |
| purchase_id | foreignId → purchases, cascadeOnDelete | |
| purchase_cost_type_id | foreignId → purchase_cost_types, restrictOnDelete | |
| amount | decimal(12,4) | összeg a beszerzés devizájában (a fejléc `purchases.currency_id` szerint – ugyanúgy, mint a tételek `unit_price`-a) |
| note | text, nullable | megjegyzés (user kérésére) |
| timestamps | | |

Ez a tábla csak könyvelési/informatív bontás arra, hogy a `purchases.paid_amount_default_currency`
végösszegen belül milyen egyéb költségek vannak (vám ennyi, szállítás ennyi stb.) – a
tételek közötti arányosításban (5.1) nem veszünk részt vele külön-külön, mert a teljes
kifizetett forint végösszeg már eleve tartalmazza ezeket. Az összeg devizában rögzítve
(nem HUF-ban) kerül tárolásra, mert a vám/szállítás/egyéb költség is jellemzően a
beszállítóval azonos devizában merül fel, összhangban a tételek `unit_price` mezőjével.

## 4. Modellek

- `Currency` – `fillable: code, name, is_default, sort_order`; `hasMany(Purchase)`.
- `PurchaseStatus` – `fillable: name, description`; `hasMany(Purchase)`; konstansok a stabil
  `name` kulcsokhoz (`const ORDERED = 'ordered'; const RECEIVED = 'received'; const CANCELLED = 'cancelled';`),
  valamint egy `static findByName(string $name): self` helper a kódból történő kereséshez.
- `PurchaseCostType` – `fillable: name, sort_order`; `hasMany(PurchaseCost)`.
- `Purchase` – fej modell, `belongsTo(Customer)`, `belongsTo(Site, 'destination_site_id')`,
  `belongsTo(Currency)`, `belongsTo(PurchaseStatus)`, `belongsTo(StockMovement)`,
  `hasMany(PurchaseItem)`, `hasMany(PurchaseCost)`, `belongsTo(User, 'created_by')`,
  `belongsTo(User, 'received_by')`. Egy `isReceived(): bool`
  (`purchaseStatus->name === PurchaseStatus::RECEIVED`, a `StockMovement::isClosed()`
  mintájára), egy `totalForeignAmount(): string|float` (nem tárolt, a tételekből számolt
  idegen devizás végösszeg, csak megjelenítéshez), valamint egy
  `recalculateItemUnitCosts(): void` metódus (ld. 5.1), ami minden tételre kiszámolja és
  **elmenti** a `purchase_items.unit_cost_default_currency` oszlopot – ezt a `Purchase` és a
  `PurchaseItem` modellek `saved`/`deleted` eseménye (observer) hívja meg automatikusan
  minden fej- (pl. `paid_amount_default_currency` módosítása) vagy
  tételmentéskor/-törléskor.
- `PurchaseItem` – `belongsTo(Purchase)`, `belongsTo(Product)`;
  `lineTotal(): float` (`quantity * unit_price`, nem tárolt, csak megjelenítési accessor,
  amit a belső arányosítási számítás is felhasznál). A `unit_cost_default_currency` mezőt
  nem ez a modell számolja ki közvetlenül, hanem a `Purchase::recalculateItemUnitCosts()`
  tölti fel rajta (ld. 5.1) – ettől függetlenül a modellen nincs szükség kézi `fillable`
  bejegyzésre hozzá, mert mindig az observeren keresztül íródik.
- `PurchaseCost` – `belongsTo(Purchase)`, `belongsTo(PurchaseCostType)`.

`Customer` modellhez új reláció: `sellerPurchases(): HasMany` (`Purchase::class`), illetve a
Customers Filament resource szűrhető/kereshető legyen `is_seller` szerint a beszerzés
létrehozásakor (select mezőben).

## 5. Üzleti logika

### 5.1 Arányosítás – tárolt, mentéskor újraszámolt egységár

Nincs szükség árfolyam tárolására vagy számolására. A tételek a beszerzés devizájában
(`quantity`, `unit_price`) vannak rögzítve – az egész beszerzés egy devizában van
(`purchases.currency_id`), nincs külön tétel-szintű deviza mező. A
`purchases.paid_amount_default_currency` mezőben kézzel rögzítjük, hogy ténylegesen
mennyit fizettünk ki forintban – ez a szám már *mindent* tartalmaz (termékek ára + egyéb
költségek együtt). A tétel idegen devizás sorösszege (`line_total`) továbbra sem tárolt,
mindig `quantity * unit_price`-ként számolódik (csak megjelenítési/belső segédérték, pl. egy
`PurchaseItem::lineTotal(): float` accessorral).

A tétel ténylegesen kiszámított, mindent-egybevett HUF **egységára**
(`purchase_items.unit_cost_default_currency`) a kifizetett forint végösszegnek a tételek
(számolt) idegen devizás sorösszege szerinti arányos szétosztásával, majd a mennyiséggel
elosztva adódik. Ez a mező **ténylegesen tárolt oszlop** (nem accessor) és **nem
szerkeszthető** – a `Purchase::recalculateItemUnitCosts()` metódus számolja ki és menti el
minden testvér-tételre; ezt a fej vagy bármely tétel mentésekor/törlésekor (modell `saved`/
`deleted` eseményén keresztüli observer) automatikusan meghívjuk, hogy a legutóbbi
`paid_amount_default_currency` és tétel-adatok alapján mindig naprakész legyen, és később
(riportokhoz, készletérték-nyilvántartáshoz) join/újraszámolás nélkül lekérdezhető maradjon:

1. `$totalForeignValue = sum(quantity * unit_price)` az összes tételre (idegen devizában)
2. minden tételre (belső számítás, nem külön kitett érték): `allocatedTotal = round(purchase.paid_amount_default_currency * (quantity * unit_price) / totalForeignValue)`
3. az utolsó tételre (stabil sorrendben, pl. `id` szerint) kerekítési korrekció, hogy az
   `allocatedTotal`-ok összege pontosan `paid_amount_default_currency`-re jöjjön ki
4. `unit_cost_default_currency = round(allocatedTotal / quantity)` – ez a kiszámított érték
   kerül mentésre minden tételen, amit az admin felület is ebből olvas ki (pl.
   készletérték-nyilvántartáshoz)

Ha `paid_amount_default_currency` még `null` (a gyakorlatban ritka eset, hiszen a beszerzés
rögzítésekor – ld. 5.2 – a tételek és a kifizetett összeg már jellemzően ismert), az
újraszámolás minden tételen `unit_cost_default_currency = null` értéket ment – a
beérkeztetéshez (5.2) viszont kötelező a kitöltése.

A `purchase_costs` sorok (vám, szállítás stb.) ebben a számításban nem vesznek részt
külön-külön – azok csak tájékoztató bontást adnak arról, hogy a kifizetett végösszegen
belül miből mennyi volt (ld. 3.6).

### 5.2 Állapotok / workflow

A `purchase_statuses` tábla (3.2) a beszerzések **kategorizálására** szolgál – a státusz
önmagában nem korlátozza a szerkeszthetőséget, a tételek/költségek bármelyik állapotban
szabadon módosíthatók (ld. 5.3). A valós munkafolyamat az, hogy a tételeket a partner
weboldalán rendeljük meg és fizetjük ki, és csak ezután rögzítjük a beszerzést a
rendszerben – emiatt nincs külön "tervezet" állapot, egy beszerzés mindig kész (rendelt és
kifizetett) adatokkal jön létre. A kód a `PurchaseStatus::ORDERED` / `::RECEIVED` /
`::CANCELLED` konstansokkal (a `name` mező értékei) azonosítja a három sort:

- `ordered` – rögzítve a rendszerben: megrendelve és kifizetve a beszállítónál
  (`ordered_at` kitöltve), rögzítve a várható minimum/maximum érkezési dátum is, de az áru
  még nem érkezett. Ekkor már csak a telephely ismert, a konkrét régió még nem. Ez az
  alapértelmezett állapot egy új beszerzés létrehozásakor.
- `received` – az áru ténylegesen megérkezett. A "Beérkeztetés" akció (gomb) a következőket
  végzi:
  1. ellenőrzi, hogy `paid_amount_default_currency` ki van-e töltve (enélkül a tételek
     HUF-önköltsége, 5.1, nem számolható) és hogy van legalább 1 tétel;
  2. kitölti a `received_at` (most) és `received_by` (bejelentkezett user) mezőket, a
     `purchase_status_id`-t a `received` sorra állítja;
  3. létrehoz egy **készletre rakás** (`StockMovement`, `type = StockMovement::TYPE_IN`)
     fejrekordot – a `destination_region_id` még `null`, mert a tényleges régiót (a
     `purchase.destination_site_id` telephelyen belül) csak most, a készletmozgás
     szerkesztésekor választjuk ki – plusz a `purchase_items`-ből 1:1 `StockMovementItem`
     sorokat (mennyiség alapján);
  4. elmenti a létrehozott készletmozgás azonosítóját a `purchases.stock_movement_id` mezőbe;
  5. **átirányítja** az adminfelületet a létrehozott `StockMovement` szerkesztő oldalára
     (Filament `redirect()` az akció `action()`-jében), hogy ott válassza ki a célrégiót és
     zárja le a mozgatást (`StockMovement::isClosed()`) – ez a meglévő készletkezelő modul
     lezárási folyamata, ami ténylegesen megnöveli a `region_product_stocks` készletet.
- `cancelled` – sztornózott beszerzés, készletmozgás nem generálódik.

### 5.3 Validáció

- `purchases.customer_id` csak `is_seller = true` ügyfél lehet (form szinten szűrt select +
  modell szintű ellenőrzés).
- Legalább 1 tétel szükséges a `received` állapotba lépéshez (a header `ordered` státusszal
  jön létre, tételek nélkül is menthető, de a beérkeztetéshez már kell legalább 1 tétel).
- A tételek/költségek bármelyik állapotban (`ordered`, `received` is) szabadon
  módosíthatók/törölhetők – a státusz csak kategorizál, nem korlátoz szerkeszthetőséget.

## 6. Admin felület (Filament)

Új erőforrás: `app/Filament/Resources/Purchases` (a `StockMovements` resource szerkezetét
követve: `Pages`, `Schemas`, `Tables`), plusz három kiegészítő lookup resource:
`PurchaseStatuses`, `PurchaseCostTypes` és `Currencies` (egyszerű CRUD, a
`PaymentMethods`/`ShippingMethods` resource-ok mintájára).

### Purchases form (fej)

- Select: eladó ügyfél (csak `is_seller` ügyfelek, kereshető)
- Select: célterület – telephely (`Site`)
- Select: beszerzési deviza
- Szöveg: leírás (`description`)
- URL mező: megrendelés linkje (`url`) – Filament `TextInput::make('url')->url()` validációval
- DátumIdő: megrendelés időpontja (`ordered_at`, Filament `DateTimePicker`)
- Két dátum mező: várható érkezés minimum / maximum (`expected_arrival_date_min/max`,
  `DatePicker`)
- Szöveg: megjegyzés
- Szám mező: "Kifizetett összeg forintban (mindent egybevéve)"
- Select: állapot (`purchase_status_id`, a `purchase_statuses` tábla sorai) / akció gombok az
  állapotváltáshoz – a "Beérkeztetés" akció (megerősítő modal, régió-választás nélkül) elmenti
  a `received_at`/`received_by` mezőket, létrehozza a `StockMovement`-et és annak szerkesztő
  oldalára navigál (ld. 5.2)

### Tétel RelationManager (`purchase_items`)

- Termék select (kereshető, a termék neve mindig a `product_id`-n keresztül, aktuális állapot
  szerint jelenik meg – nincs külön név pillanatfelvétel), mennyiség, egységár (a beszerzés
  devizájában – a fejléc `purchases.currency_id` szerint, nincs külön tétel-szintű deviza)
- Readonly oszlop a táblázatban: egységár (HUF) – a `purchase_items.unit_cost_default_currency`
  tárolt mező (ld. 3.5, 5.1), a termék ténylegesen kiszámított, egyéb költségeket is
  arányosan tartalmazó HUF egységára. Nem szerkeszthető: a fej- vagy tételmentéskor
  automatikusan újraszámolódik és mentődik; csak akkor nem `null`, ha a fejlécen már ki van
  töltve a kifizetett összeg. (A deviza szerinti sorösszeg, `quantity * unit_price`, csak
  megjelenítési – nem tárolt – kiegészítő érték a beszállítói számlával való
  összevetéshez; nem tévesztendő össze az egységárral.)

### Egyéb költség RelationManager (`purchase_costs`)

- Költségtípus select, összeg (a beszerzés devizájában), megjegyzés – pusztán tájékoztató
  bontás, nem hat automatikusan a tételek önköltség-számítására (ld. 3.6, 5.1)

### Táblázat nézet (lista)

Oszlopok: leírás (`description`) + dátum, eladó, telephely, deviza, tételek idegen devizás
végösszege, kifizetett összeg (HUF), állapot badge. A `url` oszlop egy kattintható ikon/link
(Filament `TextColumn::make('url')->url(fn ($record) => $record->url)->openUrlInNewTab()`),
csak akkor jelenik meg, ha ki van töltve.

## 7. Migrációk sorrendje

1. `create_currencies_table`
2. `create_purchase_statuses_table` + `PurchaseStatusSeeder`
3. `create_purchase_cost_types_table`
4. `create_purchases_table` (currencies, purchase_statuses, customers, sites,
   stock_movements, users függőséggel – a `stock_movements` tábla már létezik, a jelen
   tervnél korábbi migrációból)
5. `create_purchase_items_table` (purchases, products függőséggel)
6. `create_purchase_costs_table` (purchase_cost_types függőséggel)

## 8. Nyitott kérdések / feltételezések

- Feltételezve: egy beszerzéshez egy célterület (telephely) tartozik, a konkrét régiót a
  beérkeztetéskor létrejövő egyetlen `StockMovement` fejlécén választjuk ki – ez a meglévő
  `StockMovement` tábla korlátja is (egy mozgatásnak egy `destination_region_id`-je van), nem
  ennek a tervnek az új megkötése. Ha egy beszerzés tételei ténylegesen több régióba kerülnek
  szétpakolásra ugyanazon a telephelyen belül, azt **nem** a meglévő `StockMovement`
  (telephelyek közötti mozgatás) oldja meg, hanem egy külön, jövőbeli felület, amivel egy
  telephelyen belül lehet a tételeket régiók között rendezni/átpakolni. Ez a jelen tervnek
  nem része, későbbi kidolgozásra vár.
- Tisztázva: a tényleges munkafolyamat az, hogy a tételeket a partner weboldalán rendeljük
  meg és fizetjük ki, és csak **ezek után** rögzítjük a beszerzést a rendszerben – vagyis a
  `purchases` rekord létrehozásakor már ismert a deviza, a tételek egységára és a kifizetett
  forint végösszeg (`paid_amount_default_currency`) is, ezek nem utólag derülnek ki. A mezőt
  (a tételekhez és költségekhez hasonlóan, ld. 5.3) ettől függetlenül státusztól függetlenül
  mindig szabadon szerkeszthetőnek és a tételek önköltségét a mentéskor mindig
  újraszámoltnak tekintjük (5.1), hogy egy esetleges utólagos elírás is javítható legyen.
- Szándékos: mivel a tételek/költségek `received` állapotba lépés után is szabadon
  módosíthatók (5.3), egy utólagos tételmódosítás (mennyiség, termék) elszakadhat a
  beérkeztetéskor már létrehozott `StockMovement`/`StockMovementItem` soroktól – ezek **nem**
  frissülnek automatikusan, és ez **nem hiba, hanem szándékos**: a két oldal (mit rendeltünk a
  `purchase_items`-ben vs. mi lett ténylegesen felvéve a `StockMovementItem`-ekben) eltérése
  pont abból enged következtetni, hogy nem az érkezett meg, amit rendeltünk.
- Feltételezve: a `purchase_items.unit_cost_default_currency` automatikus újraszámolása
  (`Purchase::recalculateItemUnitCosts()`, ld. 4. és 5.1 szakasz) modell `saved`/`deleted`
  eseményekkel (observer) van megoldva – nem kézi admin akcióként –, és minden fejléc-
  (elsősorban `paid_amount_default_currency`) vagy tételmentés/-törlés után lefut, hogy a
  tárolt oszlop mindig szinkronban legyen a legutóbbi adatokkal.
