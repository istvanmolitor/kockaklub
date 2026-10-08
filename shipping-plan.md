# Szállítási mód refaktor terv — telephelyi átvétel + Foxpost csomagautomata

Státusz: **tervezet, nincs implementálva**. Ez a dokumentum a `/penztar` checkout szállítási mód
kiválasztásának bővítését tervezi meg: jelenleg egy sima választólista van, aminek rugalmasabbá
kell válnia, hogy később bármikor új "automata" típusú szolgáltató (Foxpost, és később mások) és
a telephelyi átvétel is beköthető legyen anélkül, hogy minden alkalommal a checkout és az admin
form nagy részét újra kelljen írni.

## 1. Jelenlegi állapot

- `shipping_methods` tábla: `name`, `description`, `cost`, `is_active` — típusfogalom nincs, a
  "Személyes átvétel" csak egy szöveges néven seedelt sor (`database/seeders/ShippingMethodSeeder.php`),
  nincs hozzá kapcsolva semmilyen telephely- vagy pontadat.
- `orders` tábla: `shipping_name`, `shipping_phone`, `shipping_country_id`, `shipping_city`,
  `shipping_zip`, `shipping_address` (ez utóbbi **kötelező**, nem nullable) — strukturált
  "átvételi pont" adatot nem tud tárolni, csak egy szabad szöveges cím blokkot.
- `sites` tábla: a cég saját telephelyeit (raktár/bolt) írja le (`name`, `country`, `city`, `zip`,
  `address`, `is_active`, `is_main`), de ma kizárólag a rendelés `site_id` (melyik telephelyről
  szolgálják ki a rendelést) célra használt — nincs kapcsolat a checkout felé, a vásárló nem
  választhat belőle.
- Checkout: `app/Http/Controllers/Storefront/CheckoutController.php` + plain Blade
  (`resources/views/storefront/checkout/create.blade.php`) + vanilla JS — nincs Vue/Livewire a
  checkoutban. A szállítási mód rádiógombjai `data-shipping-method-option` attribútummal vannak
  jelölve, a JS ez alapján jeleníti meg a hozzá tartozó fizetési módokat (ugyanez a minta
  felhasználható az új "átvételi pont" blokkok megjelenítésére/elrejtésére is).
- Foxpost integráció **nincs** a kódban (sem config, sem service class, sem env var).

## 2. Cél

| Szállítási mód | Mit kell választani checkoutban |
|---|---|
| Futár (jelenlegi) | Cím megadása — marad a mai folyamat, nem változik |
| Személyes átvétel | Telephely kiválasztása a listából (`sites`) |
| Foxpost | Csomagautomata kiválasztása térképes widgetről |
| *(később)* más automata szolgáltató | Ugyanaz a minta, mint Foxpostnál, más adapterrel |

A megoldásnak support-olnia kell, hogy **új automata-szolgáltató bekötése ne igényeljen újabb
schema-migrációt**, csak egy új "handler" osztályt és egy admin-oldali enum értéket.

## 3. Adatmodell változások

### 3.1 `shipping_methods` — fulfillment típus

Új oszlop: `fulfillment_type` (string, enum-backed), lehetséges értékek:

- `courier` — mai futáros folyamat, cím a megrendelőtől
- `site_pickup` — telephelyi átvétel
- `parcel_locker` — csomagautomata (külső szolgáltatónál)

Ha `fulfillment_type = parcel_locker`, egy második oszlop (`locker_provider`, nullable string,
pl. `foxpost`) mondja meg, melyik adapter/widget tartozik hozzá. Így egy jövőbeli új automata
szolgáltató = **egy új `ShippingMethod` sor + egy új `locker_provider` érték + egy új handler
osztály**, nem új migráció.

```php
// app/Enums/ShippingFulfillmentType.php
enum ShippingFulfillmentType: string
{
    case Courier = 'courier';
    case SitePickup = 'site_pickup';
    case ParcelLocker = 'parcel_locker';
}
```

Migráció: `fulfillment_type` default `courier` (backfill a meglévő sorokra: "Házhozszállítás" →
`courier`, "Személyes átvétel" → `site_pickup`), `locker_provider` nullable.

### 3.2 `sites` — melyik telephely jelenjen meg átvételi pontként

