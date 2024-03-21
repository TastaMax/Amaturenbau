<?php
namespace App\Http\Controllers\Management\FAQ;

use App\Http\Controllers\Controller;

class FAQController extends Controller
{

    public function index()
    {
        return view('pages/faq/index', []);
    }
}
