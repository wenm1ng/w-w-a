<?php
/**
 * @desc
 * @author     WenMing<st-m1ng@163.com>
 * @date       2024-02-01 0:19
 */
namespace App\HttpController\Gzh;

use App\HttpController\LoginController;
use App\Work\WxCallBack\Service\WxCallBackService;
use Common\Common;
use Common\CodeKey;
use App\Extend\WxGzh\WXBizMsgCrypt;

class Validate extends LoginController
{
    /**
     * @desc        获取用户信息
     * @example
     * @return bool
     */
    public function index(){
//        $result = CodeKey::result();
//        try {
//            $params = Common::getHttpParams($this->request());
//            if($this->checkSignature($params)){
//                $result[CodeKey::STATE] = CodeKey::SUCCESS;
//                $result[CodeKey::DATA] = $params['echostr'];
//            }
//        } catch (\Exception $e) {
//            $result[CodeKey::MSG] = $e->getMessage();
//        }
//        return $this->writeResultJson($result);
        $params = Common::getHttpParams($this->request());
        if($this->checkSignature($params)){
            $this->response()->write($params['echostr']);
        }else{
            $this->response()->write('error');
        }
    }

    private function checkSignature(array $params)
    {
        Common::log('wx_callback params:'.json_encode($params), 'wx_gzh_validate');

        $signature = $params["signature"];
        $timestamp = $params["timestamp"];
        $nonce = $params["nonce"];

        $token = config('app.GZH_TOKEN');
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr, SORT_STRING);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
}