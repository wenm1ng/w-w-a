<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-06-10 11:07
 */
namespace App\HttpController\Api\V1\Wa;

use App\HttpController\BaseController;
use Wa\Service\WaService;

class WaL extends BaseController
{
    /**
     * @desc        获取tab列表
     * @example
     * @return bool
     */
    public function toComment()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new WaService())->toComment($params);
        });
    }

    /**
     * @desc       　删除评论
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function delComment(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new WaService())->delComment($params);
        });
    }
}