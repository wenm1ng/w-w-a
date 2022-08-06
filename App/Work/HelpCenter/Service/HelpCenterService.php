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
use App\Work\Config;
use App\Work\Common\File;
use Wa\Models\WowWaCommentModel;

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
            'where' => [
                ['status', '=', 1]
            ]
        ];
        if(!empty($params['version'])){
            $where['where'][] = ['version', '=', $params['version']];
        }
        if(!empty($params['help_type'])){
            $where['where'][] = ['help_type', '=', $params['help_type']];
        }
        if(!empty($params['adopt_type'])){
            $where['where'][] = ['is_adopt', '=', $params['adopt_type']];
        }
        if(!empty($params['is_pay'])){
            $where['where'][] = ['is_pay', '=', $params['is_pay']];
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
     * @desc       获取回答列表
     * @author     文明<736038880@qq.com>
     * @date       2022-08-04 10:21
     * @param array $params
     *
     * @return array
     */
    public function getAnswerList(array $params){
        $this->validator->checkId();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $where = [
            'where' => [
                ['help_id', '=', $params['id']]
            ],
            'order' => ['favorites_num' => 'desc', 'comment_num' => 'desc', 'id' => 'desc'],
        ];
        $fields = 'id,help_id,user_id,image_url,description,modify_at';
        $list = WowHelpAnswerModel::getPageOrderList($where, $params['page'], $fields, $params['pageSize']);

        $list = (new UserService())->mergeUserInfo($list);
        $waIds = array_column($list, 'id');
        $list = $this->mergeCount($list, $waIds, 4);
        foreach ($list as &$val) {
            $val['modify_at'] = getTimeFormat($val['modify_at']);
        }
        return ['list' => $list, 'page' => (int)$params['page']];
    }


    /**
     * @desc       处理返回数据
     * @author     文明<736038880@qq.com>
     * @date       2022-07-28 17:16
     * @param array $list
     */
    public function dealData(array &$list){
        $versionList = (new \Version\Service\VersionService())->getVersionList();
        $versionList = array_column($versionList, 'name', 'version');
        foreach ($list as $key => $val) {
            $list[$key]['modify_at'] = getTimeFormat($val['modify_at']);
            $list[$key]['flod'] = false;
            $list[$key]['version_name'] = $versionList[$val['version_id']] ?? '正式服';
            $list[$key]['help_type_name'] = Config::$helpTypeLink[$val['help_type']] ?? '插件研究';
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
    public function mergeCount(array $list, array $ids, int $type = 3){
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
                ['type', '=', $type]
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
        if($type === 3){
            $answerLink = WowHelpAnswerModel::baseQuery($whereAnswer)->pluck('id', 'help_id')->toArray();
        }else{
            $commentLink = WowWaCommentModel::query()->whereIn('wa_id', $ids)->where('user_id', $userId)->where('type', 2)->pluck('id', 'wa_id')->toArray();
        }

        foreach ($list as $key => $val) {
            $list[$key]['has_favor'] = !empty($likeLink[$val['id']]) ? 1 : 0;
            $list[$key]['has_answer'] = !empty($answerLink[$val['id']]) ? 1 : 0;
            $list[$key]['has_comment'] = !empty($commentLink[$val['id']]) ? 1 : 0;
        }

        return $list;
    }

    /**
     * @desc       帮助详情
     * @author     文明<736038880@qq.com>
     * @date       2022-08-02 18:11
     * @param int $id
     *
     * @return mixed
     */
    public function getHelpInfo(int $id){
        $info = WowHelpCenterModel::query()->where('id', $id)->first();
        if(empty($info) || empty($id)){
            CommonException::msgException('该帮助不存在');
        }
        $list = [$info->toArray()];
        $list = (new UserService())->mergeUserInfo($list);
        $list = $this->mergeCount($list, [$id]);
        $this->dealData($list);

        return $list[0];
    }

    /**
     * @desc       添加求助
     * @author     文明<736038880@qq.com>
     * @date       2022-07-29 14:52
     * @param array $params
     *
     * @return int
     */
    public function addHelp(array $params, \EasySwoole\Http\Request $request){
        $this->validator->checkAddHelp();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        $file = $request->getUploadedFile('file');
        $imageUrl = '';
        if (!empty($file) && $file->getSize()) {
            $fileName = $file->getClientFileName();
            $filend = pathinfo($fileName, PATHINFO_EXTENSION);
            $data = file_get_contents($file->getTempName());
            $fileName = saveFileDataImage($data, '/help', $filend);
            $imageUrl = getInterImageName($fileName);
        }
        dump($imageUrl);
        $insertData = [
            'title' => $params['title'],
            'description' => $params['description'],
            'help_type' => $params['help_type'],
            'version' => $params['version'],
            'is_adopt' => 2,
            'image_url' => $imageUrl,
            'user_id' => Common::getUserId(),
            'status' => 1,
            'is_pay' => $params['is_pay']
        ];
        $helpId = WowHelpCenterModel::query()->insertGetId($insertData);
        return $helpId;
    }

    /**
     * @desc       删除求助
     * @author     文明<736038880@qq.com>
     * @date       2022-07-29 14:52
     * @param array $params
     *
     * @return null
     */
    public function deleteHelp(array $params){
        $this->validator->checkId();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $info = WowHelpAnswerModel::query()->where('help_id', $params['id'])->select(['id'])->first();
        if(!empty($info)){
            CommonException::msgException('已有帮助人进行回答，无法删除');
        }
        $info = WowHelpCenterModel::query()->where('id', $params['id'])->select(['image_url'])->first();
        if(empty($info)){
            CommonException::msgException('数据不存在');
        }
        $info = $info->toArray();
        WowHelpCenterModel::query()->where('id', $params['id'])->update(['status' => 0]);
        (new File())->delImage($info['image_url']);

        return null;
    }

    /**
     * @desc       添加求助回答
     * @author     文明<736038880@qq.com>
     * @date       2022-07-29 15:21
     * @param array $params
     *
     * @return int
     */
    public function addAnswer(array $params){
        $this->validator->checkAddAnswer();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        $insertData = [
            'help_id' => $params['help_id'],
            'description' => $params['description'],
            'image_url' => !empty($params['image_url']) ? $params['image_url'] : '',
            'user_id' => Common::getUserId()
        ];

        $id = WowHelpAnswerModel::query()->insertGetId($insertData);

        return $id;
    }

    /**
     * @desc       修改求助回答
     * @author     文明<736038880@qq.com>
     * @date       2022-07-29 15:29
     * @param array $params
     *
     * @return null
     */
    public function updateAnswer(array $params){
        $this->validator->checkUpdateAnswer();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        $updateData = [
            'description' => $params['description'],
            'image_url' => !empty($params['image_url']) ? $params['image_url'] : '',
            'modify_at' => date('Y-m-d H:i:s')
        ];

        WowHelpAnswerModel::query()->where('id', $params['id'])->update($updateData);

        return null;
    }

    /**
     * @desc       提交回答（状态改为1）
     * @author     文明<736038880@qq.com>
     * @date       2022-07-29 15:34
     * @param array $params
     *
     * @return null
     */
    public function setAnswerStatus(array $params){
        $this->validator->checkId();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        $updateData = [
            'status' => 1,
            'modify_at' => date('Y-m-d H:i:s')
        ];

        WowHelpAnswerModel::query()->where('id', $params['id'])->update($updateData);

        return null;
    }

    /**
     * @desc       采纳答案
     * @author     文明<736038880@qq.com>
     * @date       2022-08-06 16:23
     * @param array $params
     *
     * @return null
     */
    public function adoptAnswer(array $params){
        $this->validator->checkId();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }

        $updateData = [
            'is_adopt_answer' => 1,
            'modify_at' => date('Y-m-d H:i:s')
        ];

        WowHelpAnswerModel::query()->where('id', $params['id'])->update($updateData);

        $updateData = [
            'is_adopt' => 1,
            'modify_at' => date('Y-m-d H:i:s')
        ];
        WowHelpCenterModel::query()->where('id', $params['help_id'])->update($updateData);

        return null;
    }
}