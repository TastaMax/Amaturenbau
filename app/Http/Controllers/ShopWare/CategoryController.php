<?php
namespace App\Http\Controllers\ShopWare;

use App\Http\Controllers\Controller;
use App\Models\SWCategory;
use App\Models\SWSubCategory;
use DateTime;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        return view('pages/shopware/category/index', []);
    }

    public function create()
    {
        return view('pages/shopware/category/create', []);
    }

    public function edit(Request $request)
    {
        $category = $request->category;
        $category = SWCategory::find($category);

        if (!$category) {
            return redirect('/shopware/kategorie/')->with([
                'error' => "Kategorie wurde nicht gefunden!"
            ]);
        }

        return view('pages/shopware/category/edit', [
            'category' => $category
        ]);
    }

    public function delete(Request $request)
    {
        $category = $request->category;
        $category = SWCategory::find($category);

        if (!$category) {
            return redirect('/shopware/kategorie/')->with([
                'error' => "Kategorie wurde nicht gefunden!"
            ]);
        }

        $swCategory = $category->update([
            'sw_edited' => false,
            'sw_deleted' => true,
        ]);

        if($swCategory)
        {
            return redirect('/shopware/kategorie/')->with([
                'success' => "Kategorie wurde markiert für die Löschung!"
            ]);
        }else{
            return redirect('/shopware/kategorie/editieren/'.$category)->with([
                'error' => "Kategorie konnte nicht gelöscht werden!"
            ]);
        }
    }

    public function editCategory(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'title_de' => 'required',
        ]);

        $category = $request->id;
        $swCategory = SWCategory::find($category);

        if (!$swCategory) {
            return redirect('/shopware/kategorie/')->with([
                'error' => "Kategorie wurde nicht gefunden!"
            ]);
        }

        $swCategory = $swCategory->update([
            'title' => $request->input('title_de'),
            'title_en' => $request->input('title_en'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'sw_edited' => true,
            'sw_deleted' => false,
        ]);

        if($swCategory)
        {
            return redirect('/shopware/kategorie/editieren/'.$category)->with([
                'success' => "Kategorie wurde erfolgreich bearbeitet!"
            ]);
        }else{
            return redirect('/shopware/kategorie/editieren/'.$category)->with([
                'error' => "Kategorie konnte nicht überarbeitet werden!"
            ]);
        }
    }

    public function saveCategory(Request $request)
    {
        $request->validate([
            'title_de' => 'required',
        ]);

        $swHelper = new ShopWareHelperController();

        $swCategory = SWCategory::create([
            'title' => $request->input('title_de'),
            'title_en' => $request->input('title_en'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'sw_edited' => true,
            'sw_id' => $swHelper->generateUUID(32),
        ]);
        $swCategory->save();

        return redirect('/shopware/kategorie/editieren/'.$swCategory->id)->with([
            'success' => "Kategorie wurde erfolgreich erstellt!"
        ]);
    }

    public function getCategory()
    {
        $categorys = SWCategory::all();
        $formattedCategorys = [];

        foreach ($categorys as $category)
        {
            $status = $category->sw_deleted ? '<i class="fa-solid fa-trash"></i>' : ($category->sw_edited ? '<i class="fa-solid fa-clock"></i>' : '<i class="fa-solid fa-check"></i>');
            $created_at = new DateTime($category['created_at']);
            $formattedCategorys[] = [
                'id' => $category['id'],
                'title' => $category['title'],
                'status' => $status,
                'created_at' => $created_at->format('Y-m-d H:i:s')
            ];
        }

        return response()->json($formattedCategorys);
    }

    public function getSubCategoryData(Request $request)
    {
        $idCategory = $request->id;

        if(is_null($idCategory))
        {
            return response()->json();
        }

        $subCategorys = SWSubCategory::where('swCategory_id', $idCategory)->get();

        if(is_null($subCategorys))
        {
            return response()->json();
        }

        $formattedSubCategorys = [];

        foreach ($subCategorys as $subCategory)
        {
            $status = $subCategory->sw_deleted ? '<i class="fa-solid fa-trash"></i>' : ($subCategory->sw_edited ? '<i class="fa-solid fa-clock"></i>' : '<i class="fa-solid fa-check"></i>');
            $created_at = new DateTime($subCategory['created_at']);
            $formattedSubCategorys[] = [
                'id' => $subCategory['id'],
                'title' => $subCategory['title'],
                'status' => $status,
                'created_at' => $created_at->format('Y-m-d H:i:s')
            ];
        }

        return response()->json($formattedSubCategorys);
    }
}
