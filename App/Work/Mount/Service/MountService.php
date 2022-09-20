<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-20 14:06
 */
namespace App\Work\Mount\Service;

use App\Work\Validator\MountValidator;
use App\Exceptions\CommonException;
use User\Service\CommonService;
use App\Work\Mount\Models\WowMountModel;
use App\Work\Mount\Models\WowMountLogModel;
use Common\Common;
use App\Utility\Database\Db;
use App\Work\Common\Lottery;
use App\Work\Config;

class MountService
{
    protected $validator;

    public function __construct()
    {
        $this->validator = new MountValidator();
    }

    /**
     * @desc       获取坐骑列表
     * @author     文明<736038880@qq.com>
     * @date       2022-09-20 14:33
     * @param array $params
     *
     * @return array
     */
    public function getList(array $params)
    {
//        $this->validator->checkPage();
//        if (!$this->validator->validate($params)) {
//            CommonException::msgException($this->validator->getError()->__toString());
//        }
        $where = [
            'where' => [
                ['status', '=', 1]
            ]
        ];
        if (!empty($params['name'])) {
            $where['where'][] = ['name', 'like', "%{$params['name']}%"];
        }
        if (!empty($params['order']) && !empty($params['sort'])) {
            $where['order'] = [$params['order'] => $params['sort'], 'id' => 'desc'];
        } else {
            $where['order'] = ['rate' => 'asc', 'id' => 'desc'];
        }
        $fields = 'id,name,image_url';
        $list = WowMountModel::baseQuery($where)->select(Db::raw($fields))->get()->toArray();

        return ['list' => $list];
    }

    /**
     * @desc       进行坐骑抽奖
     * @author     文明<736038880@qq.com>
     * @date       2022-09-20 16:51
     * @param array $params
     *
     * @return array
     */
    public function doLottery(array $params){
        $this->validator->checkLottery($params);
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $where = [
            'where' => [
                ['status', '=', 1]
            ]
        ];
        if(empty($params['is_all'])){
            $where['whereIn'][] = ['id', $params['id']];
            $list = redis()->hMGet(Config::REDIS_KEY_MOUNT_LIST, $params['id']);
        }else{
            $list = redis()->hGetAll(Config::REDIS_KEY_MOUNT_LIST);
        }

        if(empty($list)){
            $fields = 'id,name,description,image_url,rate';
            $list = WowMountModel::baseQuery($where)->select(DB::raw($fields))->get()->toArray();
            $jsonList = [];
            foreach ($list as $val) {
                $jsonList[$val['id']] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
            redis()->hMSet(Config::REDIS_KEY_MOUNT_LIST, $jsonList);
        }else{
            foreach ($list as $key => $val) {
                if(empty($val)){
                    unset($list[$key]);
                    continue;
                }
                $list[$key] = json_decode($list[$key], true);
            }
            $list = array_values($list);
        }

        $count = count($list);
        $return = [];
        if($params['type'] == 1){
            //单抽
            $randNum = mt_rand(1, $count);
            $return[] = Lottery::doDraw($list[$randNum-1]['name'], $list[$randNum-1]['rate'], $list[$randNum-1]['image_url'], $list[$randNum-1]['id']);
        }else{
            //10连抽
            for ($i = 0; $i < 10; $i++) {
                $randNum = mt_rand(1, $count);
                $return[] = Lottery::doDraw($list[$randNum-1]['name'], $list[$randNum-1]['rate'], $list[$randNum-1]['image_url'], $list[$randNum-1]['id']);
            }
        }
        $this->addLotteryLog($return);
        return $return;
    }

    private function addLotteryLog(array $params){
        $mountIds = array_filter(array_column($params, 'id'));
        if(empty($mountIds)){
            return;
        }
        $userId = Common::getUserId();
        $list = WowMountLogModel::query()->whereIn('mount_id', $mountIds)->where('user_id', $userId)->select(DB::raw('mount_id,times,suc_times'))->get()->toArray();
        $list = array_column($list, null, 'mount_id');
        $insertData = [];
        foreach ($params as $val) {
            $list[$val['id']]['times']++;
            if(empty($val['id'])){
                continue;
            }
            if(!isset($list[$val['id']])){
                //新增
                $insertData[$val['id']] = [
                    'user_id' => $userId,
                    'mount_id' => $val['id'],
                    'times' => 1,
                    'suc_times' => '1'
                ];
                $list[$val['id']] = [
                    'times' => 1,
                    'suc_times' => '1'
                ];
                continue;
            }
            //编辑
            if(isset($insertData[$val['id']])){
                $insertData[$val['id']]['times']++;
                $insertData[$val['id']]['suc_times'] .= ','. $insertData[$val['id']]['times'];
                continue;
            }

            $updateData = [
                'times' => DB::raw('times + 1'),
                'suc_times' => DB::raw('concat(suc_times, ",", '.$list[$val['id']].')')
            ];
            WowMountLogModel::query()->where('id', $val['id'])->update($updateData);
        }
    }
}