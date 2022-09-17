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
use App\Work\Mount\Models\WowMountModel;

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

    public function collectMount(){
        $url = EASYSWOOLE_ROOT . '/amount.html';
//        $url = 'https://www.warcraftmounts.com/loot.php';
        $html = file_get_contents($url);

        preg_match_all("/<img class='thumbimage' src='(.*?)' alt/", $html, $matchImg);
        preg_match_all("/<span class='mountname'>(.*?)<\/span>/", $html, $matchMount);
        preg_match_all("/<span class='thumbinfotext'>(.*?)<\/span>/", $html, $matchThumb);

        $insertData = [];
        foreach ($matchMount[1] as $key => $val) {
            if(empty($matchImg[1][$key]) || empty($matchThumb[1][$key])){
                continue;
            }
            $description = $matchThumb[1][$key];
            $insertData[] = [
                'type' => 1,
                'origin_name' => call_user_func(function()use($val){
                    $val = preg_replace("/<img.*?>/",'', $val);
                    return $val;
                }),
                'image_url' => $matchImg[1][$key],
                'origin_description' => call_user_func(function()use($description){
                    $description = preg_replace("/<a.*?_blank'>/",'', $description);
                    $description = preg_replace("/<\/a>/",'', $description);
                    $description = preg_replace("/<img.*?>/",'', $description);
                    return $description;
                })
            ];
        }
        WowMountModel::query()->insert($insertData);
//        dump($matchImg[1][0], $matchMount[1][0], $matchThumb[1][0]);
    }
}