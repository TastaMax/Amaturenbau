<?php

namespace App\Migration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWPicture;
use App\Models\SWProductClass;
use App\Models\SWVariantHeader;
use App\Models\SWSubCategory;
use League\Csv\Reader;

class MigrationProductclassController extends Controller
{
    private string $csvVariantsPath = 'migration/variants';
    private string $csvArticlespath = 'migration/export_print_podukte.csv';
    private int $batchSize = 5000; // Anzahl der Datensätze pro Batch
    private $subcategory;
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
        $this->createSubCategoryCache();

        //Datensätze löschen
        SWProductClass::truncate();
        SWVariantHeader::truncate();

        //Produkte anlegen
        $productclasses = $this->convertProductclass($csvArticles, $csvVariants);
        foreach (array_chunk($productclasses, $this->batchSize) as $batch) {
            sleep(1);
            foreach ($batch as $entry)
            {
                $SWProductClass = new SWProductClass();
                $SWProductClass->id = $entry['id'];
                $SWProductClass->swSubCategory_id = $entry['swSubCategory_id'];
                $SWProductClass->title = $entry['title'];
                $SWProductClass->title_en = $entry['title_en'];
                $SWProductClass->description = $entry['description'];
                $SWProductClass->description_en = $entry['description_en'];
                $SWProductClass->sw_id = $this->swHelper->generateUUID(32);
                $SWProductClass->sw_edited = true;
                $SWProductClass->save();
                $id = $SWProductClass->id;

                $SWPicture = new SWPicture();
                $SWPicture->type = 1;
                $SWPicture->assignment_id = $id;
                $SWPicture->file = $entry['image'];
                $SWPicture->path = '';
                $SWPicture->pos = 0;
                $SWPicture->save();

                $pos = 0;
                foreach ($entry['headers'] as $datasheetHeader) {
                    $SWVariantHeader = new SWVariantHeader();
                    $SWVariantHeader->swProductClass_id = $id;
                    $SWVariantHeader->title = $datasheetHeader;
                    $SWVariantHeader->title_en = $datasheetHeader;
                    $SWVariantHeader->pos = $pos;
                    $SWVariantHeader->save();
                    $pos++;
                }
            }
        }

        return redirect('shopware/sync/')->with([
            'success' => "Produktklassen wurde erfolgreich migriert!"
        ]);
    }

    private function convertImages($csvArticles, $csvVariants): array
    {
        $productclasses = [];
        $id = 0;
        try {
            $csv = $this->csv($csvArticles);

            foreach ($csv as $daten) {

                $category = trim($this->removeBreaks(utf8_encode(trim($daten['Kategorie-DE ']))));
                $categoryid = $this->searchSubCategoryCache($category);

                $exists = false;
                if (count($productclasses) > 0) {
                    foreach ($productclasses as $productclass) {
                        if ($productclass['title'] === $this->removeBreaks(utf8_encode(trim($daten['Titel-DE ']))) && $productclass['swSubCategory_id'] === $categoryid) {
                            $exists = true;
                            break;
                        }
                    }
                }
            }
        }catch (\Exception $exception) {
            dd($exception);
        }

        return true;
    }

    private function convertProductclass($csvArticles, $csvVariants): array
    {
        $productclasses = [];
        $id = 0;
        try {
            $csv = $this->csv($csvArticles);

            foreach ($csv as $daten) {

                $category = trim($this->removeBreaks(utf8_encode(trim($daten['Kategorie-DE ']))));
                $categoryid = $this->searchSubCategoryCache($category);

                $exists = false;
                if (count($productclasses) > 0) {
                    foreach ($productclasses as $productclass) {
                        if ($productclass['title'] === $this->removeBreaks(utf8_encode(trim($daten['Titel-DE ']))) && $productclass['swSubCategory_id'] === $categoryid) {
                            $exists = true;
                            break;
                        }
                    }
                }

                if (!$exists) {
                    $id++;
                    $rubrik = $this->removeBreaks(utf8_encode(trim($daten['Rubrik '])));
                    $headers = $this->convertProductHeader($rubrik, $csvVariants);

                    $productclasses[] = [
                        'id' => $id,
                        'image' => $this->removeBreaks(utf8_encode(trim($daten['Bild ']))),
                        'swSubCategory_id' => $categoryid,
                        'title' => $this->removeBreaks(utf8_encode(trim($daten['Titel-DE ']))),
                        'title_en' => $this->removeBreaks(utf8_encode(trim($daten['Title-EN ']))),
                        'description' => utf8_encode(trim($daten['Text-DE '])),
                        'description_en' => utf8_encode(trim($daten['Text-EN '])),
                        'sw_id' => $this->swHelper->generateUUID(32),
                        'sw_edited' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                        'headers' => $headers,
                    ];
                }
            }
        } catch (\Exception $exception) {
            dd($exception);
        }

        return $productclasses;
    }


    private function convertProductHeader($rubrik, $csvVariants)
    {
        foreach ($csvVariants as $csvVariant) {
            $filename = pathinfo($csvVariant, PATHINFO_FILENAME);
            $transformedName = $this->extractAndTransformFilename($filename);

            if ($transformedName == $rubrik) {

                $file = $this->getFile($csvVariant);
                $csv = $this->csv($file);

                $csvHeader = $this->getcsvheader($csv);

                $datasheetheader = [];
                for ($i = 0; $i < count($csvHeader) - 4; $i++) {
                    $datasheetheader[] = utf8_encode(trim($csvHeader[$i]));
                }

                return $datasheetheader;
            }
        }

        return [];
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

    private function getVariantsFiles(): bool|array
    {
        return glob(storage_path("$this->csvVariantsPath/*.csv"));
    }
    private function getcsvheader($csv): array
    {
        $csv = Reader::createFromString($csv);
        $csv->setDelimiter(';');
        $csv->setHeaderOffset(0);

        $header = $csv->getHeader();
        unset($csv);

        return $header;
    }
    private function searchSubCategoryCache($title): int
    {
        $subcategory = array_flip($this->subcategory);
        return $subcategory[$title] ?? 0;
    }

    private function createSubCategoryCache(): true
    {
        $this->subcategory = SWSubCategory::all()->pluck('title', 'id')->toArray();
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

    private function removeBreaks($string): string
    {
        return trim(str_replace(["\r", "\n"], '', $string));
    }
}
