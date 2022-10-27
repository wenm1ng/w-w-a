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
}