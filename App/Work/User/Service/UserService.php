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
use User\Models\WowUserModelNew;
use User\Models\WowUserLikesModel;
use Wa\Models\WowWaContentModel;
use Wa\Service\WaService;
use App\Exceptions\CommonException;
use App\Utility\Database\Db;

class UserService{

    protected $token = '';
    protected $url = 'http://mini-test.eccang.com:18080';
    protected $systemType = 'SSO_SYS_USER';
    protected $userModel;

    public function __construct($token = "")
    {
        $this->validate = new UserValidate();
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
        $userInfo = WowUserModelNew::query()->where('user_id', $userId)->select(Db::raw($fields))->first();
        dump($userInfo);
        if(!empty($userInfo)){
            $userInfo = $userInfo->toArray();
        }
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
        $userInfo = WowUserModelNew::query()->where('openId', $sessionInfo['openid'])->select(['user_id'])->first();
        if(!empty($userInfo)){
            $userInfo = $userInfo->toArray();
        }
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
            $userInfo['user_id'] = WowUserModelNew::query()->insertGetId($dbData);
        }else{
            //修改用户
            $dbData['update_at'] = date('Y-m-d H:i:s');
            WowUserModelNew::query()->where('user_id', $userInfo['user_id'])->update($dbData);
        }

        $loginService = new LoginService();
        $return = $loginService->setToken(['user_id' => $userInfo['user_id']]);
        $data['userInfo']['id'] = $userInfo['user_id'];
        $data['userInfo']['token'] = $return['Authorization'];
        return $data['userInfo'];
    }

    /**
     * @desc       　合并用户名称
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array  $list 要合并的列表
     * @param string $originColumnName 原始用户id字段
     * @param string $targetColumnName 目标用户名称字段
     *
     * @return array
     */
    public function mergeUserName(array $list, string $originColumnName = 'user_id', string $targetColumnName = 'user_name'){
        $userIds = array_unique(array_filter(array_column($list, $originColumnName)));
        $link = [];
        if(!empty($userIds)){
            $link = WowUserModelNew::query()->whereIn('user_id', $userIds)->pluck('nickName', 'user_id');
        }
        foreach ($list as &$val) {
            $val[$targetColumnName] = $link[$val['user_id']] ?? \App\Work\Config::ADMIN_NAME;
        }
        return $list;
    }

    /**
     * @desc       　合并用户名称and头像
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $list
     *
     * @return array
     */
    public static function mergeUserNameAvatarUrl(array $list){
        $userIds = array_unique(array_filter(array_column($list, 'user_id')));
        $replyIds = array_unique(array_filter(array_column($list, 'reply_user_id')));
        $userIds = array_merge($userIds, $replyIds);
        $link = [];
        if(!empty($userIds)){
            $link = WowUserModelNew::query()->whereIn('user_id', $userIds)->get(['nickName', 'user_id', 'avatarUrl'])->toArray();
            $link = array_column($link, null, 'user_id');
        }
        foreach ($list as &$val) {
            $val['user_name'] = $link[$val['user_id']]['nickName'] ?? \App\Work\Config::ADMIN_NAME;
            $val['avatar_url'] = $link[$val['user_id']]['avatarUrl'] ?? '';
            $val['reply_user_name'] = $link[$val['reply_user_id']]['nickName'] ?? '';
            $val['reply_avatar_url'] = $link[$val['reply_user_id']]['avatarUrl'] ?? '';
        }
        return $list;
    }
    /**
     * @desc       　获取收藏的内容列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return array|mixed
     */
    public function getFavoritesList(array $params){
        $userId = Common::getUserId();
        $linkIds = WowUserLikesModel::query()->where('user_id', $userId)->pluck('link_id');
        if($linkIds === null){
            return [];
        }
        $tableLink = [
            1 => (new WaService())->getWaList(['id' => $linkIds, 'page' => $params['page']])
        ];
        $type = (int)$params['type'];
        return $tableLink[$type];
    }

    /**
     * @desc       　添加收藏
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     */
    public function addFavorites(array $params){
        $this->validate->checkFavorites();
        if (!$this->validate->validate($params)) {
            CommonException::msgException($this->validate->getError()->__toString());
        }
        $id = (int)$params['link_id'];
        (new WaService())->incrementWaFavorites($id, 1);

        $userId = Common::getUserId();
        $addData = [
            'type' => $params['type'],
            'link_id' => $params['link_id'],
            'user_id' => $userId
        ];
        WowUserLikesModel::query()->insert($addData);
        return null;
    }

    /**
     * @desc       　喜欢和取消喜欢操作
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return null
     */
    public function toLikes(array $params){
        $this->validate->checkoutLikes();
        if (!$this->validate->validate($params)) {
            CommonException::msgException($this->validate->getError()->__toString());
        }
        dump(Common::getUserInfo());

        $id = (int)$params['link_id'];
        $count = WowUserLikesModel::query()->where('link_id', $id)->where('type', $params['type'])->count();
        if(!$count){
            $this->addLikes($params);
        }else{
            $this->cancelFavorites($params);
        }
        return null;
    }

    /**
     * @desc       　点赞||取消点赞
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return null
     */
    public function addLikes(array $params){
        $this->validate->checkoutLikes();
        if (!$this->validate->validate($params)) {
            CommonException::msgException($this->validate->getError()->__toString());
        }
        $id = (int)$params['link_id'];
        (new WaService())->incrementWaLikes($id, 1);
        $userId = Common::getUserId();
        $addData = [
            'type' => $params['type'],
            'link_id' => $params['link_id'],
            'user_id' => $userId
        ];
        WowUserLikesModel::query()->insert($addData);
        return null;
    }

    /**
     * @desc       　取消收藏
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     */
    public function cancelFavorites(array $params){
        $this->validate->checkFavorites();
        if (!$this->validate->validate($params)) {
            CommonException::msgException($this->validate->getError()->__toString());
        }
        $id = (int)$params['link_id'];
        (new WaService())->incrementWaFavorites($id, -1);
        $where = [
            ['link_id','=', $params['link_id']],
            ['type','=', $params['type']],
            ['user_id','=', Common::getUserId()],
        ];
        WowUserLikesModel::query()->where($where)->delete();
        return null;
    }

    /**
     * @desc       　取消点赞
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     */
    public function cancelLikes(array $params){
        $this->validate->checkoutLikes();
        if (!$this->validate->validate($params)) {
            CommonException::msgException($this->validate->getError()->__toString());
        }
        $id = (int)$params['link_id'];
        (new WaService())->incrementWaLikes($id, -1);
        $where = [
            ['link_id','=', $params['link_id']],
            ['type','=', $params['type']],
            ['user_id','=', Common::getUserId()],
        ];
        WowUserLikesModel::query()->where($where)->delete();
        return null;
    }

    /**
     * @desc       　获取当前用户是否有点赞收藏
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param int    $id
     * @param string $likeColumn
     * @param string $favoritesColumn
     *
     * @return int[]
     */
    public function getIsLikes(int $id, string $likeColumn = 'is_like', string $favoritesColumn = 'is_favorites'){
        $return = [$likeColumn => 0, $favoritesColumn => 0];
        if(!Common::getUserId()){
            return $return;
        }
        $likesLink = WowUserLikesModel::query()->where('link_id', $id)->whereIn('type', [1,2])->pluck('link_id', 'type');
        return [$likeColumn => !empty($likesLink[2]) ? 1 : 0, $favoritesColumn => !empty($likesLink[1]) ? 1 : 0];
    }
}
