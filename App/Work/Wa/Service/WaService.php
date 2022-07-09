<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-05-07 10:52
 */
namespace Wa\Service;

use Common\Common;
use Wa\Models\WowWaTabTitleModel;
use Wa\Models\WowWaTabModel;
use Version\Models\WowVersionModelNew;
use Wa\Models\WowWaImageModel;
use Wa\Models\WowWaContentModel;
use Wa\Models\WowWaContentHistoryModel;
use Wa\Models\WowUserLikesModel;
use Wa\Models\WowWaCommentModel;
use App\Exceptions\CommonException;
use App\Work\Validator\WaValidator;
use Occupation\Models\WowOccupationModelNew;
use Occupation\Service\OccupationService;
use User\Service\UserService;
use App\Utility\Database\Db;
use Talent\Models\WowTalentModelNew;

class WaService
{
    protected $validator;

    public function __construct()
    {
        $this->validator = new WaValidator();
    }

    /**
     * @desc       　获取tab列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return array
     */
    public function getTabList(array $params){
        $this->validator->checkVerision();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $version = (int)$params['version'];
        $tabList = WowWaTabModel::getEnableList($version);
        $where = [
            'where' => [
                ['version', '=', $version]
            ]
        ];
        $ocList = (new OccupationService())->getOcListByVersion($version);
        $titleList = WowWaTabTitleModel::getList($where, 'id as tt_id,version,type,title,image_url,description');
        $newTitleList = [];
        foreach ($titleList as &$val) {
            $temp = explode('#', $val['description']);
            $val['description'] = [];
            $val['occupation'] = '';
            foreach ($temp as $description) {
                $val['description'][] = ['description' => $description];
            }
            $newTitleList[$val['type']][] = $val;
        }

        foreach ($tabList as &$tabVal) {
            if($tabVal['type'] == 1){
                $tabVal['child'] = $ocList;
            }else{
                $tabVal['child'] = $newTitleList[$tabVal['type']] ?? [];
            }
        }
        return $tabList;
    }

    /**
     * @desc       　获取wa列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return array
     */
    public function getWaList(array $params){
        $this->validator->checkGetWaList($params);
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        if(!empty($params['tt_id'])){
            $where = [
                'where' => [
                    ['tt_id', '=', $params['tt_id']]
                ],
            ];
        }elseif(!empty($params['oc'])){
            $where = [
                'where' => [
                    ['version', '=', $params['version']],
                    ['occupation', '=', $params['oc']],
                ],
            ];
            if(!empty($params['talent_name']) && $params['talent_name'] !== '全部'){
                $where['where'][] = ['talent_name', '=', $params['talent_name']];
            }
        }elseif(!empty($params['id'])){
            $where = [
                'whereIn' => [
                    ['id', $params['id']]
                ],
            ];
        }
        $where['order'] = ['update_at' => 'desc'];
        if(!empty($params['order'])){
            $where['order'] = [$params['order'] => 'desc'];
        }
        $list = WowWaContentModel::getPageOrderList($where, $params['page'], 'id,title,user_id,update_at,description,read_num', $params['pageSize']);
        $list = (new UserService())->mergeUserName($list);
        $list = $this->mergeWaImage($list, 3);
        $waIds = array_column($list, 'id');
        $list = $this->mergeWaCount($list, $waIds);
        return ['list' => $list, 'page' => (int)$params['page']];
    }

