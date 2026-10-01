# Egyszerű Filament webshop – terv

## Kiindulási állapot

- Laravel 13, PHP 8.3, Filament `^4.0` már composer függőség.
- Admin panel scaffoldolva: `App\Providers\Filament\AdminPanelProvider`, elérhető: `/admin`, `->login()` be van kapcsolva.
- Nincs még egyetlen domain modell, migráció (a `users`, `cache`, `jobs` táblákon kívül) vagy Filament resource.
- Teszt: Pest telepítve.

## Cél

Egy minimál, de éles használatra alkalmas webshop:
- **Admin oldal (Filament):** termékek, kategóriák, rendelések, vásárlók kezelése.
- **Storefront (Blade + Tailwind):** terméklista, termék oldal, kosár, checkout, rendelés visszaigazolás.
- Fizetés: induló verzióban **utánvét / banki átutalás** (nincs fizetési gateway integráció) – ez tartja egyszerűen a scope-ot. Stripe később, külön fázisban.

## Döntések (frissítve)

1. **Regisztráció kell.** Saját, egyszerű auth (Register/Login/Logout controller), nincs szükség Fortify/Breeze csomagra – csak 2-3 form.
2. **Külön `customers` tábla.** A `Customer` a rendelések valódi "tulajdonosa", a `User` csak a bejelentkezéshez kell. Egy `Customer`-hez opcionálisan tartozhat egy `User` (ha regisztrált), de vendégként (bejelentkezés nélkül) is létrejöhet `Customer` rekord a checkout során.
3. **Minden `Order`-hez kötelezően tartozik `Customer`** (`customer_id` NOT NULL FK) – nincs "árva" rendelés.
4. Egy pénznem (HUF), egy nyelv (magyar), nincs multi-tenant / multi-shop igény.
5. Egyszerű raktárkészlet (darabszám), nincs variáció (méret/szín) az MVP-ben.
6. **A kosár adatbázis-alapú** (`carts` / `cart_items` tábla) – nem vész el böngészőváltáskor/session lejáratkor, és bejelentkezve több eszközről is ugyanaz a kosár látszik.
7. Nincs kupon/kedvezmény rendszer az MVP-ben.
8. **A megrendelés-státuszok adminban (Filamentben) szabadon létrehozhatók/szerkeszthetők** – nincs kőbe vésett enum, saját `order_statuses` tábla.
9. **A termékképek saját `product_images` táblában** (nem MediaLibrary) – minden termékhez tartozik pontosan egy alapértelmezett kép, amit a storefront (katalógus, kártya) és az admin tábla listázás ezt jeleníti meg elsőként.

---

## 1. Adatmodell

### `Category`
- `id`, `name`, `slug`, `description` (nullable), `is_active` (bool), timestamps

### `Product`
- `id`, `category_id` (FK), `name`, `slug`, `description` (text), `price` (integer, fillér/Ft egészben), `stock` (integer), `sku` (nullable, unique), `is_active` (bool), timestamps
- Képek: ld. `ProductImage` lentebb. `Product::images()` (összes kép) és `Product::defaultImage()` (az alapértelmezett) relációk.

### `ProductImage`
- `id`, `product_id` (FK → products, `cascadeOnDelete()`), `path` (string – storage disk relatív útvonala), `alt_text` (nullable string), `sort_order` (int, default `0`), `is_default` (bool, default `false`), timestamps
- Egy termékhez több kép tartozhat, de **pontosan egy `is_default = true`** – ez jelenik meg a katalógusban, termékkártyán, admin táblázatban thumbnailként.
- Szabályok (modell/observer szinten, nem DB constraint, mert az egyszerűbb mint partial unique index):
  - Egy termék **első** feltöltött képe automatikusan `is_default = true` lesz.
  - Ha egy másik kép kap `is_default = true`-t, a termék többi képéről levesszük a flaget (csak egy lehet).
  - Ha az aktuális default kép törlődik, és van még kép a termékhez, a legkisebb `sort_order`-ű automatikusan átveszi a default szerepet.
- `Product::defaultImage()` → `hasOne(ProductImage::class)->where('is_default', true)`
- `Product::images()` → `hasMany(ProductImage::class)->orderBy('sort_order')`

