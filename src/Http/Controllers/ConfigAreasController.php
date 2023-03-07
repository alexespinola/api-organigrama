<?php

namespace apiOrganigrama\Http\Controllers;

use Illuminate\Http\Request;
use apiOrganigrama\Organigrama;

class ConfigAreasController extends Controller
{

  public function index()
  {
    return view('apiOrganigrama::configAreas.index');
  }


  public function getLevelsTypes(Request $request){
    return Organigrama::getLevelsTypes();
  }


  public function getLeavesByParent(Request $request){
    return Organigrama::getLeavesByParent($request->parentId, $request->deep);
  }


}
