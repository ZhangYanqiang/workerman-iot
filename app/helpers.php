<?php
use app\Application;

if (! function_exists('config')) {
    function config($key,$default = null)
    {
        $app = new Application();
        $config = $app->config->get($key,$default);
        return $config;
    }
}

if(! function_exists('outLog')){
    function outLog($message)
    {
        try{
            if (is_array($message)) {
                $message = json_encode($message);
            } elseif (is_object($message)) {
                $message = print_r($message);
            }
            $message = str_replace("\r\n",'\r\n',$message);

            $time = time();
            $path = dirname(__DIR__).'/runtime/log/'.date('Ymd',$time);
            if (! is_dir($path)) {
                mkdir($path);
            }
            $filename = $path .'/system.log';
            $content = date("Y-m-d H:i:s").' | ' . $message . PHP_EOL. PHP_EOL;
            file_put_contents($filename, $content, FILE_APPEND);
        }catch (Exception $e){
            outLog($e->getMessage());
        }

    }
}


if(! function_exists('deviceLog')){
    function deviceLog($uid,$type,$message)
    {
        try{
            if (is_array($message)) {
                $message = json_encode($message);
            } elseif (is_object($message)) {
                $message = json_encode($message);
            }
            $message = str_replace("\r\n",'\r\n',$message);
            $time = time();
            $path = dirname(__DIR__).'/runtime/log/'.date('Ymd',$time);
            if (! is_dir($path)) {
                mkdir($path);
            }
            $filename = $path ."/$uid.log";
            $content = date("Y-m-d H:i:s").' | '. $uid .' | '. $type . " | " . $message . PHP_EOL;
            print_r($content);
            file_put_contents($filename, $content, FILE_APPEND);
        }catch (Exception $e){
            outLog($e->getMessage());
        }

    }
}


function crc16($data)
{
    $crc = 0xFFFF;
    for ($i = 0; $i < strlen($data); $i++)
    {
        $crc ^=ord($data[$i]);

        for ($j = 8; $j !=0; $j--)
        {
            if (($crc & 0x0001) !=0)
            {
                $crc >>= 1;
                $crc ^= 0xA001;
            }
            else
                $crc >>= 1;
        }
    }
    return $crc;
}
/**
 * 将一个字符按比特位进行反转 eg: 65 (01000001) --> 130(10000010)
 * @param $char
 * @return $char
 */
function reverseChar($char) {
    $byte = ord($char);
    $tmp = 0;
    for ($i = 0; $i < 8; ++$i) {
        if ($byte & (1 << $i)) {
            $tmp |= (1 << (7 - $i));
        }
    }
    return chr($tmp);
}

/**
 * 将一个字节流按比特位反转 eg: 'AB'(01000001 01000010)  --> '\x42\x82'(01000010 10000010)
 * @param $str
 */
function reverseString($str) {
    $m = 0;
    $n = strlen($str) - 1;
    while ($m <= $n) {
        if ($m == $n) {
            $str[$m] = reverseChar($str[$m]);
            break;
        }
        $ord1 = reverseChar($str[$m]);
        $ord2 = reverseChar($str[$n]);
        $str[$m] = $ord2;
        $str[$n] = $ord1;
        $m++;
        $n--;
    }
    return $str;
}

function unicode2Chinese($str)
{
    return preg_replace_callback("#\\\u([0-9a-f]{4})#i",
        function ($r) {
            return iconv('UCS-2BE', 'UTF-8', pack('H4', $r[1]));
        },
        $str);
}

function getNowTime(){
    return time();
}



