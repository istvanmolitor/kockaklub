<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'A(z) :attribute mezőt el kell fogadni.',
    'accepted_if' => 'A(z) :attribute mezőt el kell fogadni, ha a(z) :other értéke :value.',
    'active_url' => 'A(z) :attribute mező nem érvényes URL-cím.',
    'after' => 'A(z) :attribute mezőnek :date utáni dátumnak kell lennie.',
    'after_or_equal' => 'A(z) :attribute mezőnek :date vagy azutáni dátumnak kell lennie.',
    'alpha' => 'A(z) :attribute mező csak betűket tartalmazhat.',
    'alpha_dash' => 'A(z) :attribute mező csak betűket, számokat, kötőjeleket és aláhúzásokat tartalmazhat.',
    'alpha_num' => 'A(z) :attribute mező csak betűket és számokat tartalmazhat.',
    'any_of' => 'A(z) :attribute mező érvénytelen.',
    'array' => 'A(z) :attribute mezőnek listának kell lennie.',
    'array_keys' => 'A(z) :attribute mező csak a következő kulcsokat tartalmazhatja: :values.',
    'ascii' => 'A(z) :attribute mező csak egybájtos alfanumerikus karaktereket és szimbólumokat tartalmazhat.',
    'base64' => 'A(z) :attribute mezőnek érvényes Base64 karakterláncnak kell lennie.',
    'before' => 'A(z) :attribute mezőnek :date előtti dátumnak kell lennie.',
    'before_or_equal' => 'A(z) :attribute mezőnek :date vagy azelőtti dátumnak kell lennie.',
    'between' => [
        'array' => 'A(z) :attribute mezőnek :min és :max elem között kell lennie.',
        'file' => 'A(z) :attribute mezőnek :min és :max kilobájt között kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek :min és :max között kell lennie.',
        'string' => 'A(z) :attribute mezőnek :min és :max karakter között kell lennie.',
    ],
    'boolean' => 'A(z) :attribute mezőnek igaznak vagy hamisnak kell lennie.',
    'can' => 'A(z) :attribute mező jogosulatlan értéket tartalmaz.',
    'confirmed' => 'A(z) :attribute mező megerősítése nem egyezik.',
    'contains' => 'A(z) :attribute mezőből hiányzik egy kötelező érték.',
    'current_password' => 'A megadott jelszó helytelen.',
    'date' => 'A(z) :attribute mezőnek érvényes dátumnak kell lennie.',
    'date_equals' => 'A(z) :attribute mezőnek :date dátummal egyezőnek kell lennie.',
    'date_format' => 'A(z) :attribute mezőnek a(z) :format formátumnak kell megfelelnie.',
    'decimal' => 'A(z) :attribute mezőnek :decimal tizedesjegyet kell tartalmaznia.',
    'declined' => 'A(z) :attribute mezőt el kell utasítani.',
    'declined_if' => 'A(z) :attribute mezőt el kell utasítani, ha a(z) :other értéke :value.',
    'different' => 'A(z) :attribute és a(z) :other mezőknek különbözniük kell.',
    'digits' => 'A(z) :attribute mezőnek :digits számjegyből kell állnia.',
    'digits_between' => 'A(z) :attribute mezőnek :min és :max számjegy között kell lennie.',
    'dimensions' => 'A(z) :attribute mező képmérete érvénytelen.',
    'distinct' => 'A(z) :attribute mező duplikált értéket tartalmaz.',
    'doesnt_contain' => 'A(z) :attribute mező nem tartalmazhatja a következők egyikét sem: :values.',
    'doesnt_end_with' => 'A(z) :attribute mező nem végződhet a következők egyikére sem: :values.',
    'doesnt_start_with' => 'A(z) :attribute mező nem kezdődhet a következők egyikével sem: :values.',
    'email' => 'A(z) :attribute mezőnek érvényes e-mail címnek kell lennie.',
    'encoding' => 'A(z) :attribute mezőnek :encoding kódolásúnak kell lennie.',
    'ends_with' => 'A(z) :attribute mezőnek a következők egyikére kell végződnie: :values.',
    'enum' => 'A kiválasztott :attribute érvénytelen.',
    'exists' => 'A kiválasztott :attribute érvénytelen.',
    'extensions' => 'A(z) :attribute mezőnek a következő kiterjesztések egyikével kell rendelkeznie: :values.',
    'file' => 'A(z) :attribute mezőnek fájlnak kell lennie.',
    'filled' => 'A(z) :attribute mezőnek értéket kell tartalmaznia.',
    'gt' => [
        'array' => 'A(z) :attribute mezőnek több mint :value elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek nagyobbnak kell lennie, mint :value kilobájt.',
        'numeric' => 'A(z) :attribute mezőnek nagyobbnak kell lennie, mint :value.',
        'string' => 'A(z) :attribute mezőnek hosszabbnak kell lennie, mint :value karakter.',
    ],
    'gte' => [
        'array' => 'A(z) :attribute mezőnek legalább :value elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek legalább :value kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek legalább :value-nak kell lennie.',
        'string' => 'A(z) :attribute mezőnek legalább :value karakter hosszúnak kell lennie.',
    ],
    'hex_color' => 'A(z) :attribute mezőnek érvényes hexadecimális színkódnak kell lennie.',
    'image' => 'A(z) :attribute mezőnek képnek kell lennie.',
    'in' => 'A kiválasztott :attribute érvénytelen.',
    'in_array' => 'A(z) :attribute mezőnek szerepelnie kell a(z) :other mezőben.',
    'in_array_keys' => 'A(z) :attribute mezőnek legalább az egyik kulcsot tartalmaznia kell: :values.',
    'integer' => 'A(z) :attribute mezőnek egész számnak kell lennie.',
    'ip' => 'A(z) :attribute mezőnek érvényes IP-címnek kell lennie.',
    'ipv4' => 'A(z) :attribute mezőnek érvényes IPv4-címnek kell lennie.',
    'ipv6' => 'A(z) :attribute mezőnek érvényes IPv6-címnek kell lennie.',
    'json' => 'A(z) :attribute mezőnek érvényes JSON karakterláncnak kell lennie.',
    'list' => 'A(z) :attribute mezőnek listának kell lennie.',
    'lowercase' => 'A(z) :attribute mezőnek kisbetűsnek kell lennie.',
    'lt' => [
        'array' => 'A(z) :attribute mezőnek kevesebb mint :value elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek kisebbnek kell lennie, mint :value kilobájt.',
        'numeric' => 'A(z) :attribute mezőnek kisebbnek kell lennie, mint :value.',
        'string' => 'A(z) :attribute mezőnek rövidebbnek kell lennie, mint :value karakter.',
    ],
    'lte' => [
        'array' => 'A(z) :attribute mező nem tartalmazhat :value elemnél többet.',
        'file' => 'A(z) :attribute mezőnek legfeljebb :value kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek legfeljebb :value-nak kell lennie.',
        'string' => 'A(z) :attribute mezőnek legfeljebb :value karakter hosszúnak kell lennie.',
    ],
    'mac_address' => 'A(z) :attribute mezőnek érvényes MAC-címnek kell lennie.',
    'max' => [
        'array' => 'A(z) :attribute mező nem tartalmazhat :max elemnél többet.',
        'file' => 'A(z) :attribute mező nem lehet nagyobb, mint :max kilobájt.',
        'numeric' => 'A(z) :attribute mező nem lehet nagyobb, mint :max.',
        'string' => 'A(z) :attribute mező nem lehet hosszabb, mint :max karakter.',
    ],
    'max_digits' => 'A(z) :attribute mező nem tartalmazhat :max számjegynél többet.',
    'mimes' => 'A(z) :attribute mezőnek a következő típusú fájlnak kell lennie: :values.',
    'mimetypes' => 'A(z) :attribute mezőnek a következő típusú fájlnak kell lennie: :values.',
    'min' => [
        'array' => 'A(z) :attribute mezőnek legalább :min elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek legalább :min kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek legalább :min-nek kell lennie.',
        'string' => 'A(z) :attribute mezőnek legalább :min karakter hosszúnak kell lennie.',
    ],
    'min_digits' => 'A(z) :attribute mezőnek legalább :min számjegyet kell tartalmaznia.',
    'missing' => 'A(z) :attribute mezőnek hiányoznia kell.',
    'missing_if' => 'A(z) :attribute mezőnek hiányoznia kell, ha a(z) :other értéke :value.',
    'missing_unless' => 'A(z) :attribute mezőnek hiányoznia kell, hacsak a(z) :other értéke nem :value.',
    'missing_with' => 'A(z) :attribute mezőnek hiányoznia kell, ha a(z) :values jelen van.',
    'missing_with_all' => 'A(z) :attribute mezőnek hiányoznia kell, ha a(z) :values mind jelen vannak.',
    'multiple_of' => 'A(z) :attribute mezőnek :value többszörösének kell lennie.',
    'not_in' => 'A kiválasztott :attribute érvénytelen.',
    'not_regex' => 'A(z) :attribute mező formátuma érvénytelen.',
    'numeric' => 'A(z) :attribute mezőnek számnak kell lennie.',
    'password' => [
        'letters' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy betűt.',
        'mixed' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy nagy- és egy kisbetűt.',
        'numbers' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy számot.',
        'symbols' => 'A(z) :attribute mezőnek tartalmaznia kell legalább egy szimbólumot.',
        'uncompromised' => 'A megadott :attribute szerepel egy korábbi adatszivárgásban. Kérjük, válassz másik :attribute.',
    ],
    'present' => 'A(z) :attribute mezőnek jelen kell lennie.',
    'present_if' => 'A(z) :attribute mezőnek jelen kell lennie, ha a(z) :other értéke :value.',
    'present_unless' => 'A(z) :attribute mezőnek jelen kell lennie, hacsak a(z) :other értéke nem :value.',
    'present_with' => 'A(z) :attribute mezőnek jelen kell lennie, ha a(z) :values jelen van.',
    'present_with_all' => 'A(z) :attribute mezőnek jelen kell lennie, ha a(z) :values mind jelen vannak.',
    'prohibited' => 'A(z) :attribute mező tiltott.',
    'prohibited_if' => 'A(z) :attribute mező tiltott, ha a(z) :other értéke :value.',
    'prohibited_if_accepted' => 'A(z) :attribute mező tiltott, ha a(z) :other el van fogadva.',
    'prohibited_if_declined' => 'A(z) :attribute mező tiltott, ha a(z) :other el van utasítva.',
    'prohibited_unless' => 'A(z) :attribute mező tiltott, hacsak a(z) :other nincs a(z) :values között.',
    'prohibits' => 'A(z) :attribute mező megtiltja, hogy a(z) :other jelen legyen.',
    'regex' => 'A(z) :attribute mező formátuma érvénytelen.',
    'required' => 'A(z) :attribute mező kitöltése kötelező.',
    'required_array_keys' => 'A(z) :attribute mezőnek tartalmaznia kell a következő bejegyzéseket: :values.',
    'required_if' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :other értéke :value.',
    'required_if_accepted' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :other el van fogadva.',
    'required_if_declined' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :other el van utasítva.',
    'required_unless' => 'A(z) :attribute mező kitöltése kötelező, hacsak a(z) :other nincs a(z) :values között.',
    'required_with' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :values jelen van.',
    'required_with_all' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :values mind jelen vannak.',
    'required_without' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :values nincs jelen.',
    'required_without_all' => 'A(z) :attribute mező kitöltése kötelező, ha a(z) :values egyike sincs jelen.',
    'same' => 'A(z) :attribute mezőnek egyeznie kell a(z) :other mezővel.',
    'size' => [
        'array' => 'A(z) :attribute mezőnek :size elemet kell tartalmaznia.',
        'file' => 'A(z) :attribute mezőnek :size kilobájtnak kell lennie.',
        'numeric' => 'A(z) :attribute mezőnek :size-nak kell lennie.',
        'string' => 'A(z) :attribute mezőnek :size karakter hosszúnak kell lennie.',
    ],
    'starts_with' => 'A(z) :attribute mezőnek a következők egyikével kell kezdődnie: :values.',
    'string' => 'A(z) :attribute mezőnek szövegnek kell lennie.',
    'timezone' => 'A(z) :attribute mezőnek érvényes időzónának kell lennie.',
    'unique' => 'A(z) :attribute már foglalt.',
    'uploaded' => 'A(z) :attribute feltöltése sikertelen.',
    'uppercase' => 'A(z) :attribute mezőnek nagybetűsnek kell lennie.',
    'url' => 'A(z) :attribute mezőnek érvényes URL-nek kell lennie.',
    'ulid' => 'A(z) :attribute mezőnek érvényes ULID-nak kell lennie.',
    'uuid' => 'A(z) :attribute mezőnek érvényes UUID-nak kell lennie.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'name' => 'név',
        'email' => 'e-mail cím',
        'password' => 'jelszó',
        'password_confirmation' => 'jelszó megerősítése',
        'phone' => 'telefonszám',
        'quantity' => 'mennyiség',
        'message' => 'üzenet',
        'remember' => 'emlékezz rám',
        'shipping_name' => 'szállítási név',
        'shipping_phone' => 'szállítási telefonszám',
        'shipping_country' => 'szállítási ország',
        'shipping_city' => 'szállítási város',
        'shipping_zip' => 'szállítási irányítószám',
        'shipping_address' => 'szállítási cím',
        'shipping_method_id' => 'szállítási mód',
        'billing_name' => 'számlázási név',
        'billing_country' => 'számlázási ország',
        'billing_city' => 'számlázási város',
        'billing_zip' => 'számlázási irányítószám',
        'billing_address' => 'számlázási cím',
        'billing_tax_number' => 'adószám',
        'billing_same_as_shipping' => 'a számlázási cím megegyezik a szállítási címmel',
        'payment_method_id' => 'fizetési mód',
    ],

];
