<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-20 14:20
 */namespace App\HttpController\Api\V1\Test;

use App\HttpController\LoginController;
use Common\Common;
use Common\CodeKey;
use Wa\Service\WaService;
use User\Service\UserService;
use User\Service\LeaderBoardService;
use EasySwoole\EasySwoole\Task\TaskManager;
use App\Task\WowLeaderBoardTask;
use User\Service\CommonService;

class Test extends LoginController
{
    /**
     * @desc       同步缓存
     * @author     文明<736038880@qq.com>
     * @date       2022-09-12 10:45
     * @return bool
     */
    public function aKeySyncRedis()
    {
        return $this->apiResponse(function () {
            $params = $this->getRequestJsonData();
            return (new LeaderBoardService())->aKeySyncRedis();
        });
    }

    /**
     * @desc       手动执行定时任务
     * @author     文明<736038880@qq.com>
     * @date       2022-09-14 16:43
     * @return bool
     */
    public function handLeaderBoard(){
        return $this->apiResponse(function () {
            // 定时任务的执行逻辑
            $task = TaskManager::getInstance();
            return $task->async(new WowLeaderBoardTask());
        });
    }

    public function test(){
        $url = 'http://wenming.online/public/uploads/20220713/35141348beb9ec3881d842c6b715db56.jpg';
        $openId = 'osEzx5LPUZz_Tq08nRqgBX4RPxEs';
        (new CommonService())->wxCheckImage($url, $openId);
    }
}