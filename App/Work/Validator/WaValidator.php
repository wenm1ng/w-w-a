<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-05-07 11:48
 */
namespace App\Work\Validator;
use Common\Common;
use EasySwoole\Validate\Validate as systemValidate;

class WaValidator extends systemValidate
{
    public function checkVerision(){
        $this->addColumn('version')->notEmpty('版本号不能为空');
    }

    public function checkGetWaList(array $params){
        if(empty($params['id'])){
            $this->checkVerision();
            $this->addColumn('page')->notEmpty('页数不能为空')->func(function($params){
                if(empty($params['tt_id']) || empty($params['oc'])){
                    return 'id和职业必选一项';
                }
                return true;
            });
        }
        $this->addColumn('page')->notEmpty('页数不能为空');
    }
}