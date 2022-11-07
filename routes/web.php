<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
})->middleware('auth')->name('home');

Route::group(['prefix' => 'auth'], function () {

    Route::get('/login', function () {
        if (!auth()->check()) {
            return view('auth.login');
        } else {
            return redirect(\route('home'));
        }
    })->name('login.page');

    Route::post('/login', function (\Illuminate\Http\Request $request) {

        $user = \App\Models\User::where('email', $request->get('login'))->first();
        if (!$user) {
            return response('Пользователь не найден!', 403);
        }

        if (!\Illuminate\Support\Facades\Hash::check($request->get('password'), $user->password)) {
            return response('Не угадал с паролем!', 403);
        }

        \Illuminate\Support\Facades\Auth::login($user);

        return true;

    })->name('login');

    Route::get('/logout', function () {
        \Illuminate\Support\Facades\Auth::logout();
        return redirect(\route('login'));
    })->name('logout');

//    Route::post('/login-api', [Controllers\Auth\AuthController::class, 'LoginApi'])->name('login-api');
//
//    Route::post('/registration', [Controllers\Auth\AuthController::class, 'Registration'])->name('registration');//
//
//    Route::post('/phone-validation', [Controllers\Auth\AuthController::class, 'PhoneValidation'])->name('phone-validation');
//    Route::post('/check-confirmation-code', [Controllers\Auth\AuthController::class, 'CheckConfirmationCode'])->name('check-confirmation-code');
//
//    Route::post('/change-password', [Controllers\Auth\AuthController::class, 'ChangePasswordRequest'])->name('change-password');
//    Route::post('/check-password', [Controllers\Auth\AuthController::class, 'CheckPasswordRequest'])->name('check-password');
//
//

});

Route::group(['prefix' => 'layout'], function () {

    Route::post('/all', function (\Illuminate\Http\Request $request) {
        return \App\Models\Layouts::query()
            ->with('points')
            ->get();
    })->name('layout.all');

    Route::post('/add', function (\Illuminate\Http\Request $request) {
        return \App\Models\Layouts::create([
            'user_id' => auth()->user()->id,
            'title' => $request->get('title'),
            'data' => json_encode((object)[
                'color' => $request->get('color')
            ])
        ]);
    })->name('layout.add');

//    Route::post('/update', function (\Illuminate\Http\Request $request) {
//        return \App\Models\Points::where([
//            'id' => $request->get('pointId')
//        ])->update([
//            'latitude' => $request->get('latitude'),
//            'longitude' => $request->get('longitude'),
//        ]);
//    })->name('point.update');
//
    Route::post('/delete', function (\Illuminate\Http\Request $request) {
        return \App\Models\Layouts::where([
            'id' => $request->get('layoutId')
        ])->delete();
    })->name('layout.delete');

});

Route::group(['prefix' => 'point'], function () {

    Route::post('/all', function (\Illuminate\Http\Request $request) {
        return \App\Models\Points::all();
    })->name('point.all');

    Route::post('/add', function (\Illuminate\Http\Request $request) {
        return \App\Models\Points::create([
            'layout_id' => $request->get('layoutId'),
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
        ]);
    })->name('point.add');

    Route::post('/update', function (\Illuminate\Http\Request $request) {
        return \App\Models\Points::where([
            'id' => $request->get('pointId')
        ])->update([
            'latitude' => $request->get('latitude'),
            'longitude' => $request->get('longitude'),
        ]);
    })->name('point.update');

    Route::post('/delete', function (\Illuminate\Http\Request $request) {
        return \App\Models\Points::where([
            'id' => $request->get('pointId')
        ])->delete();
    })->name('point.delete');

});

Route::group(['prefix' => 'lines'], function () {

    Route::post('/all', function (\Illuminate\Http\Request $request) {
        return \App\Models\Lines::query()
//            ->with('fromPoint')
//            ->with('toPoint')
            ->get();
    })->name('lines.all');

    Route::post('/add', function (\Illuminate\Http\Request $request) {
        return \App\Models\Lines::create([
            'startPointId' => $request->get('startPointId'),
            'endPointId' => $request->get('endPointId'),
        ]);
    })->name('lines.add');

    Route::post('/delete', function (\Illuminate\Http\Request $request) {
        return \App\Models\Lines::where([
            'id' => $request->get('lineId')
        ])->delete();
    })->name('lines.delete');

});
