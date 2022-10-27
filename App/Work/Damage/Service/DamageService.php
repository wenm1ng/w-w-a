<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-11 11:45
 */
namespace Damage\Service;

use Common\Common;
use Damage\Models\WowSkillNewModel;
use Talent\Models\WowTalentTreeModel;
use App\Work\Config;
use Talent\Service\TalentService;
use App\Utility\Database\Db;

class DamageService
{

    protected $skillModel;
    protected $talentTreeModel;

    public function __construct()
    {
//        $this->skillModel = new WowSkillModel();
//        $this->talentTreeModel = new WowTalentTreeModel();
    }

    public function test(){
//        $list = $this->talentTreeModel->all(['content' => ['%技能，%','like']])->toRawArray();;
//        $id = array_column($list, 'wtr_id');
//        dump($id);
//        $this->talentTreeModel->update(['is_active' => 1], ['wtr_id' => [$id, 'in']]);
        $list = $this->talentTreeModel->all()->toRawArray();
        $skillList = $this->skillModel->all(['sk'])->toRawArray();
        $skillList = array_column($skillList, 'ws_id', '');
        $id = array_column($list, 'wtr_id');

        foreach ($list as $val) {
            $updateData = [];
            if(strpos($val['content'], '技能，') !== false){
                $updateData['is_active'] = 1;
            }
            if(preg_match("/.*?你的(.*?)技能.*?减少.*?点/", $val['content'], $match)){

            }elseif(preg_match("/.*?你的(.*?)技能.*?提高.*?%/", $val['content'], $match)){
                if(strpos($val['content'], '致命一击') !== false){

                }
            }elseif(preg_match("/.*?你的(.*?)技能.*?提高.*?点/", $val['content'], $match)){
                if(strpos($val['content'], '致命一击') !== false){

                }
            }
        }
        $this->talentTreeModel->update(['is_active' => 0], ['wtr_id' => [$id, 'in']]);

    }

    public function crawlerSkill(array $params){
//        $result = (new \CloudKit\Tools\TranslateManage())->translateNaverInside(['from_lang' => 'ko', 'target_lang' => 'zh', 'content' => ['aa' => '티|셔|츠']]);
//        dump($result);exit;
//        dump(EASYSWOOLE_ROOT);exit;
//        $rs = CodeKey::result();
//        $params = $this->getRequestJsonData();
//        try{
//            if(empty($params['result'])){
//                throw new \Exception('数据不对');
//            }
//            $sql = [];
//            $params = $params['result'];
//            foreach ($params as $talentNum => $talent) {
//                foreach ($talent as $hangNum => $hangVal) {
//                    foreach ($hangVal as $skill) {
//                        if(empty($skill)){
//                            continue;
//                        }
//                        unset($skill['id']);
//                        $insertStr = implode("','", array_values($skill));
//                        $insertStr = "'{$insertStr}'";
//                        $sql[] = "insert into wow_talent_tree (`version`,`occupation`,`talent_id`,`is_active`,`actReqSpecPoints`,`actReqTalPoints`,`actTarget2X`,`actTarget2Y`,`actTargetX`,`actTargetY`,`arrowType`,`content`,`currentActPoints`,`currentPoints`,`effect2End`,`effect2Init`,`effect2Per`,`effect3End`,`effect3Init`,`effect3Per`,`effect4End`,`effect4Init`,`effect4Per`,`effectEnd`,`effectInit`,`effectName1`,`effectName2`,`effectPer`,`icon`,`maxPoint`,`name`,`positionX`,`positionY`,`spec`,`wClass`) values(2,'',0,0,{$insertStr})";
//                    }
//                }
//            }
//            $rs[CodeKey::STATE] = CodeKey::SUCCESS;
//            $rs[CodeKey::DATA] = implode(";", $sql);
//        }catch (\Exception $e){
//            $rs[CodeKey::MSG] = $e->getMessage();
//        }
//        $this->writeResultJson($rs);
        if(empty($params['result'])){
            throw new \Exception('数据不对');
        }
        if(empty($params['oc'])){
            throw new \Exception('职业不能为空');
        }
        $oc = $params['oc'];
        $sql = [];
        $skills = [];
        $params = $params['result'];
        foreach ($params as $talentNum => $talent) {
            if(empty($talent)){
                continue;
            }
            foreach ($talent as $hangNum => $skill) {
                if(empty($skill)){
                    continue;
                }
                unset($skill['id']);
                unset($skill['learnBook']);
                $skills[$skill['localesName']] = $skill;
//                        $insertStr = implode("','", array_values($skill));
//                        $insertStr = "'{$insertStr}'";
//                        $sql[] = "insert into wow_skill (`version`,`occupation`,`talent_id`,`is_active`,`actReqSpecPoints`,`actReqTalPoints`,`actTarget2X`,`actTarget2Y`,`actTargetX`,`actTargetY`,`arrowType`,`content`,`currentActPoints`,`currentPoints`,`effect2End`,`effect2Init`,`effect2Per`,`effect3End`,`effect3Init`,`effect3Per`,`effect4End`,`effect4Init`,`effect4Per`,`effectEnd`,`effectInit`,`effectName1`,`effectName2`,`effectPer`,`icon`,`maxPoint`,`name`,`positionX`,`positionY`,`spec`,`wClass`) values(2,'',0,0,{$insertStr})";
            }
        }

        foreach ($skills as $val) {
            $temp = array_values($val);
//                $insertStr = str_replace("'", "\'", $temp);
            $insertStr = implode("','", $temp);
            $insertStr = "'{$insertStr}'";
            preg_match("/(\d+).*?/", $val['costText'], $match);
            $consume = $match[1] ?? 0;
            preg_match("/(\d+)分钟.*?/", $val['coolDownText'], $match);
            if(!empty($match[1])){
                $cool_time = $match[1] * 60;
            }else{
                preg_match("/(\d+)秒.*?/", $val['coolDownText'], $match);
                $cool_time = $match[1] ?? 0;
            }
            preg_match("/(\d+).*?/", $val['spellTimeText'], $match);
            $read_time = $match[1] ?? 0;
            $hurtArr = $this->getHurtInfo($val['spellDescLoc']);
            $sql[] = "insert into wow_skill (`version`,`occupation`,`is_active`,`consume`,`cool_time`,`read_time`,`hurt`,`max_hurt`,`keep_time`,`hurt_type`,`is_weapon_hurt`,`hurt_times`,`hurt_unit`,`coolDownText`,`costText`,`distanceText`,`icon`,`isTalent`,`localesName`,`name`,`rankDesc`,`reqClass`,`reqLevel`,`reqRace`,`reqSpec`,`spellDescLoc`,`spellTimeText`,`trainingCost`) values(4,'{$oc}',1,{$consume},{$cool_time},{$read_time},{$hurtArr['hurt']},{$hurtArr['max_hurt']},{$hurtArr['keep_time']},{$hurtArr['hurt_type']},{$hurtArr['is_weapon_hurt']},{$hurtArr['hurt_times']},{$hurtArr['hurt_unit']},{$insertStr})";
        }

        return $sql;
    }

