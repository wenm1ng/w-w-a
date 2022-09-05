<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-05 10:47
 */
namespace App\Work\WxPay\Models;

use App\Common\EasyModel;

class WowUserWalletModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_user_wallet';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

//    protected $casts = [
//        'money' => 'float',
//    ];
    /**
     * @desc       自增money
     * @author     文明<736038880@qq.com>
     * @date       2022-09-05 11:57
     * @param int       $type
     * @param int       $userId
     * @param float|int $money
     */
    public static function incrementMoney(int $type, int $userId, float $money = 0)
    {
        $id = self::query()->where('user_id', $userId)->where('type', $type)->value('id');
        if(empty($id)){
            //新增钱包数据
            $insertData = [
                'type' => $type,
                'money' => $money,
                'user_id' => $userId
            ];
            self::query()->insert($insertData);
        }else{
            self::query()->where('id', $id)->increment('money', $money);
        }
    }

    /**
     * @desc       获取用户余额
     * @author     文明<736038880@qq.com>
     * @date       2022-09-05 12:01
     * @param int $type
     * @param int $userId
     *
     * @return int|mixed
     */
    public static function getMoney(int $type, int $userId){
        $info = self::query()->where('user_id', $userId)->where('type', $type)->first(['money']);
        if(empty($info)){
            $money = 0;
        }else{
            $info = $info->toArray();
            $money = $info['money'];
        }
        return $money;
    }
}