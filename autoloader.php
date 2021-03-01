<?php

spl_autoload_register(function($class) {
    var_dump($class);
    $baseDir = __DIR__."/";
    $explode = explode("\\", $class);
    $directory = strtolower($explode[0]);
    $classFile = strtolower($explode[1]).".php";

    if (file_exists($classPath = $baseDir . $directory . DIRECTORY_SEPARATOR . $classFile)) {
        include $classPath;
    }
});