Új oszlop: `is_pickup_point` (boolean, default `true`). Az admin ki tudja kapcsolni azokat a
telephelyeket (pl. tisztán raktár), amik nem alkalmasak ügyfél-átvételre, anélkül hogy az
`is_active`/`is_main` jelentését módosítanánk.

### 3.3 Új generikus `delivery_points` tábla

Ahelyett, hogy minden automata-szolgáltatóhoz külön táblát (pl. `foxpost_points`,
`packeta_points`, …) hoznánk létre, egyetlen generikus, önleíró táblát vezetünk be, ami a
checkout pillanatában kiválasztott pont **pillanatfelvételét** (snapshot) tárolja:

```php
Schema::create('delivery_points', function (Blueprint $table) {
    $table->id();
    $table->string('type');        // 'site' | 'foxpost' | (jövőbeli providerek)
    $table->string('reference_id')->nullable(); // site.id vagy a szolgáltató pontazonosítója (pl. Foxpost place_id)
    $table->string('name');
    $table->string('zip')->nullable();
    $table->string('city')->nullable();
    $table->string('address')->nullable();
    $table->decimal('lat', 10, 7)->nullable();
    $table->decimal('lng', 10, 7)->nullable();
    $table->json('raw_payload')->nullable(); // a szolgáltatótól kapott teljes nyers adat, auditálásra
    $table->timestamps();
});
```

Ez snapshot, nem live-referencia: ha egy Foxpost automata adatai később módosulnak (pl. nyitvatartás),
a már leadott rendelés megőrzi az eredeti állapotot — ez a számlázás/rendeléskövetés szempontjából
helyes viselkedés.

### 3.4 `orders` — kapcsolás a delivery point-hoz

Új oszlop: `delivery_point_id` (nullable FK → `delivery_points.id`, `nullOnDelete`).

A meglévő `shipping_name` / `shipping_city` / `shipping_zip` / `shipping_address` oszlopok
**megmaradnak és kitöltésre kerülnek automatikusan** a kiválasztott `delivery_point` adataiból is
(pl. `shipping_address = "Foxpost automata: Bp. XI. kerület, Fehérvári út 23."`), amikor nem
futár módot választanak. Ezzel:

- a visszaigazoló e-mail, a `confirmation.blade.php`, a Filament admin rendelés-nézet és a
  számlagenerálás **nem igényel módosítást** — ugyanazt az 1 db cím-blokkot olvassák, mint eddig.
