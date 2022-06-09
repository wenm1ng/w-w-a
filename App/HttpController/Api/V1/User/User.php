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

    /**
     * @desc       　获取用户收藏列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function getFavoritesList(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new UserService())->getFavoritesList($params);
        });
    }

    /**
     * @desc       　用户添加收藏
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function addFavorites(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new UserService())->addFavorites($params);
        });
    }

    /**
     * @desc       　用户取消收藏
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function cancelFavorites(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new UserService())->cancelFavorites($params);
        });
    }

    /**
     * @desc       　点赞
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function addLikes(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new UserService())->addLikes($params);
        });
    }

    /**
     * @desc       　取消点赞
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function cancelLikes(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new UserService())->cancelLikes($params);
        });
    }

    /**
     * @desc       　点赞和取消点赞
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @return bool
     */
    public function toLikes(){
        return $this->apiResponse(function (){
            $params = $this->getRequestJsonData();
            return (new UserService())->toLikes($params);
        });
    }
}