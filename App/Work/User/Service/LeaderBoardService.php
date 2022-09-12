<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-09-10 16:48
 */
namespace User\Service;

/**
 * UserService不要去掉会报错
 */

use Common\Common;
use User\Validator\UserValidate;
use User\Models\LeaderBoardModel;
use App\Exceptions\CommonException;
use App\Work\Config;

class LeaderBoardService
{

    public function __construct($token = "")
    {
        $this->validate = new UserValidate();
    }

    public function getList(array $params){
        $this->validate->checkBoardGetList();
        if (!$this->validate->validate($params)) {
            CommonException::msgException($this->validate->getError()->__toString());
        }
        $redisKey = Config::REDIS_KEY_BOARD . $params['year']. $params['week'];
        redis()->zRange($redisKey, 0, -1);
        LeaderBoardModel::query()->where('year', $params['year'])->where('week', $params['week'])->get()->toArray();
    }
}