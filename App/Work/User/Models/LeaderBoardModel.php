<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-10 17:15
 */
namespace User\Models;

use App\Common\EasyModel;
use App\Work\config;
use App\Utility\Database\Db;

class LeaderBoardModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_leader_board';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public static function incrementScore($userId, int $type, string $dateTime, int $num = 1, int $descriptionNum = 0){
         $year = date('Y', strtotime($dateTime));
         $week = date('W', strtotime($dateTime)) + config::WEEK_OFFSET;
         $model = self::query();
         $id = $model
             ->where('year', $year)
             ->where('week', $week)
             ->where('user_id', $userId)
             ->value('id');

         $column = config::$typeColumnLink[$type];
         $value = config::$scoreLink[$type];
         $score = $value * $num;
         if(empty($id) && $num >= 1){
             //没有记录，添加
             $insertData = [
                 'year' => $year,
                 'week' => $week,
                 'user_id' => $userId,
                 'score' => $score,
                 'description_num' => $descriptionNum
             ];
             $insertData[$column] = $value;
             $model->insert($insertData);
         }else{
             //increment
             $updateData = [
                 $column => Db::raw("{$column} + {$num}"),
                 'score' => Db::raw("score + {$score}"),
                 'description_num' => Db::raw('description_num + '.$descriptionNum)
             ];
             $model->where('id', $id)->update($updateData);
         }
    }
}