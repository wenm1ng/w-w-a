<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-09-04 11:18
 */
namespace Talent\Service;

use Common\Common;
use Talent\Models\WowTalentModel;

class TalentService
{

    protected $versionModel;

    public function __construct($token = "")
    {
        $this->talentModel = new WowTalentModel();
    }

    public function getTalentList(int $version){
        $talentList = redis()->get('talent_list:'.$version);
        if(!empty($talentList)){
            $talentList = json_decode($talentList, true);
            return $talentList;
        }
        $talentList = $this->talentModel->field('occupation,talent_id,talent_name,icon,sort')->order(['sort' => 'ASC'])->all(['version' => $version])->toRawArray();
        redis()->set('version_list', json_encode($talentList), 3600);
        return $talentList;
    }
}