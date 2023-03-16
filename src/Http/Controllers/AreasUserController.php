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


  public function getPermissionsByArea(Request $request){
     $user_id = $request->user_id;
     $user = User::find($user_id);
     Auth::login($user);

     //Guarda en session los permisos
     $res  = DB::table('users_ramales_roles')
             ->select('permissions.name', 'users_ramales_roles.ramal_id')
             ->join('roles', 'roles.id', '=', 'users_ramales_roles.role_id')
             ->join('role_has_permissions', 'role_has_permissions.role_id', '=', 'roles.id')
             ->join('permissions', 'permissions.id', '=', 'role_has_permissions.permission_id')
             ->where('users_ramales_roles.user_id',$user_id)
             ->get();

     $permisos = array();
     foreach ($res as $key => $r) {
       $permisos[$r->name][] = $r->ramal_id;
     }
     Session::put('permisos', $permisos);
  }

}
