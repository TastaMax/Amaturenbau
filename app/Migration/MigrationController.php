<?php
namespace App\Migration;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ShopWare\ShopWareHelperController;
use App\Models\SWPicture;
use App\Models\SWProduct;
use App\Models\SWProductClass;
use App\Models\SWSubCategory;
use App\Models\SWVariantHeader;
use App\Models\SWVariantValue;
use League\Csv\Reader;

class MigrationController extends Controller
{
    private string $csvPath = 'migration/export_print_podukte.csv';
    private string $csvVariantsPath = 'migration/variants';
    private int $batchSize = 5000; // Anzahl der Datensätze pro Batch
    private ShopWareHelperController $swHelper;
    private $variantValues = [];

    public function __construct()
    {
        $this->swHelper = new ShopWareHelperController();
    }

    public function index()
    {
        $categorys = [];
        SWSubCategory::truncate();

        $productclasses = [];
        SWProductClass::truncate();

        $pictures = [];
        SWPicture::truncate();

        $products = [];
        SWProduct::truncate();

        $variantHeaders = [];
        SWVariantHeader::truncate();

        $variantValues = [];
        SWVariantValue::truncate();

        $csv = $this->csv($this->getFile(storage_path($this->csvPath)));
        foreach ($csv as $daten) {
            $kategorieEN = $this->removeBreaks($daten['Kategorie-EN ']);
            $kategorieDE = $this->removeBreaks($daten['Kategorie-DE ']);
            $zeichnung = $this->removeBreaks($daten['Zeichnung ']);
            $bild = $this->removeBreaks($daten['Bild ']);

            $textDE = $this->removeBreaks($daten['Text-DE ']);
            $textEN = $this->removeBreaks($daten['Text-EN ']);
            $titelDE = $this->removeBreaks($daten['Titel-DE ']);
            $titelEN = $this->removeBreaks($daten['Title-EN ']);

            $id = $this->removeBreaks($daten['ID ']);
            $rubrik = $this->removeBreaks($daten['Rubrik ']);

            //Kategorie
            $existingTitles = array_column($categorys, 'title');
            if (!in_array($kategorieDE, $existingTitles)) {
                $idCategory = count($categorys)+1;
                $categorys[] = [
                    'id' => $idCategory,
                    'swCategory_id' => 1,
                    'title' => $kategorieDE,
                    'title_en' => $kategorieEN,
                    'meta_title' => $kategorieDE,
                    'sw_id' => $this->swHelper->generateUUID(32),
                    'sw_edited' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }else {
                // Finde die Indexposition des vorhandenen Titels
                $index = array_search($kategorieDE, $existingTitles);
                $idCategory = $index+1;
            }

            //Produktklasse
            $productclasses[] = [
                'id' => $id,
                'swSubCategory_id' => $idCategory,

                'title' => $titelDE,
                'title_en' => $titelEN,
                'description' => $textDE,
                'description_en' => $textEN,

                'datasheet' => $zeichnung,

                'sw_id' => $this->swHelper->generateUUID(32),
                'sw_edited' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $pictures[] = [
                'id' => $id,
                'type' => 1,
                'assignment_id' => $id,
                'path' => '',
                'file' => $bild,
                'pos' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];

            $idrubrik = str_replace('.', '-', $rubrik).'__';
            $csvVariants = $this->csv($this->getFile(storage_path($this->csvVariantsPath.'/'.$idrubrik.$id.'.csv')));

            $variantHeaderCSV = $this->getvariantHeader($csvVariants);
            $tmpid = 1;
            foreach ($variantHeaderCSV as $datasheetHeader) {
                $titles = $this->splitGermanEnglishIdentifier($datasheetHeader);
                if(!is_array($titles))
                {
                    dd($titles, $datasheetHeader);
                }
                $variantHeaders[] = [
                    'id' => count($variantHeaders)+1,
                    'swProductClass_id' => $id,
                    'title' => $titles['German'],
                    'title_en' => $titles['English'],
                    'pos' => $tmpid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $tmpid++;
            }

            $isArticles = false;
            foreach ($csvVariants as $key => $values)
            {
                $this->getArticleValues($values, $this->removeBreaks($values['ID']));
                $cleanedString = str_replace(',', '.', preg_replace('/[^0-9,]/', '', utf8_encode(trim($values['Brutto preis']))));
                $decimalValue = floatval($cleanedString);
                $products[] = [
                    'id' => $this->removeBreaks($values['ID']),
                    'swProductClass_id' => $id,
                    'articlenumber' => $this->removeBreaks($values['Art.-Nr.']),
                    'serie' => $this->removeBreaks($values['Serie']),
                    'price' => $decimalValue,
                    'sw_id' => $this->swHelper->generateUUID(32),
                    'sw_edited' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        foreach (array_chunk($categorys, $this->batchSize) as $batch) {
            //sleep(1);
            SWSubCategory::insert($batch);
        }

        foreach (array_chunk($productclasses, $this->batchSize) as $batch) {
            //sleep(1);
            SWProductClass::insert($batch);
        }

        foreach (array_chunk($pictures, $this->batchSize) as $batch) {
            //sleep(1);
            SWPicture::insert($batch);
        }

        foreach (array_chunk($products, $this->batchSize) as $batch) {
            //sleep(1);
            SWProduct::insert($batch);
        }

        foreach (array_chunk($variantHeaders, $this->batchSize) as $batch) {
            //sleep(1);
            SWVariantHeader::insert($batch);
        }

        foreach (array_chunk($this->variantValues, $this->batchSize) as $batch) {
            //sleep(1);
            SWVariantValue::insert($batch);
        }

        return redirect('shopware/sync/')->with([
            'success' => "Migration von Excel zu Datenbank erfolgreich!"
        ]);
    }

    private function getArticleValues($values, $productid)
    {
        $pos = 1;
        foreach ($values as $key => $variantValue) {
            if ($key === 'Art.-Nr.' || $key === 'Brutto preis' || $key === 'Serie' || $key === 'ID') {
                break;
            }
            $this->variantValues[] = [
                'id' => count($this->variantValues)+1,
                'swProduct_id' => $productid,
                'value' => $this->removeBreaks($variantValue),
                'value_en' => $this->removeBreaks($variantValue),
                'pos' => $pos,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            $pos += 1;
        }

        return;
    }

    private function getvariantHeader($csv)
    {
        $csvHeader = $this->getcsvheader($csv);
        $datasheetheader = [];
        for ($i = 0; $i < count($csvHeader) - 4; $i++) {
            $datasheetheader[] = utf8_encode(trim($csvHeader[$i]));
        }
        return $datasheetheader;
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
        return utf8_encode(trim(str_replace(["\r", "\n"], '', $string)));
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

    function splitGermanEnglishIdentifier($input) {
            // Teile den Eingabestring anhand von zwei Leerzeichen auf
            $parts = explode("  ", $input);

            // Stelle sicher, dass es genau drei Teile gibt
            if (count($parts) !== 3) {
                if(count($parts) == 1)
                {
                    $germanPart = trim($input);
                    $englishPart = trim($input);
                    $identifier = '';
                    return array("German" => $germanPart, "English" => $englishPart, "Identifier" => $identifier);
                }
                // Extrahiere die Teile entsprechend
                $germanPart = trim($parts[0]);
                $englishPart = trim($parts[1]);
                $identifier = '';
                return array("German" => $germanPart, "English" => $englishPart, "Identifier" => $identifier);
            }

            // Extrahiere die Teile entsprechend
            $germanPart = trim($parts[0]);
            $englishPart = trim($parts[1]);
            $identifier = trim($parts[2]);

            return array("German" => $germanPart, "English" => $englishPart, "Identifier" => $identifier);
        }
}
