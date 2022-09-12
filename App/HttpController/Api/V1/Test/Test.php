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
use User\Service\LeaderBoardService;

class Test extends LoginController
{
    /**
     * @desc       同步缓存
     * @author     文明<736038880@qq.com>
     * @date       2022-09-12 10:45
     * @return bool
     */
    public function aKeySyncRedis()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new LeaderBoardService())->aKeySyncRedis();
        });
    }
}