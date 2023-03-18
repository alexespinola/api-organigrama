<?php

use Illuminate\Support\Facades\Route;
use apiOrganigrama\Http\Controllers\ConfigAreasController;
use apiOrganigrama\Http\Controllers\AreasUserController;

Route::group(['middleware'=>['web','auth']],function(){
  Route::resource('config-areas', ConfigAreasController::class);
  Route::get('api-organigrama-get-root', [ConfigAreasController::class, 'getRoot']);
  Route::get('api-organigrama-get-levels-types', [ConfigAreasController::class, 'getLevelsTypes']);
  Route::get('api-organigrama-get-relaciones-niveles', [ConfigAreasController::class, 'getRelacionesNiveles']);

  Route::resource('areas-user', AreasUserController::class);
  Route::get('areas-user-get-roles', [AreasUserController::class, 'getRoles']);
  Route::get('areas-user-get-premissions-by-areas', [AreasUserController::class, 'getPermissionsByAreas']);
});
