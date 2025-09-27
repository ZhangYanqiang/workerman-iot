<?php

ini_set('display_errors', 'on');

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    exit("start_mqtt.php not support windows, please use start_for_win.bat\n");
}

// 检查扩展
if(!extension_loaded('pcntl'))
{
    exit("Please install pcntl extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

if(!extension_loaded('posix'))
{
    exit("Please install posix extension. See http://doc3.workerman.net/appendices/install-extension.html\n");
}

// 标记是全局启动
define('GLOBAL_START', 1);

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/orm.php';

use Workerman\Worker;


// 加载所有Applications/*/start_mqtt.php，以便启动所有服务
foreach(glob(__DIR__ . '/app/runmqtt/start_*.php') as $start_file)
{
    require_once $start_file;
}

 Worker::runAll();
