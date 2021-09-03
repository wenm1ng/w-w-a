<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-20 23:48
 */
namespace User\Service;

/**
 * UserService不要去掉会报错
 */

use Common\Common;
use Common\CodeKey;
use User\Validator\UserValidate;
use User\Service\LoginService;
use User\Models\WowUserModel;

class UserService{

    protected $token = '';
    protected $url = 'http://mini-test.eccang.com:18080';
    protected $systemType = 'SSO_SYS_USER';
    protected $userModel;

    public function __construct($token = "")
    {
        $this->validate = new UserValidate();
        $this->userModel = new WowUserModel();
    }

    private function getSessionKey($code){
        $appId = \Common\Config::APPID;
        $secret = \Common\Config::SECRET;
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid={$appId}&secret={$secret}&js_code={$code}&grant_type=authorization_code";
        $return = httpClientCurl($url);
        return $return;
    }

    public function getUserInfo($userId){
        $fields = 'nickName,gender,language,city,province,country,avatarUrl';
        $userInfo = $this->userModel->get(['user_id' => $userId], $fields);
        if(empty($userInfo)){
            throw new \Exception('用户信息不存在');
        }
        return $userInfo;
    }

    public function saveUserInfo($params){
        $sessionInfo = $this->getSessionKey($params['code']);
        dump($sessionInfo);
//        if(empty($sessionInfo['session_key']) || empty($sessionInfo['openid'])){
//            throw new \Exception('授权失败', CodeKey::SESSION_FAIL);
//        }
//        $params['sessionKey'] = $sessionInfo['session_key'];
//
//        $wxBizDataCrypt = new WxBizDataCrypt(\App\HttpController\Config::APPID, $params['sessionKey']);
//        $errCode = $wxBizDataCrypt->decryptData($params['encryptedData'], $params['iv'], $data );
//        dump($errCode);
        $data = $params;
//        $this->userModel::create()->connection('default')
        //保存用户信息
        $userInfo = $this->userModel->get(['openId' => $sessionInfo['openid']], 'user_id');

        $dbData = [
            'nickName' => $data['userInfo']['nickName'],
            'gender' => $data['userInfo']['gender'],
            'language' => $data['userInfo']['language'],
            'city' => $data['userInfo']['city'],
            'province' => $data['userInfo']['province'],
            'country' => $data['userInfo']['country'],
            'avatarUrl' => $data['userInfo']['avatarUrl'],
            'openId' => $sessionInfo['openid']
        ];
        if(empty($userInfo)){
            //新增用户
            $userInfo['user_id'] = $this->userModel->create($dbData);
        }else{
            //修改用户
            $dbData['update_at'] = date('Y-m-d H:i:s');
            $this->userModel->update(['user_id' => $userInfo['user_id']],$dbData);
        }

        $loginService = new LoginService();
        $return = $loginService->setToken(['user_id' => $userInfo['user_id']]);
        $data['userInfo']['id'] = $userInfo['user_id'];
        $data['userInfo']['token'] = $return['Authorization'];
        return $data['userInfo'];
    }

}
