<?php

namespace apiOrganigrama\Helpers;

use Config;
use Exception;
use stdClass;
use GuzzleHttp\Client;
use apiOrganigrama\Models\ConfigAreas;
use apiOrganigrama\Models\AreasUser;


class Organigrama
{

  public static $areas = [];

  /** return API url */
  public static  function getApiUrl()
  {
    return Config::get('apiOrganigrama.apiUrl');
  }

  /** return a client HTTP */
  public static function getHttpClient(){
    return new Client(['verify' => false,'http_errors' => false]);
  }

  /** return root of tree */
  public static function getRoot()
  {
    try {
      $url =  self::getApiUrl().'get-raiz?include=nivel_padre';
      $response = self::getHttpClient()->get( $url , [
        'headers'=> [
          'Accept' => 'application/json',
          'Authorization' => session('token')
        ]
      ]);

      if($response->getStatusCode() != 200){
        $response =  json_decode((string) $response->getBody());
        throw new Exception(  $response->error  );
      }

      $response = json_decode((string) $response->getBody());
      return $response->data[0]->nivel_padre;
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }
  }

  /** return types of leaves */
  public static  function getLevelsTypes(String $id = null)
  {
    try {
      $url =  self::getApiUrl().'tipos-niveles';
      if ($id) { $url = $url . '?filter[id]='. urlencode($id); }

      $response = self::getHttpClient()->get( $url , [
        'headers'=> [
          'Accept' => 'application/json',
          'Authorization' => session('token')
        ]
      ]);

      if($response->getStatusCode() != 200){
        $response =  json_decode((string) $response->getBody());
        throw new Exception(  $response->error  );
      }

      $response = json_decode((string) $response->getBody());
      return $response->data;
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }

  }

  /**
   * retorna los nodos hijos de un padre del organigrama de SOFSE
   * @param Int $parentId - required (id del padre)
   * @param Int $deep - required (produndidad de los hijos)
   */
  public static  function getLeavesByParent($parentId, $deep)
  {
    try {
      $url =  self::getApiUrl().'niveles-inferiores?';
      $params = [
        'include'=>'nivel_hijo',
        'filter[id_nivel_padre]'=>$parentId,
        'filter[profundidad]'=>$deep,
      ];
      $params = http_build_query($params);

      $response = self::getHttpClient()->get( $url . $params , [
        'headers'=> [
          'Accept' => 'application/json',
          'Authorization' => session('token')
        ]
      ]);
      if($response->getStatusCode() != 200){
        $response =  json_decode((string) $response->getBody());
        dd($response);
        throw new Exception(  $response->getBody() ); //$response->error  );
      }

      $response = json_decode((string) $response->getBody());
      // return $response->data;

      $data = [];
      foreach ($response->data as $nodo) {
        $data[] = [
          'id' =>  $nodo->nivel_hijo->id,
          'nombre' =>  $nodo->nivel_hijo->nombre,
          'descripcion' =>  $nodo->nivel_hijo->descripcion,
          'tipo_id' =>  $nodo->nivel_hijo->tipo_id,
          'parent_id' =>  $nodo->id_nivel_padre,
        ];
      }
      return $data;
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }

  }

  /** return all relations of the tree */
  public static function getRelacionesNiveles()
  {
    try {
      $url =  self::getApiUrl().'relaciones-niveles?include=nivel_hijo&limit=-1';
      $response = self::getHttpClient()->get( $url , [
        'headers'=> [
          'Accept' => 'application/json',
          'Authorization' => session('token')
        ]
      ]);

      if($response->getStatusCode() != 200){
        $response =  json_decode((string) $response->getBody());
        throw new Exception(  $response->error  );
      }

      $response = json_decode((string) $response->getBody());
      return $response->data;
    }
    catch(Exception $e) {
      return response()->json(['error'=>$e->getMessage(), 'file'=>$e->getFile(), 'line'=>$e->getLine()], 500);
    }
  }


  /** return user's areas */
  public static function getAreasUser(int $idUser, int $idTipoAreas, array $idPadre=null){
    $areasUserConfig = AreasUser::find($idUser);
    if(! $areasUserConfig) return null;
    $areasUser = json_decode($areasUserConfig->areas);
    $collection = collect(self::getTreeNodes($areasUser, [] ,$idTipoAreas, $idPadre));
    $unique = $collection->unique('id');
    return $unique;
  }


  /** funcion recursiva que retorna los nodos del usuario de determinados tipos o padres  */
  public static function getTreeNodes($elem=null, $result=[], $idTipoArea, $idPadre=null){
    if(is_array($elem)) {
      foreach ($elem as $key => $e) {
        self::getTreeNodes($e, $result, $idTipoArea, $idPadre);
      }
    }
    else {
      if(property_exists($elem, 'selected')){
        if($elem->selected && $elem->tipo_id == $idTipoArea  && (!$idPadre || in_array($elem->parent_id, $idPadre) ) ){
          $obj = new stdClass();
          $obj->id = $elem->id;
          $obj->nombre = $elem->nombre;
          $obj->descripcion = $elem->descripcion;
          $obj->tipo_id = $elem->tipo_id;
          $obj->parent_id = $elem->parent_id? $elem->parent_id: 0;
          self::$areas[] = $obj;
        }
      }

      if(property_exists($elem, 'children')){
        self::getTreeNodes($elem->children, $result, $idTipoArea, $idPadre);
      }
    }
    return self::$areas;
  }

}