### `Customer`
- `id`, `user_id` (nullable, unique FK → `users.id`, `nullOnDelete()`), `name`, `email` (unique), `phone` (nullable), timestamps
- Egy `User`-hez legfeljebb egy `Customer` tartozik (1:1, opcionális).
- Vendég checkoutnál: `email` alapján `firstOrCreate` – ha már létezik `Customer` ezzel az emaillel (korábbi vendégrendelésből), azt használjuk újra; ha a vásárló ezután regisztrál ugyanazzal az emaillel, a meglévő `Customer` rekordot kötjük a frissen létrehozott `User`-hez (nem jön létre duplikátum).

### `OrderStatus`
- `id`, `name` (pl. "Függőben", "Feldolgozás alatt", "Teljesítve", "Törölve"), `slug` (unique, kódból hivatkozható azonosító, pl. `pending`), `color` (Filament badge szín: `gray`/`warning`/`success`/`danger`/stb.), `sort_order` (int, admin lista és storefront megjelenítési sorrend), `is_default` (bool – ez kerül új rendelésre automatikusan), `is_final` (bool – lezárt állapot, pl. "Teljesítve"/"Törölve", innen már nincs tovább), timestamps
- Adminban (Filament `OrderStatusResource`) szabadon létrehozható, átnevezhető, színezhető, sorrendezhető új státusz.
- Pontosan egy `OrderStatus` lehet `is_default = true` (ezt validáljuk: új default mentésekor a többiről levesszük a flaget).
- Törlés védett, ha már van rá hivatkozó `Order` (`restrictOnDelete()` a FK-n + Filament oldali ellenőrzés is, hogy érthető hibaüzenet legyen).

### `Order`
- `id`, `customer_id` (**NOT NULL FK** → `customers.id`), `order_status_id` (**NOT NULL FK** → `order_statuses.id`, `restrictOnDelete()`), `order_number` (unique, pl. `ORD-20260930-0001`), `shipping_name`, `shipping_phone`, `shipping_address` (text/JSON), `payment_method` (enum: `cod`, `bank_transfer`), `subtotal`, `total`, timestamps
- Új rendelés mindig a `is_default = true` `OrderStatus`-szal jön létre.
- A `shipping_*` mezők az adott rendelés szállítási adatainak **pillanatfelvétele** (a `Customer` neve/címe később változhat, a korábbi rendelésen ettől függetlenül a leadáskori adat marad).

### `OrderItem`
- `id`, `order_id` (FK), `product_id` (FK), `product_name` (snapshot), `unit_price` (snapshot), `quantity`, `line_total`, timestamps

> Snapshot mezők (`product_name`, `unit_price`, `shipping_*`) azért kellenek, hogy a rendelés a termék/ügyfél későbbi módosítása után is konzisztens maradjon.

### `Cart`
- `id`, `customer_id` (nullable FK → `customers.id`), `guest_token` (nullable, string, unique – cookie-ban tárolt UUID a be nem jelentkezett látogatóknak), timestamps
- Pontosan egy a kettő közül mindig ki van töltve: vagy `customer_id`, vagy `guest_token`.
- Egy `Customer`-nek legfeljebb egy aktív kosara van (unique `customer_id`); egy `guest_token`-hez is legfeljebb egy (unique `guest_token`).

### `CartItem`
- `id`, `cart_id` (FK), `product_id` (FK), `quantity`, timestamps
- Unique `(cart_id, product_id)` – ugyanazt a terméket nem többször, hanem mennyiség-növeléssel adjuk hozzá.
- Az ár nem snapshot itt (a kosárban mindig az aktuális `Product.price`-t mutatjuk) – a checkoutnál kerül csak pillanatfelvételre az `OrderItem.unit_price`-ba.

### `User` (meglévő)
- Bejelentkezés/regisztráció (admin ÉS vásárló ugyanabban a `users` táblában, megkülönböztetés: `is_admin` bool mező, vagy admin csak azok akiknek van Filament panel hozzáférésük – ld. lentebb).
- `hasOne(Customer::class)` reláció.

---

## 2. Csomagok

