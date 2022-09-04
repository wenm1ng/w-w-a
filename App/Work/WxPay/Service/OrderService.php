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
    protected $logName = 'wxPayCallback';

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
        $params['money'] = 0.01;
        $money = $params['money'] * 100;
        $outTradeNo = date('YmdHis').getRandomStr(18);
        $result = (new WxPayService())->wxAddOrder($money, $userInfo['openId'], $outTradeNo);
        if($result['code'] !== 200 || empty($result['data']['prepay_id'])){
            CommonException::msgException($result['message'], CodeKey::WXPAY_ERROR);
        }

        $insertData = [
            'type' => 1,
            'order_status' => 1,
            'order_money' => $params['money'],
            'wx_money' => $money,
            'order_id' => $outTradeNo,
            'user_id' => $userId,
            'prepay_id' => $result['data']['prepay_id']
        ];
        WowOrderModel::query()->insert($insertData);

        $prepayId = $result['data']['prepay_id'];
        $returnData = WxPayService::getSign($prepayId);

        return $returnData;
    }

    /**
     * @desc        微信支付回调
     * @example
     * @param array $params
     *
     * @return array
     */
    public function wxPayCallback(array $params){
        Common::log('wxPayCallback params:'. json_encode($params), $this->logName);
        $returnJson = (new WxPayService())->decryptToString($params['resource']['associated_data'], $params['resource']['nonce'], $params['resource']['ciphertext']);
        Common::log('wxPayCallback response:'.$returnJson, $this->logName);
        $return = json_decode($returnJson, true);
        if(!is_array($return) || empty($return['out_trade_no'])){
            CommonException::msgException('签名错误');
        }
        $this->callbackUpdateOrder($return['out_trade_no'], $return['transaction_id'], $returnJson);
        return [];
    }

    /**
     * @desc        支付回调修改订单信息
     * @example
     * @param string $tradeNo
     * @param string $transactionId
     * @param string $callbackJson
     */
    public function callbackUpdateOrder(string $tradeNo, string $transactionId, string $callbackJson){
        $updateData = [
            'wx_order_id' => $transactionId,
            'callback_json' => $callbackJson,
            'order_status' => 2 //2支付成功
        ];
        WowOrderModel::query()->where('order_id', $tradeNo)->update($updateData);
    }
}