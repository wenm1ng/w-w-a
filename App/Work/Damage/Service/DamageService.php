<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-11 11:45
 */
namespace Damage\Service;

use Common\Common;
use Damage\Models\WowSkillModel;
use Talent\Models\WowTalentTreeModel;
use App\Work\Config;
use Talent\Service\TalentService;

class DamageService
{

    protected $skillModel;
    protected $talentTreeModel;

    public function __construct()
    {
        $this->skillModel = new WowSkillModel();
        $this->talentTreeModel = new WowTalentTreeModel();
    }

    /**
     * @desc       　获取进行伤害测试的技能列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $version
     *
     * @return array|mixed
     */
    public function getDamageSkillList($params){
        $version = $params['version'];
        $talentId = $params['talent_id'];
        $oc = $params['oc'];
        //获取天赋树技能
        $talentService = new TalentService();
        $treeList = $talentService->getTalentSkillTree($version, $talentId, $oc);
        //获取职业技能
        $ocSkillList = $this->getOcSkillList($version, $oc);
        //将技能进行组合返回给前端
        $this->dealSkillData($treeList, $ocSkillList, $params);
    }

    /**
     * @desc       　处理天赋树技能和职业技能
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $treeList  //天赋树技能
     * @param $ocSkillList  //职业技能
     */
    private function dealSkillData($treeList, $ocSkillList, $params){
        //获取角色各属性值
        //统一技能格式
        //设置好技能伤害、冷却时间等
        foreach ($treeList as $skill) {

        }
    }

    /**
     * @desc       　获取职业技能列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $version
     * @param $oc
     *
     * @return array|mixed
     */
    public function getOcSkillList($version, $oc){
        $redisKey = Config::getOcSkillRedisKey($version, $oc);
        $list = redis()->get($redisKey);
        $list = json_decode($list, true);
        if(!empty($list) && is_array($list)){
            return $list;
        }
        $list = $this->skillModel->all(['version' => $version, 'occupation' => $oc])->toRawArray();
        redis()->set($redisKey, json_encode($list), 3600);
        return $list;
    }
}