| Csomag | Cél |
|---|---|
| `filament/filament` | már megvan – admin UI |
| `livewire/livewire` | Filament függősége, kosár/checkout Blade komponensekhez is jól jön |
| (opcionális, 2. fázis) `stripe/stripe-php` vagy `laravel/cashier` | online fizetés |

> Regisztráció/login: nincs külön csomag, saját `RegisterController` / `SessionController` (2-3 egyszerű Blade form + `Auth` facade).
> Termékképek: nincs külön csomag (nem MediaLibrary) – natív Laravel `Storage` (`public` disk, `php artisan storage:link`) + saját `product_images` tábla, Filamentben natív `FileUpload` komponenssel feltöltve.

### Admin vs. vásárló elkülönítése ugyanabban a `users` táblában

- `users` táblához új mező: `is_admin` (bool, default `false`).
- `User::canAccessPanel()` (Filament `FilamentUser` interface implementáció) → `return $this->is_admin;`
- Regisztrációs form mindig `is_admin = false`-t hoz létre → vásárló sosem fér hozzá a `/admin`-hoz.
- Admin usereket seederrel / `php artisan make:filament-user` + manuális `is_admin = true` frissítéssel hozzuk létre.

---

## 3. Filament admin resource-ok

- `CategoryResource` – lista, form (name, slug auto-generate, is_active)
- `ProductResource`
  - Form: name, slug, category select, price, stock, sku, description (RichEditor), is_active, + **`images` Repeater** (`Forms\Components\Repeater::make('images')->relationship()`, rendezhető (`reorderable` → `sort_order`), mezői: `FileUpload` (path), `alt_text`, `is_default` toggle – mentéskor model-eseménnyel biztosítjuk, hogy csak egy maradjon `is_default = true`)
  - Table: `defaultImage` thumbnail (`ImageColumn` a `default_image_url` accessor alapján), name, category, price, stock, is_active, szűrők (kategória, aktív/inaktív)
- `OrderStatusResource`
  - Form: name, slug (auto-generate name-ből), color (Filament `ColorPicker` vagy select a Filament szín-paletta nevei közül), sort_order, is_default (toggle – bekapcsoláskor a többiről automatikusan levesszük), is_final (toggle)
  - Table: színes badge előnézet, name, sort_order, is_default/is_final jelölők, hány `Order` hivatkozik rá (defenzív infó törlés előtt)
  - Delete action letiltva/hibaüzenet, ha van hozzá tartozó `Order`
- `OrderResource`
  - Lista: order_number, customer.name (relation), total, `order_status` badge (a saját `color` mezőjével), created_at
  - View/Edit: rendelési tételek (RelationManager: `OrderItemsRelationManager`, csak olvasható), státusz váltás a `OrderStatus` lista alapján feltöltött select-tel (nincs hardcode-olt státusz-lista a kódban)
  - Szűrők: `order_status`, dátum tartomány, ügyfél
- `CustomerResource`
  - Lista: name, email, phone, "regisztrált-e" (van-e `user_id`), rendelések száma, timestamps
  - View: profil adatok + `OrdersRelationManager` (az adott ügyfél összes rendelése)
  - Admin innen nem hoz létre új ügyfelet kézzel (az a regisztráció/checkout mellékhatása), de szerkeszteni/inaktiválni tudja
- Dashboard widget: napi/heti bevétel, új rendelések száma, legkeresettebb termékek (opcionális, 2. fázis)

---

## 4. Storefront (customer oldal)

