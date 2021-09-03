<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-20 22:56
 */
namespace Common;

use EasySwoole\Utility\File;
use EasySwoole\EasySwoole\Logger;
use EasySwoole\EasySwoole\Core;
use EasySwoole\EasySwoole\Config;

Class Common{
    /**
     * 加载配置
     */
    public static function loadConf()
    {
        if( Core::getInstance()->isDev()){
            $files = File::scanDirectory(EASYSWOOLE_ROOT . '/App/ConfigDev');
        } else {
            $files = File::scanDirectory(EASYSWOOLE_ROOT . '/App/Config');
        }

        if (is_array($files)) {
            foreach ($files['files'] as $file) {
                $fileNameArr = explode('.', $file);
                $fileSuffix = end($fileNameArr);
                if ($fileSuffix == 'php') {
                    Config::getInstance()->loadFile($file);
                }
            }
        }
    }

    /**
     * 更换数组的键名
     * @param array $arr
     * @param string $key
     * @param bool $is_multiple
     * @param bool $get_first_item 选择第一个
     * @return array
     */
    public static function arrayKeyChange(array $arr, $key, $is_multiple = false,$get_first_item = false): array
    {
        $result = [];
        if (empty($arr)) {
            return $result;
        }
        if (empty($key)) {
            return $result;
        }
        foreach ($arr as $v) {
            if (!isset($v[$key])) {
                continue;
            }
            if ($is_multiple) {
                $result[$v[$key]][] = $v;
            } else {
                if ($get_first_item){
                    if (isset( $result[$v[$key]])){
                        continue;
                    }else{
                        $result[$v[$key]] = $v;
                    }
                }else{
                    $result[$v[$key]] = $v;
                }
            }
        }
        return $result;
    }

    /******************************很重要勿动（影响walmart刊登）***************************************/
    //是否开启刊登脚本本地调试
    public static function getIsPublishDebug(){
        return false;
    }
    /******************************很重要勿动（影响walmart刊登）***************************************/

    /**
     * 设置多主键，通过_连接
     * @param $data array
     * @param $keys array
     * @param $is_multiple bool
     * @return array
     * @author     zhy    find404@foxmail.com
     * @createTime 2020年8月20日 10:47:17
     */
    public static function arrayKeysChange(array $data, array $keys, bool $is_multiple = false): array
    {
        $result = [];
        if (empty($data)) {
            return $result;
        }
        if (empty($keys)) {
            return $result;
        }

        foreach ($data as $dataVal) {
            $newKey = '';
            foreach ($keys as $keysVal) {
                if (!isset($dataVal[$keysVal])) {
                    continue;
                }
                $newKey .= empty($newKey) ? $dataVal[$keysVal] : '_' . $dataVal[$keysVal];
            }


            if ($is_multiple) {
                $result[$newKey][] = $dataVal;
            } else {
                $result[$newKey] = $dataVal;
            }
        }
        return $result;
    }

    /**
     * 更换数组的键名多维
     * @param unknown $arr
     * @param unknown $key
     * @return multitype:unknown
     */
    public static function multiArrayKeyChanges(array $arr, $key)
    {
        $return = array();
        foreach ($arr as $k => $v) {
            $return[$v[$key]][$k] = $v;
        }
        return $return;
    }

    public static function arrayGroup(array $arr, $groupKey, $beValKey = ''){
        $return = [];
        if($beValKey){
            foreach ($arr as $key => $val) {
                $return[$val[$groupKey]][] = $val[$beValKey];
            }
        }else{
            foreach ($arr as $key => $val) {
                $return[$val[$groupKey]][] = $val;
            }
        }
        return $return;
    }

    /**
     * @desc 获取用户token
     * @return mixed
     */
    public static function getUserToken()
    {
        global $_USER_INFO;
        return $_USER_INFO['token'] ?? "";
    }

    /**
     * @desc userToken
     * @param string $token
     * @return mixed
     */
    public static function setUserToken($token)
    {
        global $_USER_INFO;
        if (!$_USER_INFO instanceof SplContextArray) {
            $_USER_INFO = new SplContextArray();
        }
        $_USER_INFO['token'] = $token;
    }


    public static function getUserInfo()
    {
        $token = Common::getUserToken();
        $userInfo = $token ? (new AuthManage($token))->getUserInfo() : "";
        return $userInfo ?? [];
    }


    public static function getUserId()
    {
        $userInfo = Common::getUserInfo();

        return $userInfo['userId'] ?? '';
    }

    public static function getUserNameByUserId(int $userId)
    {
        $userInfo = (new AuthManage())->getUserInfoById($userId);

        return $userInfo['user_name'] ?? "";
    }

    // 根据用户id获取用户信息
    public static function getUserInfoList()
    {
        return (new UserService())->all();
    }

    /**
     * 获取当前公司信息
     * @param string $companyCode
     * @return mixed
     */
    public static function getCompanyInfo($companyCode = "")
    {
        if (empty($companyCode))
            $companyCode = Company::getCompanyCode();

        return (new CompanyService())->getCompanyInfoByCompanyCode($companyCode) ?? [];
    }

    /**
     * 格式化创建人/修改人
     * @param Array 二维数组
     * @param string createIdFiled 创建人
     * @param string modifyIdFiled 修改人
     */
    public static function formatData(array $data, $createIdFiled = 'create_id', $modifyIdFiled = 'modify_id')
    {
        if (count($data)) {
            $users = Common::getUserInfoList();
            foreach ($data as $key => $v) {
                if (array_key_exists($v[$createIdFiled], $users)) {
                    !is_array($users[$v[$createIdFiled]]) && $users[$v[$createIdFiled]] = json_decode($users[$v[$createIdFiled]], true);
                }

                if (array_key_exists($v[$modifyIdFiled], $users)) {
                    !is_array($users[$v[$modifyIdFiled]]) && $users[$v[$modifyIdFiled]] = json_decode($users[$v[$modifyIdFiled]], true);
                }

                $data[$key]['create_name'] = $users[$v[$createIdFiled]]['user_name'] ?? "";
                $data[$key]['modify_name'] = $users[$v[$modifyIdFiled]]['user_name'] ?? "";
            }
        }

        return $data;
    }

    /**
     * 格式化用户名称
     * @param Array 二维数组
     * @param string filed 用户id列名
     * @param string filed_name 用户id返回列名
     */
    public static function formatDataUserId(array $data, $filed = 'user_id', $filed_name = 'user_name')
    {
        if (count($data)) {
            $users = Common::getUserInfoList();
            foreach ($data as $key => $v) {
                if (array_key_exists($v[$filed], $users)) {
                    !is_array($users[$v[$filed]]) && $users[$v[$filed]] = json_decode($users[$v[$filed]], true);
                }

                $data[$key][$filed_name] = $users[$v[$filed]]['user_name'] ?? "";
            }
        }

        return $data;
    }


    /**
     * 转换时间格式
     * @param $dateTime
     * @param bool $date
     * @return false|string
     */
    public static function toIos8601String($dateTime, $date = false)
    {
        if ($date) {
            return date($dateTime, strtotime($date));
        }
        return date($dateTime, time());
    }


    /**
     * 获取字符串大小
     * @param $str
     * @return string
     */
    public static function stringSize($str)
    {
        $str_len = strlen($str);
        $str_size = $str_len - ($str_len / 8) * 2;
        return number_format(($str_size / 1024), 2);
    }


    /**
     * 获取随机字符串 或者数字
     * @param number $length 长度
     * @param string $type 类型
     * @param number $convert 转换大小写
     * @return string 随机字符串
     */
    public static function getRandomCode($length = 6, $type = 'all', $convert = 0)
    {
        $config = array(
            'number' => '1234567890',
            'letter' => 'abcdefghjkmnpqrstuvwxyz',
            'string' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            'all' => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890'
        );

        if (!isset($config[$type]))
            $type = 'string';
        $string = $config[$type];
        $code = '';
        $strlen = strlen($string) - 1;
        for ($i = 0; $i < $length; $i++) {
            $code .= $string{mt_rand(0, $strlen)};
        }
        if (!empty($convert)) {
            $code = ($convert > 0) ? strtoupper($code) : strtolower($code);
        }
        return $code;
    }


    /**
     * 生成顺序数字
     * @param int $length
     * @return array
     */
    public static function rankNumber($length = 5)
    {
        $seed = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9);
        $str = [];
        for ($i = 0; $i < $length; $i++) {
            $rand = rand(0, count($seed) - 1);
            $temp = $seed[$rand];
            $str[] = $temp;
            unset($seed[$rand]);
            $seed = array_values($seed);
        }
        arsort($str);
        $str = implode('', $str);
        return $str;
    }


    /**
     * 数组键值批量替换
     * @param $data array 一维或者二维数组
     * @param $replaceKeys array 需要替换的键值对['原来键'=>‘替换键’]
     * @return array
     * @throws Exception
     * @author     zhy    find404@foxmail.coms
     * @createTime 2020年6月26日 10:57:43
     */
    public static function replaceArrayKeys($data, $replaceKeys)
    {
        $result = [];
        if (count($data) == count($data, 1)) {
            foreach ($data as $key => $val) {
                if (isset($replaceKeys[$key])) {
                    $result[$replaceKeys[$key]] = $val;
                }
            }
        } else {
            foreach ($data as $key => $val) {
                foreach ($val as $ke => $va) {
                    if (isset($replaceKeys[$ke])) {
                        if (is_array($replaceKeys[$ke])) {
                            foreach ($replaceKeys[$ke] as $k => $v) {
                                if (isset($data[$key][$v])) {
                                    $result[$key][$ke][$v] = $data[$key][$v];
                                }
                            }
                        } else {
                            $result[$key][$replaceKeys[$ke]] = $va;
                        }
                    }
                }
            }
        }
        return $result;
    }

    /**
     * 按$fieldsLimits规则清理需要操作表的数据
     * @param $data array 一维或者二维数组
     * @param $fieldsLimits array ['数据库键'=>[
     *  'type'=>'int',
     *  'length'=>'78',
     *  'default'=>'78',
     * ]]
     * @param $time int 时间戳
     * @param $timeType int 时间格式
     * @return array
     * @throws Exception
     */
    public static function clearFieldsLimits(array $data, array $fieldsLimits, int $time = 0, int $timeType = 0): array
    {
        if (empty($data)) {
            return $data;
        }
        if (empty($fieldsLimits)) {
            return $data;
        }

        if (empty($time)) {
            $time = time();
        }

        switch ($timeType) {
            case 0:
                $date = date('Y-m-d', $time);
                $dateTime = date('Y-m-d H:i:s', $time);
                break;
        }

        foreach ($data as $key => $val) {
            foreach ($val as $ke => $va) {
                //model中的键值，和现在的键值，没有对上。
                if (!isset($fieldsLimits[$ke])) {
                    unset($data[$key][$ke]);
                    continue;
                }

                if (isset($fieldsLimits[$ke])) {
                    switch ($fieldsLimits[$ke]['type']) {
                        case 'int':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : 0;
                            //枚举
                            if (isset($fieldsLimits[$ke]['enum'])) {
                                if (in_array($va, $fieldsLimits[$ke]['enum'])) {
                                    if (intval($va) <= (int)str_repeat('9', 10)) {
                                        $data[$key][$ke] = intval($va);
                                    }
                                }
                            } else {
                                if (intval($va) <= (int)str_repeat('9', 10)) {
                                    $data[$key][$ke] = intval($va);
                                }
                            }
                            break;
                        case 'bigint':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : 0;
                            //枚举
                            if (isset($fieldsLimits[$ke]['enum'])) {
                                if (in_array($va, $fieldsLimits[$ke]['enum'])) {
                                    if ($va <= str_repeat('9', 19)) {
                                        $data[$key][$ke] = $va;
                                    }
                                }
                            } else {
                                if ($va <= str_repeat('9', 19)) {
                                    $data[$key][$ke] = $va;
                                }
                            }
                            break;
                        case 'varchar':
                            //必填长度限制，不填就是300。
                            if (!isset($fieldsLimits[$ke]['length'])) {
                                $fieldsLimits[$ke]['length'] = 300;
                            }

                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : '';
                            //为空不需要在循环
                            if (empty($va)) {
                                break;
                            }
                            //枚举
                            if (isset($fieldsLimits[$ke]['enum'])) {
                                if (in_array($va, $fieldsLimits[$ke]['enum'])) {
                                    if (mb_strlen($va, 'UTF-8') <= $fieldsLimits[$ke]['length']) {
                                        $data[$key][$ke] = addslashes($va);
                                    }
                                }
                            } else {
                                if (mb_strlen($va, 'UTF-8') <= $fieldsLimits[$ke]['length']) {
                                    $data[$key][$ke] = addslashes($va);
                                }
                            }
                            break;
                        case 'tinyint':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : 0;
                            //枚举
                            if (isset($fieldsLimits[$ke]['enum'])) {
                                if (in_array($va, $fieldsLimits[$ke]['enum'])) {
                                    if (intval($va) <= 127) {
                                        $data[$key][$ke] = intval($va);
                                    }
                                }
                            } else {
                                if (intval($va) <= 127) {
                                    $data[$key][$ke] = intval($va);
                                }
                            }
                            break;
                        case 'date':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : $date;
                            //为空不需要在循环
                            if (empty($va)) {
                                break;
                            }
                            if (strtotime($va)) {
                                if (date('Y-m-d', strtotime($va)) == $va) {
                                    $data[$key][$ke] = $va;
                                }
                            }
                            break;
                        case 'datetime':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : $dateTime;
                            //为空不需要在循环
                            if (empty($va)) {
                                break;
                            }
                            if (strtotime($va)) {
                                if (date('Y-m-d H:i:s', strtotime($va)) == $va) {
                                    $data[$key][$ke] = $va;
                                }
                            }
                            break;
                        case 'tinytext':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : '';
                            if (is_array($va)) {
                                $va = json_encode($va);
                            }
                            if (mb_strlen($va, 'UTF-8') <= 255) {
                                $data[$key][$ke] = addslashes($va);
                            }
                            break;
                        case 'text':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : '';
                            if (is_array($va)) {
                                $va = json_encode($va);
                            }
                            if (mb_strlen($va, 'UTF-8') <= 65535) {
                                $data[$key][$ke] = addslashes($va);
                            }
                            break;
                        case 'mediumtext':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : '';
                            if (is_array($va)) {
                                $va = json_encode($va);
                            }
                            if (mb_strlen($va, 'UTF-8') <= 16777215) {
                                $data[$key][$ke] = addslashes($va);
                            }
                            break;
                        case 'longtext':
                            $data[$key][$ke] = isset($fieldsLimits[$ke]['default']) ? $fieldsLimits[$ke]['default'] : '';
                            if (is_array($va)) {
                                $va = json_encode($va);
                            }
                            if (mb_strlen($va, 'UTF-8') <= 4294967295) {
                                $data[$key][$ke] = addslashes($va);
                            }
                            break;
                        case 'decimal':
                            //必填长度限制，不填就是4。
                            if (!isset($fieldsLimits[$ke]['length'])) {
                                $fieldsLimits[$ke]['length'] = 4;
                            }

                            if (strpos($fieldsLimits[$ke]['length'], ',')) {
                                $beforeLength = intval($fieldsLimits[$ke]['length']);
                                //小数点前面越过了位，置空
                                if ($va > (int)str_repeat('9', $beforeLength)) {
                                    $va = 0;
                                }
                                $afterLength = ltrim(strstr($fieldsLimits[$ke]['length'], ','), ',');
                            } else {
                                $afterLength = $fieldsLimits[$ke]['length'];
                            }
                            $data[$key][$ke] = sprintf('%.0' . $afterLength . 'f', $va);
                            break;
                    }
                }
            }
        }
        return $data;
    }


    /**
     * @param string $class __METHOD__
     * @param string $customKey 自定义的key
     * @return string
     * @author:xuanqi
     * @notes:生成redis的前缀
     */
    public static function redisKey(string $class, string $customKey)
    {
        //先根据命名空间生成key的前缀
        $tmp = explode('::', $class);
        $keyArr = [$tmp[1]];
        $tmp = explode('\\', $tmp[0]);
        $keyArr = array_merge($tmp, $keyArr);
        $redisKey = '';
        foreach ($keyArr as $k => $v) {
            $k == 0 && $v = strtoupper(substr($v, 0, 1));
            $redisKey .= $v . ':';
        }
        //再拼上自定义的key
        $redisKey .= $customKey;

        return $redisKey;
    }

    /**
     * 过滤数组中元素
     * @param array $arr
     * @param string $fields
     * @return array
     */
    public static function filterArrayData(array $arr, string $fields): array
    {
        $res = [];
        if (!empty($arr) && !empty($fields) && is_string($fields)) {
            $fields = explode(",", $fields);
            foreach ($fields as $field) {
                //&& $arr[$field] !== ""
                if (isset($arr[$field])) {
                    $res[$field] = $arr[$field];
                }
                if($field == 'vat_percent' && empty($arr[$field])) {
                    $res[$field] = null;
                }
            }
        }
        return $res;
    }

    /**
     * 日志
     * @param string|array $content 日志内容
     * @param string $logName 日志文件名
     * @param integer $is_output 是否允许输出
     */
    public static function log($content, $logName = '', $is_allow_console = 0)
    {
        $logSuffix = date("Ymd");
        if ($is_allow_console) {
            if (is_string($content)) {
                Logger::getInstance()->info($content);
            } else {
                Logger::getInstance()->info(print_r($content));
            }
        }
        if (is_string($content)) {
            Logger::getInstance()->log(date('Y-m-d H:i:s') . '----' . $content . '----', -1, $logName . $logSuffix);
        } else {
            Logger::getInstance()->log(date('Y-m-d H:i:s') . '----' . print_r($content) . '----', -1, $logName . $logSuffix);
        }

    }

    /**
     * @desc    获取接口参数
     * @param        $obj
     * @param string $paramName
     *
     * @return mixed|string
     * @example
     */
    public static function getHttpParams($obj, $paramName = '')
    {
        $methodName = $obj->getMethod();

        $returnData = [];
        if ($methodName == 'GET') {
            if ($paramName) {
                $returnData = $obj->getRequestParam($paramName);
                $returnData = $returnData ? $returnData : json_decode($obj->getBody()->__toString(), true)[$paramName];
            }
        } else {
            if ($paramName) {
                $returnData = json_decode($obj->getBody()->__toString(), true)[$paramName] ?? '';
                $returnData = $returnData ? $returnData : $obj->getRequestParam($paramName);
            }
        }

        if(!$paramName){
            $returnData = $obj->getRequestParam();
            $jsonData = json_decode($obj->getBody()->__toString(), true);
            if(!empty($jsonData)){
                $returnData = array_merge($returnData, $jsonData);
            }
        }

        return $returnData;
    }

    /**
     * 返回随机字符
     * a-z：97-122，A-Z：65-90，0-9：48-57。
     * @param int $num
     * @param string $str
     */
    public static function getRandStr($num = 1){

        for ($i = 1; $i <= $num; $i++) {
            $str = chr(mt_rand(65, 90));
        }
        return $str;
    }

    /**
     * @return array|bool|mixed|string|null
     * @author xuanqi
     * 生成视频的oss地址
     */
    public static function getOssVideoPath()
    {
        return '/resource/video/' . date("Ymd") . '/' . date("YmdHis") . self::getRandomCode(6, 'letter');
    }

    public static function getVideoMaxSize()
    {
        return \config('app.videoMaxSize') ?: 209715200;
    }

    /**
     * 搜索key
     * @param $dataArray
     * @param $key_to_search
     * @return array
     */
    public static function searchArray($dataArray, $keSearch) {

        $ret = [];
        foreach ($dataArray as $key => $value) {

            if (is_array($value) && count($value) > 0) {
                $_item = self::searchArray($value,$keSearch);
                if($_item) {
                    return $_item;
                }
            } else {
                if(strpos($key,$keSearch) !== false) {
                    $newKey = explode('=',$key);
                    $ret[$key] = $value;
                    $ret['unitCount'] = $value;
                    $ret['countUnit'] = $newKey[1];
                    break;
                }
            }
        }

        return $ret;
    }
}