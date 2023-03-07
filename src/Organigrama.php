<?php

namespace apiOrganigrama;

use Config;
use Exception;
use GuzzleHttp\Client;

class Organigrama
{

  /** return API url */
  public static  function getApiUrl()
  {
    return Config::get('apiOrganigrama.apiUrl');
  }

  /** return a client HTTP */
  public static function getHttpClient(){
    return new Client(['verify' => false,'http_errors' => false]);
  }

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
        throw new Exception(  $response->error  );
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

}
