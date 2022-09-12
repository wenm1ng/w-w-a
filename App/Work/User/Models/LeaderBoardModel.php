<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-10 17:15
 */
namespace User\Models;

use App\Common\EasyModel;

class LeaderBoardModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_leader_board';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}