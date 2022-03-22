<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit57f8a6ca6ced386ea31e9c68688a9cdd
{
    public static $files = array (
        '88045391164ed958acdb5384ce7b6c89' => __DIR__ . '/../..' . '/src/helpers.php',
    );

    public static $prefixLengthsPsr4 = array (
        'T' => 
        array (
            'Text\\' => 5,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Text\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit57f8a6ca6ced386ea31e9c68688a9cdd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit57f8a6ca6ced386ea31e9c68688a9cdd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit57f8a6ca6ced386ea31e9c68688a9cdd::$classMap;

        }, null, ClassLoader::class);
    }
}
