<?php
namespace App\Http\Controllers\Management\Downloads;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DownloadsController extends Controller
{
    protected $path = '';
    public function getDownload(Request $request)
    {
        $file = $request->file;
        $download = $this->path.$file;

        if (Storage::disk('public')->exists($download)) {
            return Storage::disk('public')->download($file);
        } else {
            abort(404, 'Die Datei wurde nicht gefunden.');
        }
    }
}
