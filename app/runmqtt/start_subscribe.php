<?php

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    require_once  __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../app/helpers.php';
    require_once __DIR__ . '/../../app/orm.php';
}


use Workerman\Worker;

$worker = new Worker();
$worker->count = 1;
$worker->name = 'subscriber';
$worker->onWorkerStart = function () {

    $mqtt = new Workerman\Mqtt\Client(config('app.mqtt'),[
        'client_id'=>config('app.product_key').mt_rand(100,999),
        'username'=>config('app.mqtt.username'),
        'password'=>config('app.mqtt.password'),
        'debug'=>true
    ]);
    $mqtt->onConnect = function ($mqtt) {
        //订阅所有设备报文信息
        $mqtt->subscribe("/".config('app.product_key')."/+/user/update"); //主题根据协议确定，此处为举例
        //订阅所有系统信息
        //设备离线
        $mqtt->subscribe('$SYS/brokers/+/clients/+/connected');
        //设备上线
        $mqtt->subscribe('$SYS/brokers/+/clients/+/disconnected');
    };
    $mqtt->onMessage = function ($topic, $content,$mqtt) {
        $message = $content;
        print_r($topic);
        echo PHP_EOL;
        print_r($message);
        echo PHP_EOL;
        $topic_arr = explode('/',$topic);
        if($topic_arr[array_key_last($topic_arr)] == 'connected'){//设备上线
            //处理设备上线事件
            //更新设备状态
            //记录设备日志
            //...
        }else if($topic_arr[array_key_last($topic_arr)] == 'disconnected'){ //设备离线
            //处理设备离线事件
            //更新设备状态
            //记录设备日志
            //...
        }else{ //设备上报信息
            //设备上报的具体信息
            //设备重启结果
            //...
        }


    };
    $mqtt->connect();
};


Worker::runAll();