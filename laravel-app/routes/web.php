<?php

use App\Http\Controllers\StoryController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/story', [StoryController::class, 'index'])->name('story.index');
Route::get('/story/characters', [StoryController::class, 'characters'])->name('story.characters');
Route::post('/story/generate', [StoryController::class, 'generate'])->name('story.generate');
