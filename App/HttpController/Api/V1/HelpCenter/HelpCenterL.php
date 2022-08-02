<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-08-02 16:22
 */
namespace App\HttpController\Api\V1\HelpCenter;

use App\HttpController\BaseController;
use Common\Common;
use Common\CodeKey;
use App\Work\HelpCenter\Service\HelpCenterService;

class HelpCenterL extends BaseController
{
    /**
     * @desc       发布求助
     * @author     文明<736038880@qq.com>
     * @date       2022-08-02 16:23
     * @return bool
     */
    public function addHelp(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new HelpCenterService())->addHelp($params,  $this->request());
        });
    }
}