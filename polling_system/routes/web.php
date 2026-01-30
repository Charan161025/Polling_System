
<?php
use App\Http\Controllers\MainController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MainController::class,'loginView']);
Route::post('/login', [MainController::class,'login']);

Route::middleware('auth')->group(function () {
    Route::get('/polls',[MainController::class,'polls']);
    Route::get('/poll/{id}',[MainController::class,'pollView']);
    Route::post('/vote',[MainController::class,'vote']);
    Route::get('/results/{id}',[MainController::class,'results']);
    Route::get('/admin',[MainController::class,'admin']);
    Route::post('/release',[MainController::class,'release']);
});
