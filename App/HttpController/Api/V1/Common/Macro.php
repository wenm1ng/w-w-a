<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-10-06 14:48
 */
namespace App\HttpController\Api\V1\Common;

use Common\Common;
use App\Work\Common\Service\MacroService;
use App\HttpController\LoginController;

class Macro extends LoginController
{

    /**
     * @desc       获取工具列表
     * @author     文明<736038880@qq.com>
     * @date       2022-09-29 18:12
     * @return bool
     */
    public function group()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new MacroService())->group($params);
        });
    }

}