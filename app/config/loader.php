<?php

$loader = new \Phalcon\Loader();

/**
 * Register the autoloader and tell it to register the tasks directory
 */
$loader = new \Phalcon\Loader();
$loader->registerDirs(
   [
       $config->application->tasksDir,// register tasks dir
       $config->application->modelsDir// register models dir
   ]
);
$loader->register();