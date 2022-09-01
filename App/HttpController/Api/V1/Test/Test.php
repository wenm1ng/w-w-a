<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-20 14:20
 */namespace App\HttpController\Api\V1\Test;

use App\HttpController\LoginController;
use Common\Common;
use Common\CodeKey;
use Wa\Service\WaService;
use User\Service\UserService;

class Test extends LoginController
{
    /**
     * @desc        获取tab列表
     * @example
     * @return bool
     */
    public function test()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new UserService())->getAccessToken();
        });
    }
}