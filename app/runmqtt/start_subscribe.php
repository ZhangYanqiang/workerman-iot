<?php

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    require_once  __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../app/helpers.php';
    require_once __DIR__ . '/../../app/orm.php';
}


use Workerman\Worker;
use app\models\Devices;

$worker = new Worker();
$worker->count = 1;
$worker->name = 'subscriber';
$worker->onWorkerStart = function () {
    global $mqtt;
    $mqtt = new Workerman\Mqtt\Client(config('app.mqtt'),[
        'client_id'=>config('app.product_key').mt_rand(100,999),
        'username'=>config('app.mqtt_cfg.username'),
        'password'=>config('app.mqtt_cfg.password'),
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
    $mqtt->onMessage = function ($topic, $content) {
        global $mqtt;
        $message = $content;
        print_r($topic);
        echo PHP_EOL;
        print_r($message);
        echo PHP_EOL;
        $topic_arr = explode('/',$topic);
        if($topic_arr[array_key_last($topic_arr)] == 'connected'){//设备上线
            #$SYS/brokers/emqx@127.0.0.1/clients/12345678/connected

            //处理设备上线事件
            //更新设备状态
            //记录设备日志
            //...

            //设备上线信息
            $device = Devices::getDeviceBySN($topic_arr[4]);
            if(empty($device)){
                $device = new Devices();
                $device->sn = $topic_arr[4];
            }
            $device->status = 1;//设备在线
            $device->save();
            deviceLog($topic_arr[4],'接收',$message); //记录日志


        # $SYS/brokers/emqx@127.0.0.1/clients/12345678/disconnected
        }else if($topic_arr[array_key_last($topic_arr)] == 'disconnected'){ //设备离线
            //处理设备离线事件
            //更新设备状态
            //记录设备日志
            $device = Devices::getDeviceBySN($topic_arr[4]);
            $device->status = 0;//设备离线
            $device->save();
            deviceLog($topic_arr[4],'接收',$message); //记录日志
        }else{ //设备上报信息
            //设备上报的具体信息
            //设备重启结果
            deviceLog($topic_arr[2],'接收',$message); //记录日志
            $data = json_decode($message);
            print_r($data);
            $return_msg = [];
            switch ($data->cmd){
                case 'LOGIN': //设备登录
                    //更新设备信息
                    $device = Devices::getDeviceBySN($data->sn);
                    $device->imei = $data->imei;
                    $device->iccid = $data->iccid;
                    $device->version = $data->version;
                    $device->save();
                    $return_msg[] = [
                        "cmd"=>"LOGIN"
                    ];
                    break;
                case "RESTART": //设备重启结果
                    break;
                default :
                    break;
            }

            if($return_msg){ //回复设备
                $sn = $data->sn;
                foreach ($return_msg as $msg){
                    deviceLog($data->sn,'发送',$msg);
                    $mqtt->publish("/".config('app.product_key')."/$data->sn/user/get",json_encode($msg),[],function($sn,$msg){
                        deviceLog($sn,'发送失败',$msg);
                    });
                }
            }
        }
    };
    $mqtt->connect();
};


Worker::runAll();