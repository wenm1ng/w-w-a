<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-21 21:09
 */
namespace Version\Service;

use Common\Common;
use Version\Models\WowVersionModel;

class VersionService
{

    protected $versionModel;

    public function __construct($token = "")
    {
        $this->versionModel = new WowVersionModel();
    }

    public function getVersionList(){
        $versionList = redis()->get('version_list');
        if(!empty($versionList)){
            $versionList = json_decode($versionList, true);
            return $versionList;
        }
        $versionList = $this->versionModel->order(['version' => 'ASC'])->all()->toRawArray();
        redis()->set('version_list', json_encode($versionList));
        return $versionList;
    }
}