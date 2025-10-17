<?php
/**
 * This file is part of workerman.
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the MIT-LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author walkor<walkor@workerman.net>
 * @copyright walkor<walkor@workerman.net>
 * @link http://www.workerman.net/
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * 用于检测业务代码死循环或者长时间阻塞等问题
 * 如果发现业务卡死，可以将下面declare打开（去掉//注释），并执行php start.php reload
 * 然后观察一段时间workerman.log看是否有process_timeout异常
 */
//declare(ticks=1);

if(strpos(strtolower(PHP_OS), 'win') === 0)
{
    require_once  __DIR__ . '/../../vendor/autoload.php';
    require_once __DIR__ . '/../../app/helpers.php';
    require_once __DIR__ . '/../../app/orm.php';
}


use \GatewayWorker\Lib\Gateway;

/**
 * 主逻辑
 * 主要是处理 onConnect onMessage onClose 三个方法
 * onConnect 和 onClose 如果不需要可以不用实现并删除
 */
class Events
{
    /**
     * 当客户端连接时触发
     * 如果业务不需此回调可以删除onConnect
     * 
     * @param int $client_id 连接id
     */
    public static function onConnect($client_id)
    {

    }
    
   /**
    * 当客户端发来消息时触发
    * @param int $client_id 连接id
    * @param mixed $message 具体消息
    */
   public static function onMessage($client_id, $message)
   {
       $uid = Gateway::getUidByClientId($client_id);
       print_r($message);
       $message = json_decode($message,true);
       $cmd = $message['cmd'] ?? '';
       if(empty($cmd)){
           Gateway::closeClient($client_id);
       }
       $sn = $message['sn'];
       deviceLog($message['sn'],'接收',$message); //记录日志
       $return_msg = [];
       switch ($cmd){
           case "LOGIN": //设备登录服务器
               //绑定设备ID和Client_id;
               Gateway::bindUid($client_id,$sn);
               $_SESSION['uid'] = $sn;
               $device = \app\models\Devices::getDeviceBySN($sn);
               if(empty($device)){
                    $device = new \app\models\Devices();
                    $device->sn = $sn;
               }
               $device->status = 1;
               $device->imei = $message['imei'];
               $device->iccid = $message['iccid'];
               $device->version = $message['version'];
               $device->save();
               $return_msg[] = [
                   "cmd"=>"LOGIN"
               ];
               break;
           case "RESTART": //重启设备结果
               //此处可记录日志等
               deviceLog($message['sn'],'接收','设备重启结果'.$message['code']); //记录日志
               break;
           default:
               break;
       }

        if($return_msg){
            foreach ($return_msg as $item) {
                Gateway::sendToUid($uid,json_encode($item));
            }
        }
   }
   
   /**
    * 当用户断开连接时触发
    * @param int $client_id 连接id
    */
   public static function onClose($client_id)
   {
       $uid = $_SESSION['uid'];
       if($uid){
           $device = \app\models\Devices::getDeviceByUid($uid);
           if($device){
               if(!Gateway::isUidOnline($uid)){
                   deviceLog($uid,'设备离线','');
                   $device->status = 0;
                   $device->save();
               }
           }
       }
   }
}
