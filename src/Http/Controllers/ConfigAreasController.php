<?php

namespace apiOrganigrama\Http\Controllers;

use Illuminate\Http\Request;
use apiOrganigrama\Helpers\Organigrama;
use apiOrganigrama\Models\ConfigAreas;

class ConfigAreasController extends Controller
{

  public function index()
  {
    $storedConfig = ConfigAreas::find(1);
    if($storedConfig){
      $storedConfig = $storedConfig->areas;
    }else{
      $storedConfig = '[]';
    }

    return view('apiOrganigrama::configAreas.index')->with(['storedConfig'=>$storedConfig]);
  }

  public function store(Request $request)
  {
    try {
      $model = ConfigAreas::find(1);
      if (!$model) $model = New ConfigAreas;
      $model->id = 1;
      $model->areas = $request->tree;
      $model->save();
      return response()->json(['status'=>200, 'message'=>'configurarión guardada'], 200);
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }
  }


  public function getRoot(Request $request){
    return Organigrama::getRoot();
  }


  public function getLevelsTypes(Request $request){
    return Organigrama::getLevelsTypes();
  }


  public function getRelacionesNiveles(Request $request){
    return Organigrama::getRelacionesNiveles();
  }

}
