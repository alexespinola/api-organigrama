## api-organigrama 

<p align="center">
  <img src="https://img.shields.io/static/v1?label=php&message=8.1&color=greem">
  
  <img src="https://img.shields.io/static/v1?label=Laravel&message=9.x&color=greem">  
    
  <a href="https://packagist.org/packages/alexespinola/login-cuentas">
    <img src="https://img.shields.io/static/v1?label=Stable&message=v1.0.0&color=blue" alt="Latest Stable Version">
  </a>

  <a href="https://packagist.org/packages/alexespinola/login-cuentas">
    <img src="https://poser.pugx.org/laravel/framework/license.svg" alt="License">
  </a>
</p>


<b>api-organigrama</b> es una librería que le permite integrar las áreas del organigama de SOFSE a su sistema, mediante la API que provee dicho organigrama.

Esta librería provee dos interfaces graficas: una  para configurar las áreas que su sistema usará y otra para asignar áreas a los usuarios de su sitema.
Ademas provee un helper que le ayudará a obtener datos de la API de organigramas SOFSE.


### Requerimientos
- PHP: ^8.0
- laravel: ^9
- composer 
- En la DB de su aplicación debe existir la tabla <b>users</b> con el campo `id`.

### Instalación

`composer require alexespinola/api-organigrama`

### Configuración

- En el archivo  `.env` de su aplicaión defina la URL de la API organigrama:

  - `URL_API_ORGANIGRAMA=http://organigrama.sofse.gob.ar/public/api/v1/`

- Ejecute el comando: `php artisan migrate`



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
  - un `@stack('page_scripts')` donde incrustar el js.


### Instruciones para usar el helper Organigama

Para obtener datos de la API organigrama pude usar el helper `src/Helpers/Organigrama` importandolo en sus controladores o donde usted lo necesite.

```php
<?php

namespace App\Http\Controller;

use apiOrganigrama\Helpers\Organigrama;

class MyController
{

}
```

