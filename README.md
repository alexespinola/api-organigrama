## api-organigrama 

<p align="center">
  <img src="https://img.shields.io/static/v1?label=php&message=8.1&color=greem">
  
  <img src="https://img.shields.io/static/v1?label=Laravel&message=9.x&color=greem">  

  <img src="https://img.shields.io/static/v1?label=Vue.js&message=3.x&color=greem">  

  <img src="https://img.shields.io/static/v1?label=JQuery&message=3.x&color=greem"> 
    
  <a href="https://packagist.org/packages/alexespinola/login-cuentas">
    <img src="https://img.shields.io/static/v1?label=Stable&message=v1.0.0&color=blue" alt="Latest Stable Version">
  </a>

  <a href="https://packagist.org/packages/alexespinola/login-cuentas">
    <img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License">
  </a>
</p>


<b>api-organigrama</b> es una librería que le permite integrar las áreas del organigama de SOFSE a su sistema, mediante la API que provee dicho organigrama. La finalidad es que usted pueda definir a que áreas los usuarios pertenecen o tinen acceso, para controlar las opciones, comportamientos o interfaces que pude ver cada usuario al navegar por su sistema.  



### Requerimientos
- `PHP: ^8.0`
- `laravel: ^9`
- `composer`
- En la DB de su aplicación debe existir la tabla <b>users</b> con los campos `id` y `name`.



### Dependencias
- JQuery ( $ )
- Lodash ( _ )
- Vue.js V3  (el archivo debe poder importarse asi: `<script src="{{asset('js/vue.js')}}"></script>`).



### Instalación
- En el archivo  `.composer.json` de su aplicaión defina la clave `repositories` como se muestra abajo:

```json
"repositories": [
  {
    "type": "vcs",
    "url": "https://gitlab.sofse.gob.ar/alejandro.espinola/api-organigrama.git"
  }
]
```

- En su terminal ejecute el siguiente comando: 
  - `composer require alexespinola/api-organigrama`



### Configuración
- En el archivo  `.env` de su aplicaión defina la URL de la API organigrama:
  - `URL_API_ORGANIGRAMA=http://organigrama.sofse.gob.ar/public/api/v1/`

- Ejecute el comando: `php artisan migrate`



### Vistas
Esta librería provee dos interfaces graficas: 
Una  para configurar las áreas que su sistema usará y otra para asignar áreas a los usuarios de su sitema.
Usted desida como los usuarios navegan a estas interfaces.
Las rutas a estas interfaces son:
- /config-areas
- /areas-user/{user_id}/edit



### Publicar archivo de configuración
Si lo desea, puede publicar la config de esta librería con el siguiente comando:

`php artisan vendor:publish --provider="apiOrganigrama\ApiOrganigramaServiceProvider" --tag="config"`

Esto crea un archivo de configuración en su aplicación: `config/apiOrganigrama.php`



### Publicar vistas
Si desea modificar las vistas que provee este paquete debe puplicarlas con el siguiente comando: 

`php artisan vendor:publish --provider="apiOrganigrama\ApiOrganigramaServiceProvider" --tag="views"`

Esto crea una carpeta con todas las vistas en su aplicación en `resources/views/vendor/apiOrganigrama`.

Las vistas que provee esta librería requieren  que su aplicación tenga una vista base `resources/views/layouts/app.blade.php` de donde extender.
Esa template debe incluir ademas los siguientes elementos:
  - un tag `<meta name="csrf-token" content="{{ csrf_token() }}" />` en la sección head del HTML.
  - un `@yield('content')` donde incrustar el HTML.
  - un `@stack('page_scripts')` donde incrustar el JavaScript.



### El helper Organigama
Este helper le ayudará a obtener:
- Las áreas del organigrama de SOFSE mediante peticiones HTTP a la API que corresponda.
- Las áreas que su sistema usa según la configuración de areas.
- Las areas asigandas a cada usuario.


### Instruciones para usar el helper Organigama

Para usar el helper `src/Helpers/Organigrama` puede importarlo en sus controladores o donde usted lo necesite.

```php
//Example Contreoller

<?php

namespace App\Http\Controller;

use apiOrganigrama\Helpers\Organigrama;

class ExampleController
{

}
```

### DOCUMENTACIÓN de los métodos del helper Organigama

```php

/**
 * Retorna el nodo raíz del arbol del organigrama de SOFSE
 */
Organigrama::getRoot();

// Respuesta de ejemplo
{
  "id": 2,
  "nombre": "presidencia",
  "descripcion": "",
  "tipo_id": 1,
}

/**
 * Retorna todos los nodos del organigrama de SOFSE y sus relaciones
 */
Organigrama::getRelacionesNiveles();

// Respuesta de ejemplo
[
  {
    "id": 1,
    "profundidad": 0,
    "id_nivel_padre": 2,
    "id_nivel_hijo": 2,
    "nivel_hijo": {
      "id": 2,
      "nombre": "presidencia",
      "descripcion": "",
      "tipo_id": 1,
    }
  },
  {
    "id": 2,
    "profundidad": 1,
    "id_nivel_padre": 2,
    "id_nivel_hijo": 3,
    "nivel_hijo": {
      "id": 3,
      "nombre": "gerencia general de asuntos jurídicos",
      "descripcion": "",
      "tipo_id": 2,
  },
  ...
]

/**
 * Retorna los tipos de los nodos del organigrama de SOFSE
 * @param String $tiposId - opcional
 */
Organigrama::getLevelsTypes( $tiposId='1,4' );

// Respuesta de ejemplo
[
  {
    "id": 1,
    "nombre": "presidencia",
  },
  {
    "id": 4,
    "nombre": "gerencia de línea",
  }
]


/**
 * Retorna los nodos hijos de un padre del organigrama de SOFSE
 * @param Int $parentId - required (id del padre)
 * @param Int $deep - required (produndidad de los hijos)
 */
Organigrama::getLeavesByParent( $parentId=3, $deep=1 );

// Respuesta de ejemplo
[
  {
    "id": 13,
    "nombre": "gerencia dictámenes y asistencia jurídica",
    "descripcion": "",
    "tipo_id": 3,
    "parent_id": 3
  },
  {
    "id": 14,
    "nombre": "gerencia asuntos contenciosos",
    "descripcion": "",
    "tipo_id": 3,
    "parent_id": 3
  },
  {
    "id": 15,
    "nombre": "gerencia siniestros y servicios a las líneas",
    "descripcion": "",
    "tipo_id": 3,
    "parent_id": 3
  }
]


/**
 * Retorna los nodos a los que pertense un usuario segun $idTipoAreas 
 * Si $idPadres se pasa como tercer parametro se retornan solo los nodos de tipo $idTipoAreas e hijos de $idPadres
 * @param Int $userId - required (id del usuario)
 * @param Int $idTipoAreas - required (id de tipo de nodos)
 * @param Array $idPadres - opcional (ids de los padres)
 */
Organigrama::getAreasUser( $userId=463, $idTipoAreas=6 , $idPadres=[21,501] );

// Respuesta de ejemplo
[
  {
    "id": 42,
    "nombre": "obras civiles",
    "descripcion": "",
    "tipo_id": 6,
    "parent_id": 501
  },
  {
    "id": 43,
    "nombre": "vías",
    "descripcion": "",
    "tipo_id": 6,
    "parent_id": 21
  },
  ...
]


```