    /**
     * @desc       　合并wa相关数量信息（点赞、评论等）
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $info
     * @param array $waId
     * @param int   $isInfo
     *
     * @return array
     */
    private function mergeWaCount(array $info, array $waId, int $isInfo = 0){
        if(empty($waId)){
            return $info;
        }
        $userId = Common::getUserId();
        $whereLikes = [
            'whereIn' => [
                ['link_id', $waId]
            ],
            'where' => [
                ['type', '=', 2]
            ]
        ];
        $whereComment = [
            'whereIn' => [
                ['wa_id', $waId]
            ],
            'where' => [
                ['status', '=', 1]
            ]
        ];

        //获取点赞、评论高亮
        $likesLink = WowUserLikesModel::baseQuery($whereLikes)->select(Db::raw('count(1) as total,link_id'))->groupBy(['link_id'])->pluck('total','link_id')->toArray();
        $commentLink = WowWaCommentModel::baseQuery($whereComment)->select(Db::raw('count(1) as total,wa_id'))->groupBy(['wa_id'])->pluck('total','wa_id')->toArray();

        if(!empty($userId)){
            $whereLikes['where'][] = $whereComment['where'][] = ['user_id', '=', $userId];
            $likesLinkUser = WowUserLikesModel::baseQuery($whereLikes)->select(Db::raw('count(1) as total,link_id'))->groupBy(['link_id'])->pluck('total','link_id')->toArray();
            $commentLinkUser = WowWaCommentModel::baseQuery($whereComment)->select(Db::raw('count(1) as total,wa_id'))->groupBy(['wa_id'])->pluck('total','wa_id')->toArray();
        }
        if(!$isInfo){
            dump(1);
            foreach ($info as $key => $val) {
                $info[$key]['flod'] = false;
                $info[$key]['likes_count'] = $likesLink[$val['id']] ?? 0;
                $info[$key]['comment_count'] = $commentLink[$val['id']] ?? 0;
                $info[$key]['has_likes'] = !empty($likesLinkUser[$val['id']]) ? 1 : 0;
                $info[$key]['has_comment'] = !empty($commentLinkUser[$val['id']]) ? 1 : 0;
            }
        }else{
            $info['likes_count'] = $likesLink[$info['id']] ?? 0;
            $info['comment_count'] = $commentLink[$info['id']] ?? 0;
            $info['has_likes'] = !empty($likesLinkUser[$info['id']]) ? 1 : 0;
            $info['has_comment'] = !empty($commentLinkUser[$info['id']]) ? 1 : 0;
        }
        return $info;
    }
    /**
     * @desc       　获取wa详情
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param int $waId
     *
     * @return array|mixed
     */
    public function getWaInfo(int $waId){
        //浏览量+1
        WowWaContentModel::query()->where('id', $waId)->increment('read_num', 1);
        $list = WowWaContentModel::query()->where('id', $waId)->select(['id','title','description','update_description','wa_content','update_at','user_id','read_num','favorites_num','likes_num','talent_name as label'])->get()->toArray();
        if(empty($list)){
            CommonException::msgException('该wa不存在');
        }
        $list = $this->mergeWaImage($list);

        $userService = new UserService();
        $list = UserService::mergeUserNameAvatarUrl($list);
        $list = $this->mergeWaHistory($list);

        $info = $list[0] ?? [];
        $info = array_merge($info, $userService->getIsLikes((int)$info['id']));
        $info = $this->mergeWaCount($info, [$waId], 1);
        return $info;
    }

    /**
     * @desc       　获取wa标签
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return array
     */
    public function getLabels(array $params){
        $this->validator->checkgetLabels($params);
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        if(!empty($params['oc'])){
            $labels = WowTalentModelNew::getTalentByVersionOc($params['version'], $params['oc']);
        }elseif(!empty($params['tt_id'])){
            $info = WowWaTabTitleModel::query()->where('tt_id', $params['tt_id'])->first();
            $labels = [];
            if(!empty($info)){
                $labels = explode('#', $info->toArray()['description']);
            }
        }

        return ['oc' => $params['oc'], 'labels' => $labels];
    }

