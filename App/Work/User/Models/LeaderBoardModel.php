<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-10 17:15
 */
namespace User\Models;

use App\Common\EasyModel;
use App\Work\config;

class LeaderBoardModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_leader_board';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public static function incrementScore($userId, int $type, int $isDel = 0){
         $year = date('Y');
         $week = date('W') + config::WEEK_OFFSET;
         $model = self::query();
         $id = $model
             ->where('year', $year)
             ->where('week', $week)
             ->where('user_id', $userId)
             ->value('id');

         if(empty($id)){
             //没有记录，添加
             $insertData = [
                 'year' => $year,
                 'week' => $week,
                 'user_id' => $userId,
                 'score' => config::$scoreLink[$type],
             ];
             $insertData[config::$typeColumnLink[$type]] = 1;
             $model->insert($insertData);
         }else{
             //increment
             $model->increment(config::$typeColumnLink[$type]);
         }
    }
}