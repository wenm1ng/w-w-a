<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-28 13:53
 */
namespace App\HttpController\Api\V1\HelpCenter;

use App\HttpController\LoginController;
use Common\Common;
use Common\CodeKey;
use App\Work\HelpCenter\Service\HelpCenterService;

class HelpCenter extends LoginController
{
    /**
     * @desc       获取帮助列表
     * @author     文明<736038880@qq.com>
     * @date       2022-07-28 14:40
     * @return bool
     */
    public function getHelpList()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new HelpCenterService())->getHelpList($params);
        });
    }

    /**
     * @desc       获取帮助详情
     * @author     文明<736038880@qq.com>
     * @date       2022-08-02 18:13
     * @return bool
     */
    public function getHelpInfo(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            $id = $params['id'] ?? 0;
            return (new HelpCenterService())->getHelpInfo($id);
        });
    }
}