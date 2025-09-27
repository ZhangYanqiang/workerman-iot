<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule = new Capsule();
// 创建链接（多库配置）
$capsule->addConnection(config('database.database'), 'default');
// 数据库查询事件
$capsule->setEventDispatcher(new Dispatcher(new Container()));

// 设置全局静态可访问
$capsule->setAsGlobal();

Capsule::connection('default')->listen(function ($query) {
    // 这里是执行 sql 后的监听回调方法
    $sql = vsprintf(str_replace("?", "'%s'", $query->sql), $query->bindings) . " [" . $query->time . ' ms] ';
    // 把 SQL 写入到日志文件中
//    Debug::log($sql, 'info');
});
// 启动 Eloquent
$capsule->bootEloquent();