Útvonalak (`routes/web.php`):
- `GET /` – kezdőlap, kiemelt/legújabb termékek
- `GET /termekek` – termékkatalógus, kategória szűréssel, lapozással
- `GET /termekek/{product:slug}` – termék részletes oldal
- `GET /kosar` – kosár megtekintése
- `POST /kosar/hozzaadas/{product}` – termék kosárba
- `PATCH /kosar/{product}` – mennyiség módosítás
- `DELETE /kosar/{product}` – törlés a kosárból
- `GET /penztar` – checkout form (bejelentkezett usernél előre kitöltve a `Customer` adataival; vendégnél név/email/telefon/cím + fizetési mód)
- `POST /penztar` – `Customer` `firstOrCreate(email)` + `Order` (kötelező `customer_id`-vel) + `OrderItem`-ek létrehozása, kosár ürítése, redirect a visszaigazolásra
- `GET /rendeles/{order:order_number}/visszaigazolas` – köszönő/összegző oldal
- `GET /regisztracio`, `POST /regisztracio` – `User` létrehozása (`is_admin=false`) + hozzá tartozó `Customer` `firstOrCreate(email)` és összekötése (`customer.user_id = user.id`)
- `GET /bejelentkezes`, `POST /bejelentkezes` – login
- `POST /kijelentkezes` – logout
- `GET /fiokom` (middleware: `auth`) – saját adatok szerkesztése + saját rendelések listája (`$request->user()->customer->orders`)

Implementáció:
- Terméklista/kártya mindenhol `product.defaultImage` alapján jelenít meg thumbnailt (fallback placeholder kép, ha egy terméknek nincs még képe); a termék részletes oldalán `product.images` (az összes kép, galéria/lightbox).
- **Kosár (`CartService`), adatbázis-alapú:**
  - Vendég látogatónak első kosárba-helyezéskor generálunk egy `guest_token` UUID-ot, ezt egy hosszú élettartamú (pl. 1 éves) titkosított cookie-ban tároljuk (`cart_token`), és erre jön létre a `Cart` rekord.
  - Bejelentkezett usernél a kosár mindig `customer_id` alapján töltődik be (`Cart::firstOrCreate(['customer_id' => $customer->id])`).
  - **Összefésülés belépéskor/regisztrációkor:** ha a böngészőben van `cart_token` cookie és ahhoz tartozik vendég `Cart`, annak `CartItem`-jeit átmásoljuk (mennyiségeket összeadva, ha már van azonos termék) a `Customer` kosarába, a vendég `Cart`-ot töröljük, a cookie-t pedig töröljük.
  - `CartItem` CRUD: hozzáadás (`updateOrCreate` + mennyiség increment), mennyiség módosítás, törlés – mindig a feloldott aktuális `Cart`-on.
- Checkout validáció: `StoreOrderRequest` (Form Request); ha a felhasználó be van jelentkezve, a `customer_id`-t a `$request->user()->customer->id`-ból vesszük, az email mező nem szerkeszthető.
- Ügyfél feloldás (vendég checkoutnál): `Customer::firstOrCreate(['email' => $email], [...])`.
- Rendelés létrehozás tranzakcióban (`DB::transaction`): `Cart` → `Order`/`OrderItem`-ek átalakítása (itt történik az árak pillanatfelvétele), készlet csökkentés (`Product::decrement('stock', ...)`), race condition ellen `lockForUpdate()` opcionálisan, végül a `Cart` (és `CartItem`-jei) törlése.
- Email értesítés (`OrderConfirmationMail`) a vásárlónak és/vagy admin értesítés – `Mailable` + queue.

---

## 5. Migrációk és seedelés sorrendje

1. `add_is_admin_to_users_table` (bool, default false)
2. `create_categories_table`
3. `create_products_table` (FK → categories)
4. `create_product_images_table` (FK → products)
5. `create_customers_table` (nullable, unique FK → users)
6. `create_order_statuses_table`
7. `create_orders_table` (NOT NULL FK → customers, NOT NULL FK → order_statuses)
8. `create_order_items_table` (FK → orders, products)
9. `create_carts_table` (nullable FK → customers, nullable `guest_token`)
10. `create_cart_items_table` (FK → carts, products)
11. Seeder: `OrderStatusSeeder` (induló készlet: Függőben[default] → Feldolgozás alatt → Teljesítve[final] / Törölve[final]), `CategorySeeder`, `ProductSeeder` + termékenként 1-3 `ProductImage` (Faker/placeholder képek, az első mindig `is_default = true`), admin `User` seeder (`is_admin = true`, `php artisan make:filament-user` helyette/mellette)

> Az `OrderStatusSeeder` csak a kezdő készletet hozza létre – ezek utána az adminban szabadon bővíthetők/átszerkeszthetők, a seeder nem egy zárt lista.