    /**
     * @desc       　合并wa图片
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $list
     * @param array $num
     *
     * @return array
     */
    public function mergeWaImage(array $list, int $num = 0){
        $waIds = array_column($list, 'id');
        $imageLink = [];
        if(!empty($waIds)){
            $imageLink = WowWaImageModel::query()->whereIn('wa_id', $waIds)->select(Db::raw('origin_image_url as image_url, wa_id'))->get()->toArray();
            $imageLink = Common::arrayGroup($imageLink, 'wa_id');
        }

        foreach ($list as &$val) {
            $val['images'] = $imageLink[$val['id']] ?? [];
            if(!empty($num)){
                $val['images'] = array_slice($val['images'],0, 3);
            }
        }
        return $list;
    }

    /**
     * @desc       　合并wa版本历史记录
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $list
     *
     * @return array
     */
    public function mergeWaHistory(array $list){
        $waIds = array_column($list, 'id');
        $historyLink = [];
        if(!empty($waIds)){
            $historyLink = WowWaContentHistoryModel::query()->whereIn('wa_id', $waIds)->get(['version_number', 'wa_content', 'wa_id'])->toArray();
            $historyLink = Common::arrayGroup($historyLink, 'wa_id');
        }
        foreach ($list as &$val) {
            $val['history_version'] = $historyLink[$val['id']] ?? [];
        }
        return $list;
    }

    /**
     * @desc       　标记wa收藏
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param int $id
     * @param int $num
     */
    public function incrementWaFavorites(int $id, int $num){
        WowWaContentModel::query()->where('id', $id)->increment('favorites_num', $num);
    }

    /**
     * @desc       　标记wa喜欢
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param int $id
     * @param int $num
     */
    public function incrementWaLikes(int $id, int $num){
        WowWaContentModel::query()->where('id', $id)->increment('likes_num', $num);
    }

    /**
     * @desc       　获取wa评论列表
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return array
     */
    public function getWaComment(array $params){
        $this->validator->checkWaId();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $waId = $params['id'];
        //获取点赞、评论高亮
        $where = [
            'wa_id' => $waId,
            'status' => 1,
//            'comment_id' => 0
        ];
        $fields = 'id,user_id,comment_id,content,create_at,reply_user_id';
        $commentList = WowWaCommentModel::query()->where($where)->select(Db::raw($fields))->orderBy('create_at')->get()->toArray();
//        $commentIds = array_column($commentList, 'id');
        $commentList = UserService::mergeUserNameAvatarUrl($commentList);
        return $commentList;
//        $replyList = [];
//        if(!empty($commentIds)){
//            unset($where['comment_id']);
//            $replyList = WowWaCommentModel::query()->where($where)->whereIn('comment_id', $commentIds)->select(Db::raw($fields))->orderBy('create_at', 'asc')->get()->toArray();
//            $replyList = UserService::mergeUserNameAvatarUrl($replyList);
//            $replyList = Common::arrayGroup($replyList, 'comment_id');
//        }
//
//        //重新二维数组排序
//        $newCommentList = [];
//        foreach ($commentList as $comment) {
//            $newCommentList[] = $comment;
//            if(isset($replyList[$comment['id']])){
//                foreach ($replyList[$comment['id']] as $childComment) {
//                    $newCommentList[] = $childComment;
//                }
//            }
//        }
//
//        return $newCommentList;
    }

    /**
     * @desc       　进行评论
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param array $params
     *
     * @return int
     */
    public function toComment(array $params){
        $this->validator->checkComment();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $insertData = [
            'wa_id' => $params['wa_id'],
            'content' => $params['content'],
            'comment_id' => $params['comment_id'] ?? 0,
            'user_id' => Common::getUserId(),
            'reply_user_id' => $params['reply_user_id']
        ];
        return WowWaCommentModel::query()->insertGetId($insertData);
    }

    /**
     * @desc       　删除评论
     * @example    　
     * @author     　文明<wenming@ecgtool.com>
     * @param int $commentId
     *
     * @return null
     */
    public function delComment(int $commentId){
        if(empty($commentId)){
            CommonException::msgException('评论id不能为空');
        }
        WowWaCommentModel::query()->where('id', $commentId)->where('user_id', Common::getUserId())->delete();
        return null;
    }
}