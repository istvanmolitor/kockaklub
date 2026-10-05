# {{ setting('company_name', 'Kockaklub') }}

> {{ setting('seo_home_description', 'Online társasjáték- és kiegészítő webshop.') }}

Kockaklub egy magyar nyelvű webshop, amely társasjátékokat, kártyajátékokat és kapcsolódó kiegészítőket árul.

## Főbb linkek

- Termékkatalógus: {{ route('catalog.index') }}
- Kapcsolat: {{ route('contact.create') }}
- Oldaltérkép (összes termék és kategória URL-je): {{ route('sitemap') }}

## Strukturált adatok

Minden termék- és kategóriaoldal `schema.org` JSON-LD adatokat (Product, Offer, BreadcrumbList) tartalmaz a `<head>`-ben, beleértve az árat, elérhetőséget (készlet) és cikkszámot. A főoldal Organization és WebSite JSON-LD adatokat tartalmaz.
