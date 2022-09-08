<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-05 11:54
 */
namespace User\Service;

/**
 * UserService不要去掉会报错
 */

use Common\Common;
use User\Validator\UserValidate;
use App\Work\WxPay\Models\WowUserWalletModel;
use App\Work\WxPay\Models\WowOrderLogModel;
use App\Exceptions\CommonException;

class WalletService
{
    protected $validator;
    public function __construct($token = "")
    {
        $this->validator = new UserValidate();
    }

    /**
     * @desc       获取用户余额
     * @author     文明<736038880@qq.com>
     * @date       2022-09-05 13:12
     * @param array $params
     *
     * @return array
     */
    public function getMoney(array $params){
        $this->validator->checkGetMoney();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $money = WowUserWalletModel::getMoney($params['type'], Common::getUserId());
        return ['money' => $money];
    }

    /**
     * @desc       金额操作及记录相关日志
     * @author     文明<736038880@qq.com>
     * @date       2022-09-08 16:16
     * @param float $money
     * @param int   $userId
     * @param int   $type
     */
    public function operateMoney(float $money, int $userId, int $type, $helpId = 0){
        WowUserWalletModel::incrementMoney($money, $userId);
        //记录订单日志
        $logData = [
            'order_type' => 1, //1帮币
            'order_id' => date('YmdHis').getRandomStr(18),
            'wx_order_id' => '',
            'date_month' => date('Y-m'),
            'pay_type' => $type, //2发布求助
            'user_id' => $userId,
            'success_at' => date('Y-m-d H:i:s'),
            'amount' => $money,
            'help_id' => $helpId
        ];
        WowOrderLogModel::query()->insert($logData);
    }
}