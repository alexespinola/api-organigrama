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


  public function getRoles(Request $request)
  {
    $roles = DB::table('roles')->select('id', 'name')->get();
    return $roles;
  }


  public function getUserAreasRoles(Request $request){
    $userAreasRoles = DB::table('users_areas_roles')->where('id_user', $request->id_user)->get();
    return $userAreasRoles;
  }


  public function setUserAreasRoles($id , Request $request)
  {
    try {
      $rolesXArea = json_decode($request->rolesXArea);

      $users_areas_roles=[];
      foreach ($rolesXArea as $area) {
        foreach ($area->roles as $role) {
          $users_areas_roles[]= ['id_user'=>$id, 'id_rol'=>$role, 'id_area'=>$area->id,  'id_parent'=>$area->parent_id];
        }
      }


      //persiste en DB
      DB::table('users_areas_roles')->where('id_user', $id)->delete();
      DB::table('users_areas_roles')->insert($users_areas_roles);
      return response()->json(['status'=>200, 'message'=>'Roles por áreas guardados'], 200);
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }
  }


}
