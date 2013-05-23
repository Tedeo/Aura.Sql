<?php
// required Aura packages
$packages = ['Sql', 'Sql_Query'];

// where required packages are installed
$basepath = dirname(dirname(__DIR__));

// load required package bootstraps
foreach ($packages as $package) {
    // filename if installed using original repository name
    $original = $basepath . DIRECTORY_SEPARATOR
              . "Aura.{$package}" . DIRECTORY_SEPARATOR
              . 'tests' . DIRECTORY_SEPARATOR
              . 'bootstrap.php';
    
    // filename if installed via composer
    $composer = $basepath . DIRECTORY_SEPARATOR
              . strtolower($package)
              . 'tests' . DIRECTORY_SEPARATOR
              . 'bootstrap.php';
    
    // look for the bootstrap files
    if (is_readable($original)) {
        // original installation
        require $original;
    } elseif (is_readable($composer)) {
        // composer installation
        require $composer;
    } else {
        // not available
        echo "Required package Aura.{$package} not available." . PHP_EOL;
        echo __FILE__ . ", line #" . __LINE__ . PHP_EOL;
        exit(1);
    }
}

// autoloader
require dirname(__DIR__) . '/autoload.php';

// default globals
if (is_readable(__DIR__ . '/globals.default.php')) {
    require __DIR__ . '/globals.default.php';
}

// override globals
if (is_readable(__DIR__ . '/globals.php')) {
    require __DIR__ . '/globals.php';
}
