<?php
namespace App\Http\Controllers\ShopWare;

use App\Http\Controllers\Controller;
use App\Models\SWCategory;
use App\Models\SWSubCategory;
use DateTime;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index()
    {
        return view('pages/shopware/subcategory/index', []);
    }

    public function create()
    {
        return view('pages/shopware/subcategory/create', []);
    }

    public function edit(Request $request)
    {
        $subcategory = $request->subcategory;
        $subcategory = SWSubCategory::find($subcategory);
        $category = $subcategory->category;

        if (!$subcategory) {
            return redirect('/shopware/kategorie/')->with([
                'error' => "Unterkategorie wurde nicht gefunden!"
            ]);
        }

        return view('pages/shopware/subcategory/edit', [
            'subcategory' => $subcategory,
            'category' => $category,
            'selectCategory' => SWCategory::all()
        ]);
    }

    public function subEditCategory(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'title_de' => 'required',
            'category' => 'required',
        ]);

        $subcategory = $request->id;
        $swSubCategory = SWSubCategory::find($subcategory);

        if (!$swSubCategory) {
            return redirect('/shopware/kategorie/')->with([
                'error' => "Unterkategorie wurde nicht gefunden!"
            ]);
        }

        $swCategory_id = $request->input('category');

        $swSubCategory = $swSubCategory->update([
            'swCategory_id' => $swCategory_id,
            'title' => $request->input('title_de'),
            'title_en' => $request->input('title_en'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'meta_keywords' => $request->input('meta_keywords'),
            'sw_edited' => true,
            'sw_deleted' => false,
        ]);

        if($swSubCategory)
        {
            return redirect('/shopware/unterkategorie/editieren/'.$subcategory)->with([
                'success' => "Unterkategorie wurde erfolgreich bearbeitet!"
            ]);
        }else{
            return redirect('/shopware/unterkategorie/editieren/'.$subcategory)->with([
                'error' => "Unterkategorie konnte nicht überarbeitet werden!"
            ]);
        }
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

    public function getSubCategory(Request $request)
    {


        $subCategorys = SWSubCategory::all();

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
