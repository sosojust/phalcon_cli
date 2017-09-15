<?php
/*
 * Modified: prepend directory path of current file, because of this file own different ENV under between Apache and command line.
 */
defined('BASE_PATH') || define('BASE_PATH', getenv('BASE_PATH') ?: realpath(dirname(__FILE__) . '/../..'));
defined('APP_PATH') || define('APP_PATH', BASE_PATH . '/app');

return new \Phalcon\Config([
    'database' => [
        'adapter'     => 'Mysql',
        'host'        => 'localhost',
        'username'    => 'root',
        'password'    => '',
        'dbname'      => 'db0907',
        'charset'     => 'utf8',
    ],
    'application' => [
        'appDir'    => APP_PATH . '/',
        'tasksDir'  => APP_PATH . '/tasks/',
        'modelsDir' => APP_PATH . '/models/'
    ]
]);
