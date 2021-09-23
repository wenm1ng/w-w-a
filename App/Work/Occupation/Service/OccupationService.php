<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-22 0:02
 */
namespace Occupation\Service;

use Common\Common;
use Occupation\Models\WowOccupationModel;

class OccupationService
{

    protected $occupationModel;

    public function __construct($token = "")
    {
        $this->occupationModel = new WowOccupationModel();
    }

    public function getOccupationList($version){
        if(empty($version)){
            throw new \Exception('版本信息不能为空');
        }
        $occupationList = redis()->get('occupation_list:'.$version);
        if(!empty($occupationList)){
            $occupationList = json_decode($occupationList, true);
            return $occupationList;
        }
        $occupationList = $this->occupationModel->where(['version' => (int)$version])->order(['sort' => 'ASC'])->all()->toArray();
        if(!empty($occupationList)){
            redis()->set('occupation_list:'.$version, json_encode($occupationList), 3600);
        }
        return $occupationList;
    }
}