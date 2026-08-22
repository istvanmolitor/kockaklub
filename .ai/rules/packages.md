---
paths:
  - 'packages/**'
---

# Packages

## packages/ mappa: helyi composer path repo, git-ignored, saját repókkal
A packages/ alatti csomagok (istvanmolitor/*) mindegyike saját git repóval rendelkezik és a gyökér composer.json-ban egy path repository ("repositories.packages": {"type":"path","url":"packages/*"}) köti be őket symlinkkel a vendor alá, "*@dev" constrainttel.

A packages/ mappa tartalma NINCS commitolva a kockaklub repóba (.gitignore: "/packages/*" + "!/packages/.gitkeep") — csak egy .gitkeep tartja meg a mappát üresen klónozás után. Új gépen/klónozás után a csomagokat külön kell behúzni (pl. saját repóikból) mielőtt composer install lefut, különben a path repository nem talál semmit.

A "shop" csomag jelenleg NINCS require-elve, mert az istvanmolitor/stock függőségét ez a projekt nem tartalmazza (találtunk egy stock mappát egy másik projektben: /home/molitor/work/molitor/packages/stock, de ide nem lett átmásolva).

A packages/contact composer.json-jában a "laravel/framework" constraint bővítve lett "^11.0|^12.0|^13.0"-ra, hogy kompatibilis legyen ezzel a Laravel 13-as projekttel (ez a contact csomag saját git repójában külön commitolandó/pusholandó, mivel az egy önálló repo).