- `delivery_point_id` csak a strukturált adatot (pl. jövőbeli "nyomtasd ki a Foxpost
  vonalkódot" funkcióhoz) adja hozzá pluszban.

Ez a lépés tartja alacsonyan a refaktor blast radius-át: a meglévő rendelés-megjelenítő kód
érintetlen marad.

## 4. Backend — fulfillment handler réteg

Strategy-pattern, egy interfész + egy osztály / `fulfillment_type`:

```php
interface ShippingFulfillmentHandler
{
    public function partialView(): string; // pl. 'storefront.checkout.fulfillment.site-pickup'
    public function rules(): array;        // conditional validation a StoreOrderRequesthez
    public function resolveDeliveryPoint(Request $request): ?DeliveryPoint;
}
```

- `CourierFulfillmentHandler` — nincs delivery point, a mai cím-mezők validálása marad.
- `SitePickupFulfillmentHandler` — `site_id` kötelező, létező aktív + `is_pickup_point=true` site;
  `resolveDeliveryPoint()` létrehoz/újrahasznosít egy `delivery_points` sort `type=site`.
- `FoxpostFulfillmentHandler` — a widget `postMessage` payloadjából kapott mezőket várja (ld. 5.
  pont), létrehoz egy `delivery_points` sort `type=foxpost`.

Egyszerű `match($shippingMethod->fulfillment_type) => ...` factory elég, nem kell DI container
bind — kevés eset van és lassan nő.

`CheckoutController::store()` és `StoreOrderRequest` módosul:

1. `$shippingMethod->fulfillment_type` alapján lekéri a handlert.
2. A handler `rules()`-át hozzáfűzi a validációhoz (a `shipping_address` mező validálása
   `required_if:fulfillment_type,courier`-re módosul).
3. `resolveDeliveryPoint()` → `delivery_point_id` + a snapshot cím-mezők kitöltése az `Order`-en.

## 5. Foxpost — konkrét integrációs adatok (research eredménye)

A Foxpost nyilvánosan dokumentált, **nem igényel API kulcsot** a pontválasztáshoz (az csak a
tényleges csomagfeladás/API-nak kell, ld. 7. pont):

- **Térkép widget**: iframe-be ágyazható, dokumentáció itt:
  `https://cdn.foxpost.hu/apt-finder/v1/documentation/`. A pontos embed URL a dokumentációs
  oldal forráskódjából (`data-embed-url` attribútum + az oldalon található élő demo iframe)
  **megerősítve**: `https://cdn.foxpost.hu/apt-finder/v1/app/` — paraméterek nélkül az
  alapértelmezett (magyar nyelvű, alap téma) nézetet adja, ahogy a döntés is szólt (9. pont).
  Implementálva: `config('services.foxpost.map_widget_url')`, alapértelmezetten erre az URL-re
  (env `FOXPOST_MAP_WIDGET_URL` felülírhatja). A kiválasztás `window.postMessage` eseményen
  keresztül érkezik a szülő oldalra — élesben letesztelve, a widget ténylegesen ezt az
  `event.origin`-t használja (fontos: a frontend kódnak ellenőriznie kell, hogy
  `event.origin === 'https://cdn.foxpost.hu'`, különben bármely más ablak/iframe is tudna
  hamis `delivery_point_payload`-ot injektálni):

  ```js
  window.addEventListener('message', function (event) {
      if (event.origin !== 'https://cdn.foxpost.hu') return;
      var apt = JSON.parse(event.data);
      // apt.place_id, apt.name, apt.address, apt.zip, apt.city, apt.street,
      // apt.geolat, apt.geolng, apt.variant (FOXPOST/Z-BOX/Z-Pont), apt.operator_id, ...
  });
  ```

  Visszaadott mezők (élő widget tesztből, Budapest IX. kerületi automatán kipróbálva):
  `place_id`, `operator_id`, `country`, `address`, `zip`, `city`, `street`, `geolat`, `geolng`,
  `name`, `open`, `load`, `apmType`, `isOutdoor`, `depot`, `allowed2`, `cardPayment`,
  `findme`, `substitutes`, `iconUrl`, `fillEmptyList`.

  → `FoxpostFulfillmentHandler::resolveDeliveryPoint()` ebből tölti fel a `delivery_points` sort:
  `reference_id = place_id`, `name`, `zip`, `city`, `address = street`, `lat = geolat`,
  `lng = geolng`, `raw_payload = teljes JSON`.

- **Csomagautomata lista JSON** (csak megjelenítéshez/kereséshez, alternatíva lehet a widgethez):
  `https://cdn.foxpost.hu/foxplus.json`.

- **Rendelésfeladó API** (nem kell most, csak jövőbeli fázis): Swagger dokumentáció
  `https://webapi.foxpost.hu/swagger-ui/index.html`, külön auth szükséges (API kulcs/szerződés a
  Foxposttal). Ez a *fizikai csomagfeladás* (vonalkód/matrica generálás) API-ja, nem a
  pontválasztás — **ebben a fázisban nincs rá szükség**, csak checkout-oldali pontválasztásra.

Gyakorlati megoldás a checkoutban: egy `<iframe>` nyitja meg a Foxpost widgetet (lazy, csak amikor
a user a Foxpost rádiógombot választja — hasonlóan, mint ma a fizetési módok JS-es
show/hide-ja), a szülő oldal JS-e feliratkozik a `message` eseményre, és a kapott JSON-t egy
hidden input-ba (`delivery_point_payload`) szerializálja submit előtt. Backend ezt a hidden
input JSON-t parse-olja és validálja (kötelező mezők: `place_id`, `name`, `zip`, `city`,
`address`/`street`).

Nincs szükség `config/foxpost.php`-ra vagy env változóra ebben a fázisban, mivel a widget és a
JSON lista publikus, kulcs nélküli. Ha majd a 7. pont API-ja bekötésre kerül, akkor kell
`FOXPOST_API_KEY` stb.

## 6. Frontend (checkout Blade + vanilla JS)

`resources/views/storefront/checkout/create.blade.php`:

- A szállítási mód rádiógombok kapnak egy `data-fulfillment-type="{{ $shippingMethod->fulfillment_type->value }}"`
  attribútumot (a mai `data-cost` mellett).
- Három új, alapból rejtett blokk (ugyanaz a mintázat, mint a payment-method show/hide JS-nél,
  kb. a mai 304–409. sorok logikája bővül):
  - `#fulfillment-courier` — a mai cím-mezők (nem új, csak átnevezve ebbe a blokkba)
  - `#fulfillment-site-pickup` — `<select name="delivery_point[site_id]">`, aktív +
    `is_pickup_point` site-okból
  - `#fulfillment-parcel-locker` — Foxpost iframe + hidden `delivery_point_payload` input
- JS: rádiógomb váltásnál a megfelelő blokk látszik, a többi `display:none` + a benne lévő
  mezők `disabled` (hogy ne menjenek fel validálásra feleslegesen — ugyanúgy, ahogy ma a nem
  releváns fizetési mód rádiógombokat kezeli a kód).

Nincs szükség Vue/Livewire bevezetésére — a meglévő vanilla JS minta elég ehhez a komplexitáshoz.

## 7. Admin (Filament)

- `app/Filament/Resources/ShippingMethods/Schemas/ShippingMethodForm.php`: új
  `Select::make('fulfillment_type')` (enum opciókkal) + feltételesen megjelenő
  `Select::make('locker_provider')` (`visible(fn ($get) => $get('fulfillment_type') === 'parcel_locker')`),
  jelenleg egyetlen opcióval: `foxpost`.
- `app/Filament/Resources/Sites/Schemas/SiteForm.php`: új `Toggle::make('is_pickup_point')`
  ("Megjelenjen átvételi pontként a checkoutban").
- `app/Filament/Resources/Orders/Schemas/OrderForm.php`: opcionális, nem kritikus — később
  megjelenítheti a `delivery_point` snapshot adatait read-only mezőkként.

## 8. Fázisok / sorrend

1. **Migrációk + modellek**: `fulfillment_type`/`locker_provider` a `shipping_methods`-on,
   `is_pickup_point` a `sites`-on, `delivery_points` tábla, `delivery_point_id` az `orders`-on.
   Seeder/backfill a meglévő két sorra.
2. **Handler réteg**: `ShippingFulfillmentType` enum + 3 handler osztály + factory.
3. **Backend validáció + mentés**: `StoreOrderRequest` conditional rules, `CheckoutController::store()`
   handler-alapú `delivery_point` feloldás és `Order` mezők kitöltése.
4. **Checkout UI**: site-pickup select + Foxpost iframe blokk + JS show/hide + hidden payload input.
5. **Admin form bővítés**: `fulfillment_type`/`locker_provider` a ShippingMethod formon,
   `is_pickup_point` a Site formon.
6. **Tesztek**: `StoreOrderRequest`/checkout feature teszt mindhárom fulfillment type-ra,
   `ShippingMethodResourceTest` bővítése az új mezőkre.
7. *(jövőbeli, külön munka, nincs ütemezve)*: valódi Foxpost csomagfeladó API bekötése
   (`webapi.foxpost.hu`), ha szükség lesz automatikus vonalkód/matrica generálásra.

## 9. Eldöntött kérdések

- **Foxpost iframe paraméterek**: nincs szükség semmilyen előre kitöltött paraméterre
  (város/irányítószám alapján szűrés) — az iframe statikus URL-lel nyílik, a vásárló a widgeten
  belül keres rá a kívánt automatára.
- **Telephelyi átvétel díja**: jelenleg ingyenes (`cost = 0` a `ShippingMethod` során, ahogy ma
  is) — nincs szükség telephelyenkénti eltérő díjra, az 5. pontban leírt egy-számos `cost` modell
  marad.

- **Automata-szolgáltatók rádiógombjai**: minden szolgáltató (Foxpost, és a jövőbeli újak) saját
  külön rádiógombot kap — nincs "Csomagpont" gyűjtőgomb több szolgáltatóval; 1 `ShippingMethod`
  sor = 1 szolgáltató, ahogy a 3.1/6. pont eredetileg is feltételezte.
