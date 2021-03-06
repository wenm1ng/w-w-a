<?php

namespace App\Utility\Pool;

use EasySwoole\Pool\ObjectInterface;
use EasySwoole\Mysqli\Client;
use Swoole\Coroutine\MySQL;
use EasySwoole\EasySwoole\Logger;
use Swoole\Coroutine;
use EasySwoole\Mysqli\Exception\Exception;

class MysqlObject extends Client implements ObjectInterface
{
    private $poolKey;

    public function setPoolKey($poolKey)
    {
        $this->poolKey = $poolKey;
    }

    public function getPoolKey()
    {
        return $this->poolKey;
    }

    public function execBuilder(float $timeout = null)
    {
        $tryTimes = 3;
        $tryNumber = 0;
        $url = 'https://open.feishu.cn/open-apis/bot/v2/hook/857e06cb-77f4-4658-8cc1-ca4000aeb8eb';
        $cid = Coroutine::getCid();
        $sql = $this->queryBuilder()->getLastQuery() ?: '';

        $this->lastQueryBuilder = $this->queryBuilder;
        $start = microtime(true);
        if($timeout === null){
            $timeout = property_exists($this->config,'exec_timeout') ? $this->config->exec_timeout : 600;
        }

        $startTransactionCount = intval(\App\Utility\Context::getContext('startTransactionCount'));

        try{
            MYSQL_EXEC_Builder_DO:
            $tryNumber += 1;

            $this->connect();
            $stmt = $this->mysqlClient()->prepare($this->queryBuilder()->getLastPrepareQuery(),$timeout);
            $ret = null;
            if($stmt){
                $ret = $stmt->execute($this->queryBuilder()->getLastBindParams(),$timeout);
                dump($sql);
                Logger::getInstance()->log($sql . " - cid({$cid})", Logger::LOG_LEVEL_INFO, 'sql_debug');
            }else{
                $ret = false;
            }
            if($this->onQuery){
                call_user_func($this->onQuery,$ret,$this,$start);
            }
            if($ret === false && $this->mysqlClient()->errno){
                throw new Exception($this->mysqlClient()->error);
            }
            return $ret;
        }catch (\Throwable $et){

            $isDev = config('app.environment');
            $container = config('coupang_crontab');
            $errno = $this->mysqlClient()->errno;

            if (($errno == 2006 || $errno == 2013 || $errno == 2002) && $startTransactionCount == 0) {
                if($tryNumber>=$tryTimes){
                    $dbHost = $this->config->getHost();
                    feishuWarning($isDev."???????????????".json_encode($container)." ??????????????????????????? - {$dbHost} - {$sql}) - cid({$cid}) -:". $et->getMessage(),$url);
                    Logger::getInstance()->log("??????????????????????????? - {$dbHost} - {$sql}) - cid({$cid}) - " . $et->getMessage(), Logger::LOG_LEVEL_WARNING, 'swoole');
                    throw new \Exception("???????????????????????????-" . $et->getMessage(), $et->getCode());
                } else {
                    $this->mysqlClient->connected = false;
                    goto MYSQL_EXEC_Builder_DO;
                }
            } else {
                $dbHost = $this->config->getHost();
                feishuWarning($isDev."???????????????".json_encode($container)."??????????????? - {$dbHost} - {$sql}) - cid({$cid}) -:". $et->getMessage(),$url);
                Logger::getInstance()->log("??????????????? - {$dbHost} - {$sql}) - cid({$cid}) - " . $et->getMessage(), Logger::LOG_LEVEL_WARNING, 'swoole');
                throw new \Exception("??????????????? - {$dbHost} - {$sql}) - cid({$cid}) - " . $et->getMessage(), $et->getCode());
            }
        }finally{
            $this->reset();
        }
    }

    public function rawQuery(string $query,float $timeout = null)
    {
        $tryTimes = 3;
        $tryNumber = 0;

        $cid = Coroutine::getCid();
        $sql = $this->queryBuilder()->getLastQuery() ?: '';

        if($timeout === null){
            $timeout = property_exists($this->config,'exec_timeout') ? $this->config->exec_timeout : 600;
        }

        $startTransactionCount = intval(\App\Utility\Context::getContext('startTransactionCount'));

        try {
            MYSQL_RAW_QUERY_DO:
            $tryNumber += 1;

            $res = parent::rawQuery($query, $timeout);
            return $res;
        } catch (\Throwable $e) {
            $errno = $this->mysqlClient()->errno;
    
            if (($errno == 2006 || $errno == 2013 || $errno == 2002) && $startTransactionCount == 0) {
                if($tryNumber>=$tryTimes){
                    $dbHost = $this->config->getHost();
                    Logger::getInstance()->log("??????????????????????????? - {$dbHost} - {$sql}) - cid({$cid}) - " . $e->getMessage(), Logger::LOG_LEVEL_WARNING, 'swoole');
                    throw new \Exception("???????????????????????????" . $e->getMessage() , $e->getCode());
                } else {
                    $this->mysqlClient->connected = false;
                    $this->connect();
                    goto MYSQL_RAW_QUERY_DO;
                }
            } else {
                $dbHost = $this->config->getHost();
                Logger::getInstance()->log("??????????????? - {$dbHost} - {$sql}) - cid({$cid}) - " . $e->getMessage(), Logger::LOG_LEVEL_WARNING, 'swoole');
                throw new \Exception("???????????????" . $e->getMessage(), $e->getCode());
            }
        }
    }

    public function fetchRow($sql)
    {
        $data = $this->rawQuery($sql) ?: [];
        return $data[0] ?? [];
    }

    public function fetchAll($sql)
    {
        $data = $this->rawQuery($sql) ?: [];
        return $data;
    }

    public function fetchOne($sql)
    {
        $data = $this->rawQuery($sql) ?: [];
        if (isset($data[0])) {
            return current($data[0]);
        }

        return false;
    }

    public function gc()
    {
        // TODO: Implement gc() method.
        // ?????????????????????
        $this->reset();
        // ?????????????????????
        $this->close();
    }

    public function objectRestore()
    {
        // TODO: Implement objectRestore() method.
        // ?????????????????????
        $this->reset();
    }

    public function beforeUse(): bool
    {
        // TODO: Implement beforeUse() method.
        //???????????????,?????????true?????????????????????????????????false?????????????????????????????????
        //????????????????????????,?????????????????????????????????,??????????????????true
        // var_dump($this->mysqlClient());
        return true; // $this->mysqlClient()->connected;
    }
}
