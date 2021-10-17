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
use Talent\Models\WowUserTalentModel;
use User\Models\WowUserModel;
use App\Work\Config;

class TalentService
{

    protected $talentModel;
    protected $talentTreeModel;
    protected $userTalentModel;
    protected $userModel;
    protected $validator;

    public function __construct($token = "")
    {
        $this->talentModel = new WowTalentModel();
        $this->talentTreeModel = new WowTalentTreeModel();
        $this->userTalentModel = new WowUserTalentModel();
        $this->userModel = new WowUserModel();
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
    public function getTalentList(int $version, string $oc = null){
        if(empty($version)){
            throw new \Exception('版本信息不能为空');
        }

        $talentList = redis()->get('talent_list:'.$version);
        if(!empty($talentList)){
            $talentList = json_decode($talentList, true);
            if(!empty($oc)){
                $newTalentList = [];
                foreach ($talentList as $val) {
                    if($oc != $val['occupation']){
                        continue;
                    }
                    $newTalentList[] = $val;
                }
                $talentList = $newTalentList;
            }
            return $talentList;
        }
        $where = ['version' => $version];
        $talentList = $this->talentModel->field('occupation,talent_id,talent_name,icon,sort')->order(['sort' => 'ASC'])->all($where)->toRawArray();
        if(!empty($oc)){
            $newTalentList = [];
            foreach ($talentList as $val) {
                if($oc != $val['occupation']){
                    continue;
                }
                $newTalentList[] = $val;
            }
            $talentList = $newTalentList;
        }
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

        $redisKey = Config::getTalentSkillTreeRedisKey($version, $talentId, $oc);
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

    /**
     * @desc       　保存用户天赋信息
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param $params
     *
     * @return array
     */
    public function saveUserTalent($params){
        $this->validator->checkSaveUserTalent();
        if (!$this->validator->validate($params)) {
            throw new \Exception($this->validator->getError()->__toString());
        }
        //获取用户id
        $userInfo = $this->userModel->get(['openId' => $params['openId']])->field('user_id')->toRawArray();
        $dbData = [
            'user_id' => $userInfo['user_id'] ?? 0,
            'version' => $params['version'],
            'occupation' => $params['oc'],
            'title' => $params['title'],
            'statis' => $params['statis'],
            'points' => $params['points'],
            'actPoints' => $params['actPoints'],
            'type' => $params['type'],
            'description' => $params['description'] ?? '',
            'talent_skill_id_json' => json_encode($params['talent_ids']),
        ];

        if(!empty($params['id'])){
            //修改
            $this->userTalentModel->update($dbData, ['wut_id' => $params['id']]);
        }else{
            //新增
            $this->userTalentModel->create($dbData)->save();
        }
        return [];
    }
}