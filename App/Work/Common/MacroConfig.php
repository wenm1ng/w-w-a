<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-10-06 15:05
 */
namespace App\Work\Common;

class MacroConfig{
    /**
     * @var array 组合技能枚举
     */
    public static $groupEnum = [
        'mouse_enemy' => '/cast [@mouseover,harm]',
        'mouse_team' => '/cast [@mouseover,help]',
        'focus' => '/cast [@focus,nodead]',
        'tar_enemy' => '/cast [@target,harm]',
        'tar_team' => '/cast [@target,help]',
        'shift' => '/cast [mod:shift]',
        'alt' => '/cast [mod:alt]',
        'ctrl' => '/cast [mod:ctrl]',
        'player' => '/cast [@player]',
        'def' => '/cast ',
    ];
}