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
     * @return string
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
        $this->addLog(Common::getUserId(), $str);
        return $str;
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
            return;
        }
        WowMacroLogModel::query()->insert(['user_id' => $userId, 'macro_content' => $macroContent]);
    }
}