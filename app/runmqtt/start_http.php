<?php

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    require_once  __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../app/helpers.php';
    require_once __DIR__ . '/../../app/orm.php';
}


use Workerman\Worker;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;

// 创建一个Worker监听2345端口，使用http协议通讯
$http_worker = new Worker(config('app.http'));
// 启动4个进程对外提供服务
$http_worker->count = 1;
$http_worker->name = 'IOTHttp';

$http_worker->onWorkerStart = function($worker)
{
    global $mqtt;
    $mqtt = new Workerman\Mqtt\Client(config('app.mqtt'));
    $mqtt->onConnect = function($mqtt) {
    };
    $mqtt->connect();
};


$http_worker->onMessage = function(TcpConnection $connection, Request $request)
{
    global $mqtt;
    $indata = $request->post();
    $sn = $indata['sn'];
    if(!isset($indata['sn']) || empty($indata['sn'])){
        $connection->send(false);
    }

    $cmd = $indata['cmd'];
    print_r($indata);
    echo PHP_EOL;
    $send_cmd_msg = [];
    switch ($cmd){
        case 'RESTART': //重启设备
            //处理具体逻辑
            //比如记录日志
            //给设备发送重启指令等
            $send_cmd_msg = [
                "cmd"=>'RESTART'
            ]; //根据具体的设备协议获取
            break;
        default:
            break;
    }
    if(empty($send_cmd_msg)){ //无需给设备发信息
        $connection->send(true); //回复http请求
    }else{ //直接给设备发送指令
        //记录日志
        deviceLog($sn,'http发送',$send_cmd_msg);
        //发送指令
        $mqtt->publish("/".config('app.product_key')."/$sn/user/get",json_encode($send_cmd_msg),[],function($sn,$send_cmd_msg,$connection){
            deviceLog($sn,'http发送失败',$send_cmd_msg);
            $connection->send(false);
        });
        $connection->send(true);
    }
};

// 如果不是在根目录启动，则运行runAll方法
if(!defined('GLOBAL_START'))
{
    Worker::runAll();
}