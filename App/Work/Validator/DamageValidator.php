<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-11-10 14:41
 */

namespace App\Work\Validator;

use App\Exceptions\CommonException;

class DamageValidator extends BaseValidator
{
    public function checkAttribute()
    {
        $this->addColumn('version')->notEmpty('版本不能为空');
        $this->addColumn('stage_name')->notEmpty('阶段名称不能为空');
        $this->addColumn('oc')->notEmpty('职业名称不能为空');
    }
}