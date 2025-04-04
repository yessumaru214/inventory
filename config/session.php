<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Session Driver
    |--------------------------------------------------------------------------
    |
    | Controla el controlador de sesiones por defecto que utilizará Laravel. 
    | El controlador predeterminado es "file", pero se puede cambiar a otros 
    | controladores compatibles como "cookie", "database", "apc", "memcached",
    | "redis" o "array".
    |
    */

    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Session Lifetime
    |--------------------------------------------------------------------------
    |
    | Define el tiempo de vida de las sesiones en minutos. Una sesión expirará
    | si permanece inactiva durante este tiempo. Si deseas que la sesión
    | expire al cerrar el navegador, configura 'expire_on_close' en true.
    |
    */

    'lifetime' => env('SESSION_LIFETIME', 120),

    'expire_on_close' => false,

    /*
    |--------------------------------------------------------------------------
    | Session Encryption
    |--------------------------------------------------------------------------
    |
    | Permite especificar si los datos de la sesión deben ser cifrados antes 
    | de almacenarse. Laravel manejará automáticamente el cifrado.
    |
    */

    'encrypt' => false,

    /*
    |--------------------------------------------------------------------------
    | Session File Location
    |--------------------------------------------------------------------------
    |
    | Cuando se utiliza el controlador "file", este define la ubicación donde 
    | se almacenan los archivos de sesión. Es necesario solo para este controlador.
    |
    */

    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Session Database Connection
    |--------------------------------------------------------------------------
    |
    | Especifica la conexión de base de datos que debe usarse para el controlador
    | "database" o "redis". Debe coincidir con una conexión configurada en la
    | opción de configuración de base de datos.
    |
    */

    'connection' => null,

    /*
    |--------------------------------------------------------------------------
    | Session Database Table
    |--------------------------------------------------------------------------
    |
    | Define la tabla que se utilizará para manejar las sesiones cuando se usa
    | el controlador "database". Puedes cambiar el valor predeterminado.
    |
    */

    'table' => 'sessions',

    /*
    |--------------------------------------------------------------------------
    | Session Cache Store
    |--------------------------------------------------------------------------
    |
    | Para los controladores "apc" o "memcached", especifica el almacén de caché
    | que se debe usar. Debe coincidir con una tienda configurada en caché.
    |
    */

    'store' => null,

    /*
    |--------------------------------------------------------------------------
    | Session Sweeping Lottery
    |--------------------------------------------------------------------------
    |
    | Define las probabilidades de que Laravel elimine sesiones antiguas en
    | cada solicitud. Por defecto, la probabilidad es de 2 en 100.
    |
    */

    'lottery' => [2, 100],

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Name
    |--------------------------------------------------------------------------
    |
    | Define el nombre de la cookie utilizada para identificar una sesión. El
    | nombre especificado se usará cada vez que Laravel cree una nueva cookie.
    |
    */

    'cookie' => env(
        'SESSION_COOKIE',
        str_slug(env('APP_NAME', 'laravel'), '_').'_session'
    ),

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Path
    |--------------------------------------------------------------------------
    |
    | Especifica el path donde la cookie de sesión estará disponible. Por lo 
    | general, será la ruta base de la aplicación.
    |
    */

    'path' => '/',

    /*
    |--------------------------------------------------------------------------
    | Session Cookie Domain
    |--------------------------------------------------------------------------
    |
    | Permite cambiar el dominio de la cookie de sesión. Define los dominios
    | donde estará disponible.
    |
    */

    'domain' => env('SESSION_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Only Cookies
    |--------------------------------------------------------------------------
    |
    | Si se establece en true, las cookies de sesión solo se enviarán si la
    | conexión del navegador es HTTPS.
    |
    */

    'secure' => env('SESSION_SECURE_COOKIE', false),

    /*
    |--------------------------------------------------------------------------
    | HTTP Access Only
    |--------------------------------------------------------------------------
    |
    | Previene que las cookies de sesión sean accedidas mediante JavaScript.
    | Las cookies solo estarán disponibles a través del protocolo HTTP.
    |
    */

    'http_only' => true,

    /*
    |--------------------------------------------------------------------------
    | Same-Site Cookies
    |--------------------------------------------------------------------------
    |
    | Controla el comportamiento de las cookies durante solicitudes cross-site
    | para mitigar ataques CSRF. Valores admitidos: "lax" o "strict".
    |
    */

    'same_site' => null,

];
