<?php
namespace App\Work;
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-10-11 13:51
 */
Class Config{
    const ADMIN_NAME = '我就是小明';
    const IMAGE_DIR = '/data/www';
    const IMAGE_HOST = 'https://mingtongct.com';
    const ACCESS_TOKEN_KEY = 'access_token';
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

    public static $pushModels = [
        //回答
        1 => 'VQUnsikNUM9pKaue4ufp4Ql8mvmKRsJCezbVtRLsEPA',
        //评论
        2 => 'UTVidyyWxD0xlnQv8xkrnu_Ar7s5OusnnyK9So_Vr1M',
        //回复
        3 => '1LaDLFdDr2zaBIXAYmJlQ4Imb3b89Owy6I8nHr-mRW4',
    ];

    public static $pushModelDataFormat = [
        //回答
        1 => [
            'thing4' => ['value' => ''],
            'name1' => ['value' => ''],
            'thing2' => ['value' => ''],
            'time3' => ['value' => ''],
        ],
    ];

    public static function getModelFormat($type, array $data){
        $modelData = self::$pushModelDataFormat[$type];
        $num = 0;
        foreach ($modelData as &$val) {
            if(!isset($data[$num])){
                break;
            }
            $val['value'] = $data[$num];
            $num++;
        }
        return $modelData;
    }
}