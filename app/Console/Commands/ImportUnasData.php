<?php

namespace App\Console\Commands;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportUnasData extends Command
{
    protected $signature = 'import:unas-data {--path= : Az old_data mappa elérési útja (alapértelmezett: base_path(\'old_data\'))}';

    protected $description = 'Kategóriák, termékek és termékképek importálása az UNAS webshopból exportált old_data mappából';

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
                    'is_active' => $row['Státusz'] === '1',
                ],
            );

            $productCount++;

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
     * @return list<array<string, string>>
     */
    private function readCsv(string $path): array
    {
        $handle = fopen($path, 'r');

        // BOM eltávolítása, különben az fgetcsv nem ismeri fel idézőjelesként az első oszlopot.
        if (fread($handle, 3) !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $header = fgetcsv($handle, separator: ';', enclosure: '"');

        $rows = [];

        while (($values = fgetcsv($handle, separator: ';', enclosure: '"')) !== false) {
            if ($values === [null]) {
                continue;
            }

            $rows[] = array_combine($header, array_map(trim(...), $values));
        }

        fclose($handle);

        return $rows;
    }
}