---

## 6. Tesztelés (Pest)

- Feature teszt: termék létrehozása admin oldalon keresztül (Filament resource teszt – Filament saját teszt helper-eit használva).
- Feature teszt: regisztráció → `User` + hozzá kötött `Customer` létrejön, `is_admin=false`, nincs hozzáférése az `/admin`-hoz.
- Feature teszt: vendég checkout → `Customer` (`user_id=null`) + `Order` (`customer_id` kitöltve) + `OrderItem`-ek létrejönnek, készlet csökken.
- Feature teszt: bejelentkezett vásárló checkoutja a saját `Customer` rekordjához köti a rendelést (nem hoz létre duplikátumot).
- Feature teszt: ugyanazzal az emaillel előbb vendégként rendel, majd regisztrál → a meglévő `Customer` rekord kapcsolódik az új `User`-hez, nem jön létre második `Customer`.
- Feature teszt: elfogyott készletű termék nem rendelhető.
- Feature teszt: vendégként kosárba helyezés (cookie `guest_token`) → bejelentkezés → a vendég kosár tételei átkerülnek a `Customer` kosarába, a vendég `Cart` törlődik.
- Feature teszt: checkoutnál a létrejövő `Order` az `is_default` `OrderStatus`-t kapja.
- Feature teszt: admin létrehoz egy új `OrderStatus`-t, és azt be tudja állítani egy rendelésen.
- Feature teszt: olyan `OrderStatus` törlése, amire van hivatkozó `Order`, elutasítva (validációs hiba, nem DB exception).
- Feature teszt: új `is_default = true` `OrderStatus` mentésekor a korábbi default automatikusan leváltódik (csak egy lehet default).
- Unit teszt: `CartService` (hozzáadás, mennyiség módosítás, törlés, összeg számítás, vendég↔ügyfél kosár összefésülés).
- Feature teszt: termékhez feltöltött első kép automatikusan `is_default = true` lesz.
- Feature teszt: egy másik kép `is_default = true`-ra állítása után a korábbi default leváltódik (csak egy marad).
- Feature teszt: az alapértelmezett kép törlése után a következő (legkisebb `sort_order`) kép veszi át a default szerepet.
- Feature teszt: terméklistázó/katalógus oldal a `defaultImage` alapján jeleníti meg a thumbnailt.

---

## 7. Fázisok / mérföldkövek

**1. fázis – Admin alapok**
- Migrációk (`is_admin`, `Category`, `Product`, `ProductImage`, `Customer`, `OrderStatus`, `Order`, `OrderItem`, `Cart`, `CartItem`) + modellek + relációk
- `CategoryResource`, `ProductResource`, `CustomerResource`, `OrderStatusResource` Filamentben
- `User::canAccessPanel()` az `is_admin` flagre kötve
- Seederek, admin user létrehozás

**2. fázis – Regisztráció + storefront + rendelés**
- Regisztráció / bejelentkezés / kijelentkezés (saját controllerek)
- Termékkatalógus és termék oldal (Blade + Tailwind)
- `CartService` + kosár UI
- Checkout flow (vendég és bejelentkezett vásárló is) + `OrderResource` az adminban
- `/fiokom` – saját adatok + saját rendelések
- Email visszaigazolás

**3. fázis – Csiszolás (opcionális)**
- Online fizetés (Stripe)
- Kuponok / kedvezmények
- Dashboard widgetek (bevétel, top termékek)
- Raktárkészlet riasztás (low stock notification Filamentben)

---

## 8. Nyitott kérdések (lezárva)

1. **Fizetés:** utánvét + banki átutalás induljon élesben, Stripe/Barion marad 3. fázis.
2. **Variáció:** nem kell, egyszerű (variáció nélküli) termék az MVP-ben.
3. **Design:** szabad kéz a Tailwind alapú UI-nál.
4. **Email-megerősítés:** kell – regisztráció után a fiók csak megerősítés után aktív (`MustVerifyEmail` a `User` modellen, Laravel beépített verification flow-ja: `email verification notification`, `/email/verify`, `/email/verify/{id}/{hash}`, védett route-ok `verified` middleware-rel).
