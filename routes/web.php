<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EstadosController;

Route::get('/', [EstadosController::class, 'index'])->name('estados.index');
Route::get('/estados/{estado:cve_ent}/municipios', [EstadosController::class, 'municipios'])->name('estados.municipios');