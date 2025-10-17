<?php
return [
    'curl' => [
        'base_uri'=>'http://xx.xxxxx.com',
        'timeout'=>5,
    ],
    'http' => 'http://127.0.0.1:9103', //监听http请求 的端口
    'product_key' => 'iot', // 产品key，和mqtt主题有关
    'mqtt' => 'mqtt://xx.xxxxxxx.com:1883', //mqtt 服务端地址
    'mqtt_cfg'=>[
        'username'=>' ',
        'password'=>' '
    ],
    'tcp' => [
        'server'=>'tcp://0.0.0.0:8282',
    ],
] ;