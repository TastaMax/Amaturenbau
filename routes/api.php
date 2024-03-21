<?php

use App\Http\Controllers\BC\GNT\GNTSyncController;
use App\Http\Controllers\BC\Migration\SkriptExport;
use App\Http\Controllers\Office365\OfficeCalendarController;
use App\Http\Controllers\Office365\OfficeUserAccountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

/*
 * Hinweis für Passwort Schutz
 * Unter app - Http - Kernel.php wird die middleware bestimmt names CustomeApiAuthentication.
 * Im Header muss ApiKey angegeben werden.
 * In der .env Datei gibt es die Value API_KEY welche den Zugang einstellt.
 */

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
