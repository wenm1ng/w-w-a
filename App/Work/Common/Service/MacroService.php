<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-10-06 14:52
 */
namespace App\Work\Common\Service;

use App\Work\Common\Models\WowMacroLogModel;
use App\Work\Common\Models\WowToolChildModel;
use App\Utility\Database\Db;
use App\Exceptions\CommonException;
use App\Work\Common\MacroConfig;
use Common\Common;

class MacroService
{
    /**
     * @desc       技能组合宏
     * @author     文明<736038880@qq.com>
     * @date       2022-10-06 15:14
     * @param array $params
     *
     * @return array
     */
    public function group(array $params)
    {
        $enum = MacroConfig::$groupEnum;
        $str = "#showtooltip\r\n";
        foreach ($params as $key => $val) {
            if(isset($enum[$key]) && !empty($val)){
                $str .= $enum[$key].$val."\r\n";
            }
        }
        $logId = $this->addLog(Common::getUserId(), $str);
        return ['content' => $str, 'id' => $logId];
    }

    /**
     * @desc       记录宏日志
     * @author     文明<736038880@qq.com>
     * @date       2022-10-06 15:24
     * @param int    $userId
     * @param string $macroContent
     */
    private function addLog(int $userId, string $macroContent){
        if($macroContent === "#showtooltip\r\n"){
            return 0;
        }
        return WowMacroLogModel::query()->insertGetId(['user_id' => $userId, 'macro_content' => $macroContent]);
    }

    /**
     * @desc       保存用户宏记录
     * @author     文明<736038880@qq.com>
     * @date       2022-10-06 17:43
     * @param array $params
     *
     * @return array
     */
    public function save(array $params){
        if(empty($params['id'])){
           CommonException::msgException('id不能为空');
        }

        WowMacroLogModel::query()->where('id', $params['id'])->update(['status' => 1, 'user_id' => Common::getUserId()]);
        return [];
    }

    /**
     * @desc       获取手动创建宏菜单列表
     * @author     文明<736038880@qq.com>
     * @date       2022-10-07 14:43
     * @return array
     */
    public function getHandMacroList(){
        return MacroConfig::getHandList();
    }

    public function handCombine(array $params){
        if(empty($params['action'])){
            CommonException::msgException('参数有误');
        }
        $handList = $this->getHandMacroList();
        $actionArr = $params['action'];
        $action = $handList['list'][$actionArr[0]]; //首级 /cast 动作指令等
        $
    }
}