<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3f7c1a2f9422e6ce9ac041660faed568
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Shift4\\WooCommerce\\' => 19,
            'Shift4\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Shift4\\WooCommerce\\' => 
        array (
            0 => __DIR__ . '/../..' . '/',
        ),
        'Shift4\\' => 
        array (
            0 => __DIR__ . '/..' . '/shift4/shift4-php/lib/Shift4',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3f7c1a2f9422e6ce9ac041660faed568::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3f7c1a2f9422e6ce9ac041660faed568::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit3f7c1a2f9422e6ce9ac041660faed568::$classMap;

        }, null, ClassLoader::class);
    }
}
