<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-10-26 18:24
 */
namespace Damage\Models;

use App\Common\EasyModel;
use App\Utility\Database\Db;

class WowSkillNewModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_skill';

    protected $primaryKey = 'ws_id';

    protected $keyType = 'int';
}