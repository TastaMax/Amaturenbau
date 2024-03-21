<?php

use App\Http\Controllers\Dashboard;
use App\Http\Controllers\Management\Authentication\AuthenticationController;
use App\Http\Controllers\Management\FAQ\FAQController;
use App\Http\Controllers\Management\Logs\LogsController;
use App\Http\Controllers\Management\Logs\LogsStorageController;
use App\Http\Controllers\Management\Logs\RisksController;
use App\Http\Controllers\Management\Logs\SchedulesController;
use App\Http\Controllers\Management\Settings\SettingsController;
use App\Http\Controllers\ShopWare\CategoryController;
use App\Http\Controllers\ShopWare\ProductclassController;
use App\Http\Controllers\Startup\StartupController;
use App\Http\Controllers\Sync\SyncController;
use App\Migration\MigrationCategoryController;
use App\Migration\MigrationProductclassController;
use App\Migration\MigrationProductController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/


/*
 * Startup
 */
Route::get('/startup', [StartupController::class, 'index']);
Route::post('/startup', [StartupController::class, 'store'])->name('startup');

/*
 * Login
 */
Route::get('/login', [AuthenticationController::class, 'index']);
Route::post('/login', [AuthenticationController::class, 'login'])->name('login');


Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
    /*
     * Dashboard
     */
    Route::get('/', [Dashboard::class, 'index']);

    Route::get('/faq', [FAQController::class, 'index']);

    Route::prefix('log')->group(function () {
        Route::get('/getLastEntries', [LogsController::class, 'getLastLogs']);
        Route::get('{logId}', [LogsController::class, 'getLogDetails']);
        Route::prefix('storage')->group(function () {
            Route::get('{logs}', [LogsStorageController::class, 'getStorageLogEntries']);
            Route::get('/clear/{logs}', [LogsStorageController::class, 'clearStorageLogs'])->name('clear.logs');
        });

    });

    Route::get('/get-risks', [RisksController::class, 'getRisks']);
    Route::get('/get-schedules', [SchedulesController::class, 'getScheduleEntries']);


    Route::prefix('notifications')->group(function () {
        Route::get('/', [LogsController::class, 'index']);
        Route::prefix('json')->group(function () {
            Route::get('/getData/', [LogsController::class, 'getData'])->name('notifications');
            Route::get('/getData/{system}', [LogsController::class, 'getData'])->name('notifications');
        });
    });

    Route::prefix('einstellungen')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('settings');
        Route::post('/mail', [SettingsController::class, 'mail'])->name('mail');
        Route::post('/shopware', [SettingsController::class, 'shopware'])->name('shopware');
    });

    Route::prefix('shopware')->group(function () {

        Route::prefix('kategorie')->group(function () {
            Route::get('/', [CategoryController::class, 'index']);
            Route::get('/editieren/{category}', [CategoryController::class, 'edit']);
            Route::get('/erstellen', [CategoryController::class, 'create']);
            Route::prefix('json')->group(function () {
                Route::get('/getCategory', [CategoryController::class, 'getCategory']);
                Route::get('/getSubCategory/{id}', [CategoryController::class, 'getSubCategoryData']);
            });

            Route::get('/delete/{category}', [CategoryController::class, 'delete']);
            Route::post('/swCategoryCreate', [CategoryController::class, 'saveCategory'])->name('swCategoryCreate');
            Route::post('/swCategoryEdit', [CategoryController::class, 'editCategory'])->name('swCategoryEdit');
        });


        Route::prefix('produktklasse')->group(function () {
            Route::get('/', [ProductclassController::class, 'index']);
            Route::get('/editieren/{productclass}', [ProductclassController::class, 'edit']);
            Route::get('/erstellen', [ProductclassController::class, 'create']);
            Route::prefix('json')->group(function () {
                Route::get('/getProductclass', [ProductclassController::class, 'getCategory']);
            });

            Route::get('/delete/{productclass}', [ProductclassController::class, 'delete']);
            Route::post('/swProductclassCreate', [ProductclassController::class, 'saveCategory'])->name('swProductclassCreate');
            Route::post('/swProductclassEdit', [ProductclassController::class, 'editCategory'])->name('swProductclassEdit');
        });

        Route::prefix('sync')->group(function () {
            Route::get('/', [SyncController::class, 'index']);
            Route::get('/category', [SyncController::class, 'category']);
            Route::get('/subcategory', [SyncController::class, 'subcategory']);
            Route::get('/product', [SyncController::class, 'product']);
            Route::get('/migrate-category', [MigrationCategoryController::class, 'migrate']);
            Route::get('/migrate-productclass', [MigrationProductclassController::class, 'migrate']);
            Route::get('/migrate-product', [MigrationProductController::class, 'migrate']);
        });

    });
});