    public function getHurtInfo($text){
//        .*?造成(\d+)到(\d+).*?
//        .*?降低(\d+).*?
//        .*?提高(\d+)点.*?
//        .*?提高(\d+)%.*?
//
//
//        .*?累计(\d+).*?
//        .*?共计强度(\d+)%.*?
//        .*?造成(\d+).*?
//        .*?外加(\d+).*?
//        .*?加上(\d+).*?
//

//
//
//        武器伤害
        $return = ['hurt' => 0, 'max_hurt' => 0, 'is_weapon_hurt' => 0, 'keep_time' => 0, 'hurt_times' => 0, 'hurt_unit' => 1, 'hurt_type' => 1];
        //伤害数量
        if(preg_match("/.*?造成(\d+)到(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['max_hurt'] = $match[2];
            $return['hurt_type'] = 1;
        }else if(preg_match("/.*?降低(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 4;
        }else if(preg_match("/.*?降低(\d+)%.*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 4;
            $return['hurt_unit'] = 2;
        }else if(preg_match("/.*?提高(\d+)点.*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 2;
        }else if(preg_match("/.*?提高(\d+)%.*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 2;
            $return['hurt_unit'] = 2;
        }else if(preg_match("/.*?累计(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 1;
        }else if(preg_match("/.*?共计强度(\d+)%.*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 1;
            $return['hurt_unit'] = 2;
        }else if(preg_match("/.*?造成(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 1;
        }else if(preg_match("/.*?外加(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 1;
        }else if(preg_match("/.*?加上(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 1;
        }else if(preg_match("/.*?累计(\d+).*?/", $text, $match)){
            $return['hurt'] = $match[1];
            $return['hurt_type'] = 1;
        }
        //        致命一击
//
//        时间：
//        .*?持续(\d+)分钟.*?
//        .*?持续(\d+)秒.*?
//        .*?(\d+)秒内.*?
//
//        次数：
//        .*?累加(\d+)次.*?
        //武器伤害
        if(strpos($text, '武器伤害') !== false){
            $return['is_weapon_hurt'] = 1;
        }
        //暴击判断
        if(strpos($text, '致命一击') !== false){
            $return['hurt_type'] = 3;
        }
        //冷却时间
        if(preg_match("/.*?持续(\d+)分钟.*?/", $text, $match)){
            $return['keep_time'] = $match[1]*60;
        }else if(preg_match("/.*?持续(\d+)秒.*?/", $text, $match)){
            $return['keep_time'] = $match[1];
        }else if(preg_match("/.*?(\d+)秒内.*?/", $text, $match)){
            $return['keep_time'] = $match[1];
        }
        //累计次数
        if(preg_match("/.*?累加(\d+)次.*?/", $text, $match)){
            $return['hurt_times'] = $match[1];
        }

        return $return;
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
        $oc = $params['oc'];
        //        $talentId = $params['talent_id'];

        //获取天赋树技能
//        $talentService = new TalentService();
//        $treeList = $talentService->getTalentSkillTree($version, $talentId, $oc);
        //获取职业技能
        $ocSkillList = $this->getOcSkillList($version, $oc);
//        //将技能进行组合返回给前端
//        $this->dealSkillData($ocSkillList, $params);
        return $ocSkillList;
    }

    /**
     * @desc       　处理天赋树技能和职业技能
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $treeList  //天赋树技能
     * @param $ocSkillList  //职业技能
     */
    private function dealSkillData($ocSkillList, $params){
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
//        $redisKey = Config::getOcSkillRedisKey($version, $oc);
//        $list = redis()->get($redisKey);
//        $list = json_decode($list, true);
//        if(!empty($list) && is_array($list)){
//            return $list;
//        }
        $fields = 'ws_id,version,occupation,is_active,consume,cool_time,read_time,is_weapon_hurt,hurt,second_hurt,every_second_hurt,target_num,max_hurt,keep_time,hurt_unit,hurt_type,hurt_times,tri_rate,icon,localesName as skill_name,spellDescLoc as skill_desc,costText as cost_text,spellTimeText as spell_text';
        $list = WowSkillNewModel::query()->where('version', $version)->where('occupation', $oc)->whereIn('hurt_type', [1,2,3,4,7])->select(Db::raw($fields))->get()->toArray();
//        redis()->set($redisKey, json_encode($list), 3600 * 24);
        return $list;
    }
}