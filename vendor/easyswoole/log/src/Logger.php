<?php


namespace EasySwoole\Log;

use EasySwoole\Component\Context\ContextManager;

class Logger implements LoggerInterface
{
    private $logDir;

    function __construct(string $logDir = null)
    {
        if(empty($logDir)){
            $logDir = getcwd();
        }
        $this->logDir = $logDir;
    }

    function log(?string $msg,int $logLevel = self::LOG_LEVEL_DEBUG,string $category = 'debug')
    {
//        $prefix = date('Ym');
//        $date = date('Y-m-d H:i:s');
//        $levelStr = $this->levelMap($logLevel);
//        $filePath = $this->logDir."/log_{$prefix}.log";
//        $str = "[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
//        file_put_contents($filePath,"{$str}",FILE_APPEND|LOCK_EX);
//        return $str;
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        $filePath = $this->logDir . '/' .  $category . '.log';
        $logUID = $this->getLogId();
        $path = dirname($filePath);
        !is_dir($path) && mkdir($path, 0755, true);
        $str = "[{$date}][{$logUID}][{$levelStr}]:[{$msg}]\n";
        file_put_contents($filePath, "{$str}", FILE_APPEND | LOCK_EX);
        return $str;
    }

    function console(?string $msg,int $logLevel = self::LOG_LEVEL_DEBUG,string $category = 'debug')
    {
        $date = date('Y-m-d H:i:s');
        $levelStr = $this->levelMap($logLevel);
        echo "[{$date}][{$category}][{$levelStr}]:[{$msg}]\n";
    }

    private function levelMap(int $level)
    {
        switch ($level)
        {
            case self::LOG_LEVEL_DEBUG:
                return 'debug';
            case self::LOG_LEVEL_INFO:
               return 'info';
            case self::LOG_LEVEL_NOTICE:
                return 'notice';
            case self::LOG_LEVEL_WARNING:
                return 'warning';
            case self::LOG_LEVEL_ERROR:
                return 'error';
            default:
                return 'unknown';
        }
    }

    public function getLogId()
    {
        try{
            $contextKey = 'coupang_project_log';
            $logId = ContextManager::getInstance()->get($contextKey);
            if (!$logId){
                $logId = $this->createLogId();
                ContextManager::getInstance()->set($contextKey, $logId);
            }
        }catch (\Throwable $e){
            $logId = '';
        }
        return $logId;
    }

    private function createLogId() {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',

            // 32 bits for "time_low"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),

            // 16 bits for "time_mid"
            mt_rand(0, 0xffff),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand(0, 0x0fff) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand(0, 0x3fff) | 0x8000,

            // 48 bits for "node"
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}