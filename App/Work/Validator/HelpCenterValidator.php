<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-28 13:56
 */
namespace App\Work\Validator;
use Common\Common;
use EasySwoole\Validate\Validate as systemValidate;

class HelpCenterValidator extends systemValidate
{
    public function checkRoom()
    {
        $this->addColumn('room_id')->notEmpty('房间号不能为空');
        $this->checkPage();
    }

    public function checkPage(){
        $this->addColumn('page')->notEmpty('页数不能为空');
        $this->addColumn('pageSize')->notEmpty('每页数量不能为空');
    }

    public function checkAddHelp(){
        $this->addColumn('title')->notEmpty('求助标题不能为空');
        $this->addColumn('version')->notEmpty('版本不能为空');
        $this->addColumn('help_type')->notEmpty('求助类型不能为空');
        $this->addColumn('description')->notEmpty('求助详细描述不能为空');
        $this->addColumn('is_pay')->required('是否有偿求助不能为空');
    }

    public function checkId(){
        $this->addColumn('id')->notEmpty('id不能为空');
    }

    public function checkAddAnswer(){
        $this->addColumn('help_id')->notEmpty('求助id不能为空');
        $this->addColumn('description')->notEmpty('描述不能为空');
    }

    public function checkUpdateAnswer(){
        $this->checkId();
        $this->addColumn('help_id')->notEmpty('求助id不能为空');
        $this->addColumn('description')->notEmpty('描述不能为空');
    }
}