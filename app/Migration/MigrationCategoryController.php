<?php

namespace App\Migration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWSubCategory;
use League\Csv\Reader;

class MigrationCategoryController extends Controller
{
    private string $csvArticlespath = 'migration/export_print_podukte.csv';
    private int $batchSize = 5000; // Anzahl der Datensätze pro Batch
    private ShopWareHelperController $swHelper;

    public function __construct()
    {
        $this->swHelper = new ShopWareHelperController();
    }

    public function migrate()
    {
        $csvArticles = $this->getFile(storage_path($this->csvArticlespath));

        //Categorys
        $categorys = $this->convertCategorys($csvArticles);

        SWSubCategory::truncate();
        foreach (array_chunk($categorys, $this->batchSize) as $batch) {
            sleep(1);
            SWSubCategory::insert($batch);
        }
        unset($categorys);

        return redirect('shopware/sync/')->with([
            'success' => "Kategorie wurde erfolgreich migriert!"
        ]);
    }

    private function convertCategorys($csvArticles): array
    {
        $categorys = [];
        try {
            $csv = $this->csv($csvArticles);

            foreach ($csv as $daten) {
                $kategorieEN = $this->removeBreaks(utf8_encode(trim($daten['Kategorie-EN '])));
                $kategorieDE = $this->removeBreaks(utf8_encode(trim($daten['Kategorie-DE '])));

                // Überprüfen, ob 'Kategorie-EN' bereits vorhanden ist
                $exists = false;
                foreach ($categorys as $category) {
                    if ($category['title'] === $kategorieDE) {
                        $exists = true;
                        break;
                    }
                }

                // Wenn 'Kategorie-EN' noch nicht vorhanden ist, hinzufügen
                if (!$exists) {
                    $categorys[] = [
                        'swCategory_id' => 1,
                        'title' => $kategorieDE,
                        'title_en' => $kategorieEN,
                        'meta_title' => $kategorieDE,
                        'sw_id' => $this->swHelper->generateUUID(32),
                        'sw_edited' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        } catch (\Exception $exception) {
            dd($exception);
        }

        return $categorys;
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
        return str_replace(["\r", "\n"], '', $string);
    }
}
