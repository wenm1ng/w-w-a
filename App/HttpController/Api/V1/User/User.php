<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-17 14:39
 */
namespace App\HttpController\Api\V1\User;

use Common\Common;
use Common\CodeKey;
use App\HttpController\BaseController;
use User\Service\UserService;

class User extends BaseController
{
    /**
     * @desc        获取用户信息
     * @example
     * @return bool
     */
    public function getUserInfo(){
        $rs = CodeKey::result();
        try {
            $userId = Common::getHttpParams($this->request(),'user_id');
            $userService = new UserService();
            $result = $userService->getUserInfo($userId);
            $rs[CodeKey::STATE] = CodeKey::SUCCESS;
            $rs[CodeKey::DATA] = $result;
        } catch (\Exception $e) {
            $rs[CodeKey::MSG] = $e->getMessage();
        }

        return $this->writeResultJson($rs);
    }
}