<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-05-09 10:26
 */
namespace Talent\Models;

use App\Common\EasyModel;

class WowTalentModelNew extends EasyModel
{
    protected $connection = 'service';

    protected $table = 'wow_talent';

    protected $primaryKey = 'wt_id';

    protected $keyType = 'int';
}