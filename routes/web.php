<?php

use Illuminate\Support\Facades\Route;
use apiOrganigrama\Http\Controllers\ConfigAreasController;

Route::group(['middleware'=>['web']],function(){
  Route::resource('config-areas', ConfigAreasController::class);
  Route::get('api-organigrama-get-levels-types', [ConfigAreasController::class, 'getLevelsTypes']);
  Route::get('api-organigrama-get-leaves-by-parent', [ConfigAreasController::class, 'getLeavesByParent']);
});
