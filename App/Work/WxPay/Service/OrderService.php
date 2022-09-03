<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-03 22:31
 */
namespace App\Work\WxPay\Service;

use Common\Common;
use App\Work\WxPay\Models\WowOrderModel;
use App\Work\Validator\OrderValidator;
use App\Exceptions\CommonException;
use Common\CodeKey;

class OrderService{
    protected $validator;

    public function __construct()
    {
        $this->validator = new OrderValidator();
    }

    /**
     * @desc        创建订单
     * @example
     * @param array $params
     *
     * @return mixed
     */
    public function addOrder(array $params){
        $this->validator->checkAddOrder();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        $userInfo = Common::getUserInfo();
        $userId = $userInfo['user_id'];

        $money = $params['money'] * 100;
        $outTradeNo = getRandomStr(32);
        $result = (new WxPayService())->wxAddOrder($money, $userInfo['openId'], $outTradeNo);
        if($result['code'] !== 200){
            CommonException::msgException($result['message'], CodeKey::WXPAY_ERROR);
        }

        $insertData = [
            'type' => 1,
            'order_status' => 1,
            'order_money' => $params['money'],
            'order_id' => $outTradeNo,
            'user_id' => $userId,
            'prepay_id' => $result['data']['prepay_id']
        ];
        WowOrderModel::query()->insert($insertData);

        return $result['data'];
    }
}