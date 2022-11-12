<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-12 15:01
 */
namespace App\HttpController\Api\V1\Damage;

use App\HttpController\LoginController;
use Common\Common;
use Common\CodeKey;
use Damage\Service\DamageService;

class Damage extends LoginController
{
    /**
     * @desc        获取伤害测试技能列表
     * @example
     * @return bool
     */
    public function getDamageSkillList(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new DamageService())->getDamageSkillList($params);
        });
    }

    /**
     * @desc       单个技能爬虫保存
     * @author     文明<736038880@qq.com>
     * @date       2022-11-03 11:30
     * @return bool
     */
    public function singleSkillSave(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new DamageService())->singleSkillSave($params);
        });
    }

    public function allRequest(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new DamageService())->allRequest();
        });
    }

    /**
     * @desc       获取各个职业毕业属性信息
     * @author     文明<736038880@qq.com>
     * @date       2022-11-10 14:53
     * @return bool
     */
    public function getOcAttribute(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new DamageService())->getOcAttribute($params);
        });
    }

    public function getUsageVersion(){
        return $this->apiResponse(function () {
            return (new DamageService())->getUsageVersion();
        });
    }

    public function getVersionStage(){
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();

            return (new DamageService())->getVersionStage($params);
        });
    }
}