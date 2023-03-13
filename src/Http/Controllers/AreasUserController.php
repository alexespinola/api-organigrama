<?php

namespace apiOrganigrama\Http\Controllers;

use Illuminate\Http\Request;
use apiOrganigrama\Helpers\Organigrama;
use apiOrganigrama\Models\ConfigAreas;
use apiOrganigrama\Models\AreasUser;
use DB;

class AreasUserController extends Controller
{


  public function edit($id , Request $request)
  {
    $user = DB::table('users')->where('id', $id)->first();
    $areasUser = AreasUser::where('id_user', $id)->first();
    $storedConfig = ConfigAreas::find(1);

    return view('apiOrganigrama::areasUser.edit')->with([
      'storedConfig'=>$storedConfig ?  $storedConfig->areas : '[]',
      'areasUser'=>$areasUser ? $areasUser->areas : '[]',
      'user'=>$user
    ]);
  }


  public function update($id , Request $request)
  {
    try {
      $model = AreasUser::where('id_user', $id)->first();
      if (!$model) $model = New AreasUser;
      $model->id_user = $id;
      $model->areas = $request->tree;
      $model->save();
      return response()->json(['status'=>200, 'message'=>'Áreas guardadas'], 200);
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }
  }


}
