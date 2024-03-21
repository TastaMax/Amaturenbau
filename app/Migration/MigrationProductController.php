<?php

namespace App\Migration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWProduct;
use App\Models\SWProductClass;
use App\Models\SWVariantValue;
use League\Csv\Reader;

class MigrationProductController extends Controller
{
    private string $csvVariantsPath = 'migration/variants';
    private string $csvArticlespath = 'migration/export_print_podukte.csv';
    private int $batchSize = 5000; // Anzahl der Datensätze pro Batch
    private $productclass;
    private ShopWareHelperController $swHelper;

    public function __construct()
    {
        $this->swHelper = new ShopWareHelperController();
    }

    public function migrate()
    {
        //Abholen der Daten
        $csvArticles = $this->getFile(storage_path($this->csvArticlespath));
        $csvVariants = $this->getVariantsFiles();

        //Kategorien Abrufen
        $this->createProductClassCache();

        //Datensätze löschen
        SWProduct::truncate();
        SWVariantValue::truncate();

        $articles = $this->convertArticles($csvArticles, $csvVariants);

        foreach (array_chunk($articles[0], $this->batchSize) as $batch) {
            sleep(1);
            SWProduct::insert($batch);
        }

        unset($articles[0], $csvArticles, $csvVariants);

        foreach (array_chunk($articles[1], $this->batchSize) as $batch) {
            sleep(1);
            // Masseninsert der Produkte für den aktuellen Batch
            SWVariantValue::insert($batch);
        }

        return redirect('shopware/sync/')->with([
            'success' => "Produkte wurde erfolgreich migriert!"
        ]);
    }

    private function convertArticles($csvArticles, $csvVariants): array
    {
        $articles = [];
        $product = [];
        $productvariantvalues = [];
        $product_id = 1;
        try {
            $csv = $this->csv($csvArticles);

            foreach ($csv as $daten) {
                $articles[] = [
                    'id' => trim($daten['ID ']),
                    'rubrik' => trim($daten['Rubrik ']),
                    'title' => $this->removeBreaks(utf8_encode(trim($daten['Titel-DE ']))),
                    'category' => $this->removeBreaks(utf8_encode(trim($daten['Kategorie-DE '])))
                ];
            }
        } catch (\Exception $exception) {
            dd($exception);
        }

        foreach ($csvVariants as $csvVariant) {
            $filename = pathinfo($csvVariant, PATHINFO_FILENAME);
            $transformedName = $this->extractAndTransformFilename($filename);

            foreach ($articles as $article) {
                if ($transformedName == $article['rubrik']) {
                    $file = $this->getFile($csvVariant);
                    $csv = $this->csv($file);

                    foreach ($csv as $daten)
                    {
                        $productclass = $this->searchProductClass($article['title'], $article['category']);
                        $pos = 0;
                        foreach ($daten as $key => $value) {
                            if (utf8_encode(trim($key)) == 'Art.-Nr.' || utf8_encode(trim($key)) == 'Serie' || utf8_encode(trim($key)) == 'Brutto preis' || utf8_encode(trim($key)) == 'ID') {
                                continue;
                            }

                            $productvariantvalues[] = [
                                'swProduct_id' => $product_id,
                                'value' => utf8_encode(trim($value)),
                                'value_en' => utf8_encode(trim($value)),
                                'pos' => $pos,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                            $pos++;
                        }

                        $cleanedString = str_replace(',', '.', preg_replace('/[^0-9,]/', '', utf8_encode(trim($daten['Brutto preis']))));
                        $decimalValue = floatval($cleanedString);

                        //Artikel Anlegen
                        $product[] = [
                            'id' => $product_id,
                            'swProductClass_id' => $productclass,
                            'articlenumber' => utf8_encode(trim($daten['Art.-Nr.'])),
                            'serie' => utf8_encode(trim($daten['Serie'])),
                            'price' => $decimalValue,
                            'sw_id' => $this->swHelper->generateUUID(32),
                            'sw_edited' => true,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                        $product_id++;
                    }

                    unset($csv, $file);
                }
            }

            unset($filename, $transformedName);
        }

        return [$product, $productvariantvalues];
    }

    private function searchProductClass($productclass, $category): int
    {
        foreach ($this->productclass as $entry) {
            if ($entry['title'] == $productclass && $entry['category'] == $category) {
                return $entry['id'];
            }
        }
        dd('ERROR', $productclass, $category);
    }
    private function createProductClassCache(): true
    {
        $result = [];
        $productclasses = SWProductClass::with('subCategory')->get();

        foreach ($productclasses as $productclass) {
            if(is_null($productclass->subCategory))
            {
                dd($productclass);
            }
            $result[] = [
                'id' => $productclass->id,
                'title' => $productclass->title,
                'category' =>  $productclass->subCategory->title
            ];
        }
        $this->productclass = $result;
        return true;
    }
    private function csv($csv)
    {
        $csv = Reader::createFromString($csv);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);
        return $csv;
    }

    private function getFile($filepath): ?string
    {
        return file_get_contents($filepath);
    }

    private function getVariantsFiles(): bool|array
    {
        return glob(storage_path("$this->csvVariantsPath/*.csv"));
    }

    private function removeBreaks($string): string
    {
        return trim(str_replace(["\r", "\n"], '', $string));
    }

    private function extractAndTransformFilename(string $filename): string
    {
        // Extrahiere den Dateinamen ohne die Erweiterung
        $filenameWithoutExtension = pathinfo($filename, PATHINFO_FILENAME);

        // Trenne den Dateinamen anhand des "__" und nimm den ersten Teil
        $firstPart = explode('__', $filenameWithoutExtension)[0];

        // Ersetze das "-" durch einen Punkt
        $transformedName = str_replace('-', '.', $firstPart);

        return $transformedName;
    }
}
