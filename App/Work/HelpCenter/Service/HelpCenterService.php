<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-28 13:54
 */
namespace App\Work\HelpCenter\Service;

use App\Work\Validator\HelpCenterValidator;
use App\Exceptions\CommonException;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use User\Service\UserService;
use App\Work\HelpCenter\Models\WowHelpAnswerModel;
use App\Work\HelpCenter\Models\WowHelpCenterModel;
use Common\Common;
use Wa\Models\WowUserLikesModel;
use App\Utility\Database\Db;

class HelpCenterService
{
    protected $validator;
    public function __construct()
    {
        $this->validator = new HelpCenterValidator();
    }

    /**
     * @desc       获取帮助列表
     * @author     文明<736038880@qq.com>
     * @date       2022-07-28 14:39
     * @param array $params
     *
     * @return array
     */
    public function getHelpList(array $params){
        $this->validator->checkPage();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $where = [
            ['status', '=', 1]
        ];
        if(!empty($params['version'])){
            $where['where'][] = [
                ['version', '=', $params['version']]
            ];
        }
        if(!empty($params['help_type'])){
            $where['where'][] = [
                ['help_type', '=', $params['help_type']]
            ];
        }
        if(!empty($params['is_pay'])){
            $where['where'][] = [
                ['is_pay', '=', $params['is_pay']]
            ];
        }
        if(empty($params['order'])){
            $where['order'] = ['modify_at' => 'desc', 'id' => 'desc'];
        }else{
            $where['order'] = [$params['order'] => 'desc', 'id' => 'desc'];
        }

        $fields = 'id,version,occupation,help_type,title,user_id,image_url,description,modify_at,favorites_num,help_num,read_num, 0 as has_favor, 0 as has_answer';
        $list = WowHelpCenterModel::getPageOrderList($where, $params['page'], $fields, $params['pageSize']);
        $list = (new UserService())->mergeUserInfo($list);
        $waIds = array_column($list, 'id');
        $list = $this->mergeCount($list, $waIds);
        $this->dealData($list);
        return ['list' => $list, 'page' => (int)$params['page']];
    }

    /**
     * @desc       处理返回数据
     * @author     文明<736038880@qq.com>
     * @date       2022-07-28 17:16
     * @param array $list
     */
    public function dealData(array &$list){
        foreach ($list as $key => $val) {
            $list[$key]['modify_at'] = getTimeFormat($val['modify_at']);
        }
    }
    /**
     * @desc       合并是否包含当前登录用户
     * @author     文明<736038880@qq.com>
     * @date       2022-07-28 14:38
     * @param array $list
     * @param array $ids
     * @param int   $isInfo
     *
     * @return array
     */
    public function mergeCount(array $list, array $ids, int $isInfo = 0){
        if(empty($ids)){
            return $list;
        }
        $userId = Common::getUserId();
        if(empty($userId)){
            return $list;
        }
        $whereLikes = [
            'whereIn' => [
                ['link_id', $ids],
            ],
            'where' => [
                ['user_id', '=', $userId],
                ['type', '=', 3]
            ]
        ];
        $whereAnswer = [
            'whereIn' => [
                ['help_id', $ids]
            ],
            'where' => [
                ['user_id', '=', $userId]
            ]
        ];

        //获取点赞、评论高亮
        $likeLink = WowUserLikesModel::baseQuery($whereLikes)->pluck('id', 'link_id')->toArray();
        $answerLink = WowHelpAnswerModel::baseQuery($whereAnswer)->pluck('id', 'help_id')->toArray();

        if(!$isInfo){
            foreach ($list as $key => $val) {
                $list[$key]['has_favor'] = !empty($likeLink[$val['id']]) ? 1 : 0;
                $list[$key]['has_answer'] = !empty($answerLink[$val['id']]) ? 1 : 0;
            }
        }else{
            $list['has_favor'] = !empty($likeLink[$list['id']]) ? 1 : 0;
            $list['has_answer'] = !empty($answerLink[$list['id']]) ? 1 : 0;
        }
        return $list;
    }
}