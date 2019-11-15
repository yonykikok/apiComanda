<?php

namespace Helpers;

class AppConfig
{
    public static $illuminateDb = [
        'driver' => 'mysql',
        'host' => 'sql184.main-hosting.eu',
        'database' => 'u698144487_coman',
        'username' => 'u698144487_root',
        'password' => '40132526',
        'charset' => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix' => ''
    ];

    public static $tables = [

        'users' => "users"
    ];

    public static $imagesDirectories = [
        'users' => 'public/img/users',
        'empleados' => 'public/img/empleados',
        'empleadosBkp' => 'public/img/empleadosBkp'
    ];

    public static $watermark = 'public/img/watermark.png';

    public static $imageConstraints = [
        'size' => '500000', //0,5mb
        'types' => [
            'image/jpeg', 'image/jpeg', 'image/png'
        ],
        'extensions' => [
            '.jpg', '.jpeg', '.png', '.JPG', '.JPEG', '.PNG'
        ]
    ];

}
