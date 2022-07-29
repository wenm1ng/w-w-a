<?php
namespace App\Work;
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-11 13:51
 */
Class Config{
    const ADMIN_NAME = '我就是小明';
    const IMAGE_DIR = '/data/www/image';
    const IMAGE_HOST = 'http://119.29.1.85:83';
    //获取天赋树技能列表的redis key
    public static function getTalentSkillTreeRedisKey($version, $oc){
        return "talent_tree_list:{$version}:{$oc}";
    }

    //获取职业技能列表的redis key
    public static function getOcSkillRedisKey($version, $oc){
        return "oc_skill_list:{$version}:{$oc}";
    }

    /**
     * @var string[] 帮助类型
     */
    public static $helpTypeLink = [
        1 => '插件研究',
        2 => '副本专区',
        3 => '任务/成就',
        4 => '人员招募',
        5 => '幻化讨论',
        6 => '宠物讨论',
        7 => '竞技场/战场',
        8 => '地精商会',
        9 => '新版本讨论'
    ];
}