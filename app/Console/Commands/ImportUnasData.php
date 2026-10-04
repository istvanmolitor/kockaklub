<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\Site;
use App\Models\StockMovement;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportUnasData extends Command
{
    protected $signature = 'import:unas-data {--path= : Az old_data mappa elérési útja (alapértelmezett: base_path(\'old_data\'))}';

    protected $description = 'Kategóriák, termékek és termékképek importálása az UNAS webshopból exportált old_data mappából';

    /**
     * Az UNAS CSV-ben "Paraméter: <Név>||enum" formátumban szereplő oszlopok,
     * amelyeket termék tulajdonságként (ProductAttribute) importálunk.
     */
    private const ATTRIBUTE_COLUMNS = [
        'Paraméter: Márka||enum' => 'Márka',
        'Paraméter: Szín||enum' => 'Szín',
        'Paraméter: Matrica||enum' => 'Matrica',
    ];

    public function handle(): int
    {
        $basePath = $this->option('path') ?: base_path('old_data');

        $categoriesCsv = $basePath.'/categories.csv';
        $productsCsv = $basePath.'/products.csv';
        $imagesPath = $basePath.'/product_images';

        if (! is_file($categoriesCsv) || ! is_file($productsCsv)) {
            $this->error("Nem található categories.csv / products.csv ebben a mappában: {$basePath}");

            return self::FAILURE;
        }

        $this->components->info('Kategóriák importálása...');
        $categoryCount = $this->importCategories($categoriesCsv);
        $this->components->info("{$categoryCount} kategória importálva.");

        $this->components->info('Termékek importálása...');
        [$productCount, $imageCount] = $this->importProducts($productsCsv, $imagesPath);
        $this->components->info("{$productCount} termék és {$imageCount} termékkép importálva.");

        $stockCsv = $basePath.'/keszlet.csv';

        if (is_file($stockCsv)) {
            $this->components->info('Készlet importálása...');
            [$stockItemCount, $stockSkippedCount] = $this->importStock($stockCsv);
            $this->components->info("{$stockItemCount} készlet tétel importálva, {$stockSkippedCount} kihagyva.");
        }

        return self::SUCCESS;
    }

    private function importCategories(string $csvPath): int
    {
        $rows = $this->readCsv($csvPath);
        $count = 0;

        DB::transaction(function () use ($rows, &$count) {
            // Első körben a szülő nélküli (top-level) kategóriákat hozzuk létre,
            // hogy a második körben az alkategóriák már tudjanak rájuk hivatkozni.
            $byName = [];

            foreach ($rows as $row) {
                if ($row['Szülő kategória'] === '') {
                    $category = $this->upsertCategory($row, null);
                    $byName[$row['Kategória neve']] = $category;
                    $count++;
                }
            }

            foreach ($rows as $row) {
                if ($row['Szülő kategória'] === '') {
                    continue;
                }

                $parent = $byName[$row['Szülő kategória']] ?? null;
                $category = $this->upsertCategory($row, $parent?->id);
                $byName[$row['Kategória neve']] = $category;
                $count++;
            }
        });

        return $count;
    }

    /**
     * @param  array<string, string>  $row
     */
    private function upsertCategory(array $row, ?int $parentId): Category
    {
        $slug = $row['SEF URL'] !== '' ? $row['SEF URL'] : Str::slug($row['Kategória neve']);

        return Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $row['Kategória neve'],
                'parent_id' => $parentId,
                'is_active' => $row['Megjelenjen a kategória oldalon'] === '1',
            ],
        );
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function importProducts(string $csvPath, string $imagesPath): array
    {
        $rows = $this->readCsv($csvPath);
        $categoryIds = $this->categoryIdsByPath();
        $attributesByColumn = $this->productAttributesByColumn();

        $productCount = 0;
        $imageCount = 0;

        $progress = $this->output->createProgressBar(count($rows));
        $progress->start();

        foreach ($rows as $row) {
            $categoryPath = array_map('trim', explode('|', $row['Kategória']));
            $categoryId = $categoryIds[implode('|', $categoryPath)] ?? null;

            if ($categoryId === null) {
                $this->components->warn("Ismeretlen kategória ({$row['Kategória']}) a(z) {$row['Cikkszám']} termékhez, kihagyva.");
                $progress->advance();

                continue;
            }

            $slug = $this->uniqueProductSlug($row);

            $product = Product::query()->updateOrCreate(
                ['sku' => $row['Cikkszám']],
                [
                    'category_id' => $categoryId,
                    'name' => $row['Termék Név'],
                    'slug' => $slug,
                    'description' => $row['Rövid Leírás'] !== '' ? $row['Rövid Leírás'] : null,
                    'price' => (int) round((float) $row['Bruttó Ár']),
                    'weight' => $this->parseWeight($row['Tömeg']),
                    'is_active' => $row['Státusz'] === '1',
                ],
            );

            $productCount++;

            $this->syncProductAttributeValues($product, $row, $attributesByColumn);

            if ($this->importProductImage($product, $row['Kép link'], $imagesPath)) {
                $imageCount++;
            }

            $progress->advance();
        }

        $progress->finish();
        $this->newLine();

        return [$productCount, $imageCount];
    }

    /**
     * @param  array<string, string>  $row
     */
    private function uniqueProductSlug(array $row): string
    {
        $slug = $row['SEF URL'] !== '' ? $row['SEF URL'] : Str::slug($row['Termék Név']);

        $exists = Product::query()
            ->where('slug', $slug)
            ->where('sku', '!=', $row['Cikkszám'])
            ->exists();

        return $exists ? $slug.'-'.Str::slug($row['Cikkszám']) : $slug;
    }

    /**
     * A CSV "Tömeg" oszlopa kg-ban adja meg a súlyt; 0 vagy üres érték azt jelenti,
     * hogy a súly nincs megadva az UNAS-ban, ezért ilyenkor null-t adunk vissza.
     */
    private function parseWeight(string $value): ?float
    {
        $weight = (float) $value;

        return $weight > 0 ? $weight : null;
    }

    private function importProductImage(Product $product, string $imageUrl, string $imagesPath): bool
    {
        if ($imageUrl === '' || $product->images()->exists()) {
            return false;
        }

        $filename = basename(parse_url($imageUrl, PHP_URL_PATH) ?: '');
        $sourcePath = $imagesPath.'/'.$filename;

        if ($filename === '' || ! is_file($sourcePath)) {
            return false;
        }

        $storagePath = 'product-images/'.$filename;
        Storage::disk('public')->put($storagePath, file_get_contents($sourcePath));

        $product->images()->create([
            'path' => $storagePath,
            'alt_text' => $product->name,
            'sort_order' => 0,
            'is_default' => true,
        ]);

        return true;
    }

    /**
     * Létrehozza (vagy visszaadja) a Márka/Szín/Matrica termék tulajdonságokat,
     * CSV oszlopnév szerint indexelve.
     *
     * @return array<string, ProductAttribute>
     */
    private function productAttributesByColumn(): array
    {
        $byColumn = [];

        foreach (self::ATTRIBUTE_COLUMNS as $column => $name) {
            $byColumn[$column] = ProductAttribute::query()->updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'allow_multiple' => false],
            );
        }

        return $byColumn;
    }

    /**
     * A sorban szereplő Márka/Szín/Matrica paraméter értékeket termék tulajdonság
     * értékként (ProductAttributeValue) hozza létre, és a termékhez szinkronizálja.
     *
     * @param  array<string, string>  $row
     * @param  array<string, ProductAttribute>  $attributesByColumn
     */
    private function syncProductAttributeValues(Product $product, array $row, array $attributesByColumn): void
    {
        $attributeIds = [];
        $targetValueIds = [];

        foreach ($attributesByColumn as $column => $attribute) {
            $attributeIds[] = $attribute->id;

            $value = trim($row[$column] ?? '');

            if ($value === '') {
                continue;
            }

            $targetValueIds[] = $attribute->values()->firstOrCreate(['value' => $value])->id;
        }

        $currentValueIds = $product->attributeValues()
            ->whereIn('product_attribute_id', $attributeIds)
            ->pluck('product_attribute_values.id')
            ->all();

        $product->attributeValues()->detach(array_diff($currentValueIds, $targetValueIds));
        $product->attributeValues()->syncWithoutDetaching($targetValueIds);
    }

    /**
     * A kategóriafát "Szülő|Gyerek" (illetve szülő nélkül csak "Szülő") útvonal
     * szerint indexeli, hogy a termékek "Kategória" oszlopa alapján egyértelműen
     * visszakereshető legyen a megfelelő category_id.
     *
     * @return array<string, int>
     */
    private function categoryIdsByPath(): array
    {
        $categories = Category::query()->get(['id', 'name', 'parent_id'])->keyBy('id');
        $byPath = [];

        foreach ($categories as $category) {
            $path = $category->parent_id !== null && $categories->has($category->parent_id)
                ? $categories[$category->parent_id]->name.'|'.$category->name
                : $category->name;

            $byPath[$path] = $category->id;
        }

        return $byPath;
    }

    /**
     * A keszlet.csv-ből egy nyitott (lezáratlan) IN típusú készletmozgatást hoz
     * létre a fő telephely első régiójába. Soronkénti cikkszám: az egyedi
     * vonalkód, ha az nincs megadva, akkor az eredeti vonalkód.
     *
     * @return array{0: int, 1: int} [importált tételek száma, kihagyott sorok száma]
     */
    private function importStock(string $csvPath): array
    {
        $rows = $this->readCsv($csvPath, ',');

        $region = Site::main()?->regions()->orderBy('id')->first();

        if (! $region) {
            $this->components->warn('Nincs fő telephely vagy régió, a készlet importálása kimarad.');

            return [0, 0];
        }

        $movement = StockMovement::create([
            'type' => StockMovement::TYPE_IN,
            'destination_region_id' => $region->id,
            'movement_date' => now(),
            'note' => 'UNAS készlet import',
        ]);

        $itemCount = 0;
        $skippedCount = 0;

        foreach ($rows as $row) {
            $quantity = (int) ($row['db'] ?? '');

            if ($quantity <= 0) {
                continue;
            }

            $sku = $row['Egyedi vonalkód'] !== '' ? $row['Egyedi vonalkód'] : $row['Eredeti vonalkód'];

            if ($sku === '') {
                continue;
            }

            $product = Product::query()->where('sku', $sku)->first();

            if (! $product) {
                $this->components->warn("Ismeretlen cikkszám ({$sku}) a(z) \"{$row['Név']}\" készlet sorhoz, kihagyva.");
                $skippedCount++;

                continue;
            }

            $movement->items()->create([
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);

            $itemCount++;
        }

        if ($itemCount === 0) {
            $movement->delete();
        }

        return [$itemCount, $skippedCount];
    }

    /**
     * @return list<array<string, string>>
     */
    private function readCsv(string $path, string $separator = ';'): array
    {
        $handle = fopen($path, 'r');

        // BOM eltávolítása, különben az fgetcsv nem ismeri fel idézőjelesként az első oszlopot.
        if (fread($handle, 3) !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, separator: $separator, enclosure: '"');

        $rows = [];

        while (($values = fgetcsv($handle, separator: $separator, enclosure: '"')) !== false) {
            if ($values === [null]) {
                continue;
            }

            $rows[] = array_combine($header, array_map(trim(...), $values));
        }

        fclose($handle);

        return $rows;
    }
}
