<?php
/**
 * @desc
 * @author     WenMing<st-m1ng@163.com>
 * @date       2024-02-23 23:28
 */
namespace App\Work\Config\Model;

use App\Common\EasyModel;

class ConfigModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_config';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    /**
     * @desc 获取登录过期时间
     * @return \Hyperf\Utils\HigherOrderTapProxy|\Illuminate\Support\HigherOrderTapProxy|mixed|null
     */
    public static function getExpireTime(){
        return self::where('key', 'expire_time')->value('value');
    }
}
