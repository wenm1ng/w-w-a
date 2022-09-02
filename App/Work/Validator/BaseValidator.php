<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-09-02 17:55
 */
namespace App\Work\Validator;

use Common\CodeKey;
use App\Exceptions\CommonException;
use EasySwoole\Validate\Validate as systemValidate;

class BaseValidator extends systemValidate
{
    /**
     * @desc       敏感词校验
     * @author     文明<736038880@qq.com>
     * @date       2022-09-02 18:53
     * @param string $description
     *
     * @return bool|string
     */
    public function checkText(string $description){
        if(!empty(searchSensitiveWords($description))){
            CommonException::msgException('你填写的信息里面包含敏感词汇，请修改', CodeKey::WORDS_SENSITIVE);
        }
    }
}