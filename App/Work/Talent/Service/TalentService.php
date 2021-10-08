<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-09-04 11:18
 */
namespace Talent\Service;

use Common\Common;
use Talent\Models\WowTalentModel;
use Talent\Models\WowTalentTreeModel;
use Talent\Validator\TalentValidator;

class TalentService
{

    protected $talentModel;
    protected $talentTreeModel;
    protected $validator;

    public function __construct($token = "")
    {
        $this->talentModel = new WowTalentModel();
        $this->talentTreeModel = new WowTalentTreeModel();
        $this->validator = new TalentValidator();
    }

    /**
     * @desc       　天赋列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $version **版本号
     *
     * @return array|mixed
     */
    public function getTalentList(int $version){
        if(empty($version)){
            throw new \Exception('版本信息不能为空');
        }
        $talentList = redis()->get('talent_list:'.$version);
        if(!empty($talentList)){
            $talentList = json_decode($talentList, true);
            return $talentList;
        }
        $talentList = $this->talentModel->field('occupation,talent_id,talent_name,icon,sort')->order(['sort' => 'ASC'])->all(['version' => $version])->toRawArray();
        redis()->set('talent_list:'.$version, json_encode($talentList), 3600);
        return $talentList;
    }

    /**
     * @desc       　获取天赋技能树
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $version **版本号
     * @param $talentId **天赋id
     *
     * @return array
     */
    public function getTalentSkillTree($version, $talentId, $oc):array{
        if(empty($version)){
            throw new \Exception('版本号不能为空');
        }
        if(empty($talentId)){
            throw new \Exception('天赋号不能为空');
        }
        if(empty($oc)){
            throw new \Exception('职业不能为空');
        }

        $redisKey = "talent_tree_list:{$version}:{$oc}:{$talentId}";
        $treeList = redis()->get($redisKey);
        if(!empty($treeList)){
            $treeList = json_decode($treeList, true);
            return $treeList;
        }

        $where = [
            'version' => $version,
            'talent_id' => $talentId,
            'occupation' => $oc
        ];
        $treeList = $this->talentTreeModel->all($where)->toRawArray();
        redis()->set($redisKey, json_encode($treeList), 3600);
        return $treeList;
    }

    public function saveUserTalent($params){
        $this->validator->checkSaveUserTalent();
        if (!$this->validator->validate($params)) {
            throw new \Exception($this->validator->getError()->__toString());
        }

        $dbData = [
            'openId'
        ];
        if(!empty($params['id'])){
            //修改
        }else{
            //新增
        }
    }
}