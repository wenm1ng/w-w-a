<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-20 18:06
 */
namespace App\Work\Mount\Models;

use App\Common\EasyModel;

class WowMountLogModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_mount_log';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}