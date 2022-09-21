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

    /**
     * @desc       记录抽奖日志
     * @author     文明<736038880@qq.com>
     * @date       2022-09-21 10:51
     * @param array $params
     *
     * @return bool
     */
    private function addLotteryLog(array $params){
        $mountIds = array_filter(array_column($params, 'id'));
        if(empty($mountIds)){
            return false;
        }

        try{
            DB::beginTransaction();
            $userId = Common::getUserId();
            $list = WowMountLogModel::query()->whereIn('mount_id', $mountIds)->where('user_id', $userId)->select(DB::raw('id,mount_id,times,suc_times_record,suc_times'))->get()->toArray();
            $list = array_column($list, null, 'mount_id');
            $insertData = [];
            foreach ($params as $val) {
                $list[$val['id']]['times'] = (!empty($list[$val['id']]['times']) ? $list[$val['id']]['times'] : 0) + 1;
                $list[$val['id']]['suc_times'] = (!empty($list[$val['id']]['suc_times']) ? $list[$val['id']]['suc_times'] : 0) + ($val['is_bingo'] ? 1 : 0);
                if(!isset($list[$val['id']]['suc_times_record'])){
                    //新增
                    $insertData[$val['id']] = [
                        'user_id' => $userId,
                        'mount_id' => $val['id'],
                        'times' => 1,
                        'suc_times' => $val['is_bingo'] ? 1 : 0,
                        'suc_times_record' => $val['is_bingo'] ? ',1' : '',
                    ];
                    $list[$val['id']]['suc_times_record'] = $insertData[$val['id']]['suc_times_record'];
                    continue;
                }
                //此次抽奖出现多次 DB没有记录的坐骑
                if(isset($insertData[$val['id']])){
                    $insertData[$val['id']]['times'] = $list[$val['id']]['times'];
                    $insertData[$val['id']]['suc_times'] = $list[$val['id']]['suc_times'];
                    if($val['is_bingo']){
                        $insertData[$val['id']]['suc_times_record'] .= ','. $list[$val['id']]['times'];
                    }
                    continue;
                }
                //编辑
                $list[$val['id']]['suc_times_record'] .= ','.$list[$val['id']]['times'];
                $updateData = [
                    'times' => DB::raw('times + 1'),
                ];
                if($val['is_bingo']){
                    $updateData['suc_times_record'] = DB::raw('concat(suc_times_record, ",", '.$list[$val['id']]['times'].')');
                    $updateData['suc_times'] = DB::raw('suc_times + 1');
                }
                WowMountLogModel::query()->where('id', $list[$val['id']]['id'])->update($updateData);
            }
            if(!empty($insertData)){
                $insertData = array_values($insertData);
                WowMountLogModel::query()->insert($insertData);
            }
            DB::commit();
        }catch (\Exception $e){
            DB::rollBack();
            Common::log($e->getMessage().'_'.$e->getFile().'_'.$e->getLine(), 'sqlTransaction');
            CommonException::msgException('系统错误');
        }
        return true;
    }

    /**
     * @desc       获取坐骑抽奖记录列表
     * @author     文明<736038880@qq.com>
     * @date       2022-09-21 16:06
     * @param array $params
     *
     * @return array
     */
    public function getLotteryLogList(array $params){
        $this->validator->checkPage();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        if (!empty($params['order']) && !empty($params['sort'])) {
            $where['order'] = [$params['order'] => $params['sort'], 'id' => 'desc'];
        } else {
            $where['order'] = ['times' => 'desc', 'id' => 'desc'];
        }
        $fields = 'id,mount_id,times,suc_times_record,suc_times';
        $list = WowMountLogModel::baseQuery($where)
            ->with([
                'mount_info'=>function($query){
                    $query->select('id','name');
                }
            ])
            ->whereHas('mount_info',function($query)use ($params){
                if (!empty($params['name'])){
                    $query->where('name','like','%' . $params['name'] . '%');
                }
            })
            ->select(Db::raw($fields))
            ->limit($params['pageSize'])->offset($params['pageSize'] * ($params['page'] - 1))
            ->get()->toArray();

        return ['list' => $list, 'page' => $params['page']];
    }
}