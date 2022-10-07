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

    /**
     * @desc       保存宏记录
     * @author     文明<736038880@qq.com>
     * @date       2022-10-06 17:40
     * @return bool
     */
    public function save()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new MacroService())->save($params);
        });
    }

    /**
     * @desc       获取手动创建宏菜单列表
     * @author     文明<736038880@qq.com>
     * @date       2022-10-07 14:44
     * @return bool
     */
    public function getHandMacroList(){
        return $this->apiResponse(function () {
            return (new MacroService())->getHandMacroList();
        });
    }
}