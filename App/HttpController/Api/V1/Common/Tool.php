<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-29 18:05
 */
namespace App\HttpController\Api\V1\Common;

use Common\Common;
use App\Work\Common\Service\ToolService;
use App\HttpController\LoginController;

class Tool extends LoginController
{

    /**
     * @desc       获取工具列表
     * @author     文明<736038880@qq.com>
     * @date       2022-09-29 18:12
     * @return bool
     */
    public function getToolList()
    {
        return $this->apiResponse(function () {
            return (new ToolService())->getToolList();
        });
    }
}