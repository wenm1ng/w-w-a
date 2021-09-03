<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-21 0:18
 */
namespace User\Validator;

use EasySwoole\Validate\Validate;

class UserValidate extends Validate
{
    public function permission() {
        $this->addColumn('functionId')->notEmpty();
        $this->addColumn('menuId')->notEmpty();
        $this->addColumn('functionName')->notEmpty();
        $this->addColumn('functionStatus')->notEmpty();
    }
}