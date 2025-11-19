<?php

namespace App\Http\Controllers\Sync;

use App\Http\Controllers\Controller;
use App\Models\SWPicture;
use App\Models\SWProductClass;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Storage;

class ImageCopyController extends Controller
{
    private int $batchSize = 5000; // Anzahl der Datensätze pro Batch
    private array $productPictures;

    public function copy()
    {
        $basePath = '';
        $localStoragePath = 'public/pictures/'; // Der Pfad im Storage, wo die Bilder gespeichert werden sollen

        $productClasses = SWProductClass::with(['subCategory.category', 'products.variantValues', 'variantHeaders'])
            ->get();

        $client = new Client();

        $productClasses->each(function (SWProductClass $productClass) use ($basePath, $localStoragePath, $client) {
            $pictures = $productClass->pictures()->get();

            foreach ($pictures as $picture) {
                $fileName = $picture->file;
                $fileUrl = $basePath . $fileName;

                try {
                    // HTTP GET Request, um die Datei herunterzuladen
                    $response = $client->get($fileUrl);
                    $fileContents = $response->getBody()->getContents();

                    // Speichern der Datei im lokalen Storage
                    //Storage::put($localFilePath, $fileContents);
                } catch (\Exception $e) {
                    // Fehlerbehandlung, falls die Datei nicht heruntergeladen werden kann
                    dd('Error downloading file: ' . $fileUrl . ' - ' . $e->getMessage());
                }
            }

            $products = $productClass->products;

            foreach ($products as $product) {

                $filename = 'product'.$product->id.'.png';
                $localFilePath = $localStoragePath . $filename;
                Storage::put($localFilePath, $fileContents);

                $this->productPictures[] = [
                    'type' => 0,
                    'assignment_id' => $product->id,
                    'path' => $basePath,
                    'file' => $filename,
                    'pos' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

            }
        });

        //dd($this->productPictures);

        SWPicture::where('type', 0)->delete();

        foreach (array_chunk($this->productPictures, $this->batchSize) as $batch) {
            //sleep(1);
            SWPicture::insert($batch);
        }
    }

}
