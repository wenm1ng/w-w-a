<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-22 10:48
 */
namespace App\HttpController\Api\V1\Wallet;

use App\HttpController\BaseController;
use Common\Common;
use Common\CodeKey;
use User\Service\WalletService;

class Wallet extends BaseController
{
    /**
     * @desc       转换币种
     * @author     文明<736038880@qq.com>
     * @date       2022-09-22 13:13
     * @param array $params
     *
     * @return bool
     */
    public function transformMoney(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new WalletService())->transformMoney($params);
        });
    }
}