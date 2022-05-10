<?php
namespace App\Work;
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-11 13:51
 */
Class Config{
    const ADMIN_NAME = '我就是小明';
    //获取天赋树技能列表的redis key
    public static function getTalentSkillTreeRedisKey($version, $oc){
        return "talent_tree_list:{$version}:{$oc}";
    }

    //获取职业技能列表的redis key
    public static function getOcSkillRedisKey($version, $oc){
        return "oc_skill_list:{$version}:{$oc}";
    }
}