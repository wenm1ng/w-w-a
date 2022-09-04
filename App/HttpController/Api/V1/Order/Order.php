<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-03 22:23
 */
namespace App\HttpController\Api\V1\Order;

use App\Work\WxPay\Service\OrderService;
use App\HttpController\BaseController;

class Order extends BaseController
{
    /**
     * @desc        获取tab列表
     * @example
     * @return bool
     */
    public function addOrder()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new OrderService())->addOrder($params);
        });
    }

    public function wxPayCallback()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new OrderService())->wxPayCallback($params);
        });
    }

}