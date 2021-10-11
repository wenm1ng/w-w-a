<?php
namespace App\Work;
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-11 13:51
 */
Class Config{
    //获取天赋树技能列表的redis key
    public static function getTalentSkillTreeRedisKey($version, $talentId, $oc){
        return "talent_tree_list:{$version}:{$oc}:{$talentId}";
    }

    //获取职业技能列表的redis key
    public static function getOcSkillRedisKey($version, $oc){
        return "oc_skill_list:{$version}:{$oc}";
    }
}