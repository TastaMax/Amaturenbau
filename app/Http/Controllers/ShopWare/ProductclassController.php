<?php
namespace App\Http\Controllers\ShopWare;

use App\Http\Controllers\Controller;
use App\Models\SWProductClass;
use DateTime;
use Illuminate\Http\Request;

class ProductclassController extends Controller
{
    public function index()
    {
        return view('pages/shopware/productclass/index', []);
    }

    public function create()
    {
        return view('pages/shopware/productclass/create', []);
    }

    public function edit(Request $request)
    {
        $productclass = $request->productclass;
        $productclass = SWProductClass::find($productclass);
        $pictures = $productclass->pictures()->get();
        $hasPictures = $pictures->isNotEmpty();

        if (!$productclass) {
            return redirect('/shopware/produktklasse/')->with([
                'error' => "Produktklasse wurde nicht gefunden!"
            ]);
        }

        return view('pages/shopware/productclass/edit', [
            'productclass' => $productclass,
            'pictures' => $pictures,
            'hasPictures' => $hasPictures
        ]);
    }

    public function createProductclass()
    {
        $swHelper = new ShopWareHelperController();
        $uuid = $swHelper->generateUUID(32);
    }

    public function getCategory()
    {
        $productclasses = SWProductClass::all();
        $formattedProductclass = [];

        foreach ($productclasses as $productclass)
        {
            $status = $productclass->sw_deleted ? '<i class="fa-solid fa-trash"></i>' : ($productclass->sw_edited ? '<i class="fa-solid fa-clock"></i>' : '<i class="fa-solid fa-check"></i>');
            $created_at = new DateTime($productclass['created_at']);
            $formattedProductclass[] = [
                'id' => $productclass['id'],
                'title' => $productclass['title'],
                'rubrik' => $productclass['rubrik'],
                'status' => $status,
                'created_at' => $created_at->format('Y-m-d H:i:s')
            ];
        }

        return response()->json($formattedProductclass);
    }
}
