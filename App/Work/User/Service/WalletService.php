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
}