<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-05 10:20
 */
namespace App\Work\WxPay\Models;

use App\Common\EasyModel;

class WowOrderLogModel extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_order_log';

    protected $primaryKey = 'id';

    protected $keyType = 'int';
}