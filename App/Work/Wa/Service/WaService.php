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
        $list = WowWaContentModel::getPageOrderList($where, $params['page'], 'id,title,user_id,update_at,description');
        $list = (new UserService())->mergeUserName($list);
        $list = $this->mergeWaImage($list);
        $userId = Common::getUserId();
        if(!empty($userId)){
            //获取点赞、评论高亮
        }
        foreach ($list as &$val) {
            $val['flod'] = false;
        }
        return ['list' => $list, 'page' => (int)$params['page']];
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
        $list = WowWaContentModel::query()->where('id', $waId)->select(['id','title','description','update_description','wa_content','update_at','user_id','read_num','favorites_num','likes_num'])->get()->toArray();
        if(empty($list)){
            CommonException::msgException('该wa不存在');
        }
        $list = $this->mergeWaImage($list);

        $userService = new UserService();
        $list = $userService->mergeUserName($list);
        $list = $this->mergeWaHistory($list);
        //浏览量+1
        WowWaContentModel::query()->where('id', $waId)->increment('read_num', 1);
        $info = $list[0] ?? [];
        $info = array_merge($info, $userService->getIsLikes((int)$info['id']));
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
     *
     * @return array
     */
    public function mergeWaImage(array $list){
        $waIds = array_column($list, 'id');
        $imageLink = [];
        if(!empty($waIds)){
            $imageLink = WowWaImageModel::query()->whereIn('wa_id', $waIds)->select(Db::raw('origin_image_url as image_url, wa_id'))->get()->toArray();
            $imageLink = Common::arrayGroup($imageLink, 'wa_id');
        }

        foreach ($list as &$val) {
            $val['images'] = $imageLink[$val['id']] ?? [];
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
}