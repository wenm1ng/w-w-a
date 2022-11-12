<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-11-10 14:46
 */

namespace Damage\Models;

use App\Common\EasyModel;
use App\Utility\Database\Db;

class WowRoleAttributeModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_role_attribute';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}