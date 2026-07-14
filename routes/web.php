<?php

use Illuminate\Support\Facades\Route;

use App\Models\Personal;

Route::get('/', function () {
    return view('menu');
});

Route::get('/test-db', function () {
    $empleados = Personal::has('laboral')->with('laboral')->take(5)->get();
    return view('test', compact('empleados'));
});

