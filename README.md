# phalcon_cli
a phalcon cli application framework


# 目录结构

使用 phalcon 创建的应用的最小目录结构如下：
```
app/config/config.php   //配置文件
app/config/loader.php   //自动加载文件
app/config/services.php //服务注册文件
app/tasks/MainTask.php  //至少包含的一个主任务
app/models/             //model层目录
app/cli.php             //启动文件
```


## cli.php

主启动文件

```
<?php
use Phalcon\DI\FactoryDefault\CLI as CliDI;
use Phalcon\CLI\Console as ConsoleApp;
define('VERSION', '0.0.1');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

//Using the CLI factory default services container
$di = new CliDI();

//Create a console application
$console = new ConsoleApp();
$console->setDI($di);

/**
 * Read services
 */
include APP_PATH . '/config/services.php';

/**
 * Get config service for use in inline setup below
 */
$config = $di->getConfig();


/**
 * Include Auto loader
 */
include APP_PATH . '/config/loader.php';


/**
 * Process the console arguments
 */
$arguments = array();
$params = array();


foreach($argv as $k => $arg) {
    if($k == 1) {
        $arguments['task'] = $arg;
    } elseif($k == 2) {
        $arguments['action'] = $arg;
    } elseif($k >= 3) {
        $params[] = $arg;
    }
}
if(count($params) > 0) {
    $arguments['params'] = $params;
}


// define global constants for the current task and action
define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));


try {
    // handle incoming arguments
    $console->handle($arguments);
}
catch (\Phalcon\Exception $e) {
    echo $e->getMessage();
    exit(255);
}
```

## app/config/config.php
定义数据库和APP相关的配置信息

```
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

```
## app/config/loader.php

应用的自动加载文件内容

```
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
```

## app/config/services.php

服务注册文件

```
<?php

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . "/config/config.php";
});


/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig();

    $class = 'Phalcon\Db\Adapter\Pdo\\' . $config->database->adapter;
    $params = [
        'host'     => $config->database->host,
        'username' => $config->database->username,
        'password' => $config->database->password,
        'dbname'   => $config->database->dbname,
        'charset'  => $config->database->charset
    ];

    if ($config->database->adapter == 'Postgresql') {
        unset($params['charset']);
    }

    $connection = new $class($params);

    return $connection;
});

```

## app/tasks/MainTask.php
至少要包含一个MainTask和 mainAction 作为默认行为
```
<?php

class mainTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        echo "\nThis is the default task and the default action \n";
    }
}
```

**至此，可以运行 cli 命令，命令行如下：**

```
php .\app\cli.php
```
显示结果如下

> This is the default task and the default action 


# 接收命令行参数的cli行为

示例如下，可以在 MainTask.php 添加一个测试方法：
```
    /**
     * @param array $params
     */
    public function testAction(array $params) {
        echo sprintf('hello %s', $params[0]) . PHP_EOL;
        echo sprintf('welcom to phalcon cli app, %s', $params[1]) . PHP_EOL;
    }
```

执行命令如下

```
php .\app\cli.php main test sosojust justsoso
```
显示结果如下(忽略换行问题)：
> hello sosojust
>
> best regards, justsoso


# 任务链

简单说，任务链就是在某一个task action中，可以连续调用其他的 task action
示例如下：

## 主持共享任务服务

1. 在 app/config/services.php 添加注册服务代码
```
/**
 * Shared task chain service
 */
$di->setShared('console', $console);
```

2. 创建一个子任务（示例，为了更好的演示任务链。并非必须步骤）
app/tasks/SubTask.php
```
<?php

class subTask extends \Phalcon\CLI\Task
{
    public function mainAction() {
        echo "\nThis is the sub task and the sub action \n";
    }
}
```

3. 在 MainTask 的main action 中使用任务链，代码如下：
```
public function mainAction() {
    echo "\nThis is the default task and the default action \n";
        $this->console->handle([
        'task' => 'main',
        'action' => 'test',
        'params' => ['sosojust', 'justsoso']
    ]);
        $this->console->handle([
        'task' => 'sub',
        'action' => 'main'
    ]);
}
```

4. 执行 cli 命令如下：

```
php .\app\cli.php
```
显示结果如下(忽略换行问题)：
> This is the default task and the default action
>
>hello sosojust
>
>best regards, justsoso
>
>This is the sub task and the sub action