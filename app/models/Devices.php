<?php
namespace app\models;
use  Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Devices extends Model
{
    use SoftDeletes;

    protected $table = 'iot_device';

    /**
     * 可批量赋值的属性。
     *
     * @var array
     */
    protected $fillable = ['sn','status','imei','iccid','net','version'];


    public static function getDeviceBySN($sn){
        return self::where('sn',$sn)->first();
    }



}