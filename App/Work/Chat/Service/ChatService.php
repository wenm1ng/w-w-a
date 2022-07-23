<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-23 16:31
 */
namespace App\Work\Chat\Service;

use App\Work\Validator\ChatValidator;
use App\Exceptions\CommonException;

class ChatService
{
    protected $validator;

    public function __construct()
    {
        $this->validator = new ChatValidator();
    }

    /**
     * @desc       获取聊天记录
     * @author     文明<736038880@qq.com>
     * @date       2022-07-23 17:33
     * @param array $params
     *
     * @return array
     */
    public function getChatHistory(array $params){
        $this->validator->checkRoom();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
//        $arr = json_encode(['name'=>'文明2','age'=>1]);
//        redis()->lpush('room:1', $arr);
        $roomId = $params['room_id'];
        $start = ($params['page']-1) * $params['pageSize'];
        $end = $params['page'] * $params['pageSize'];
        $list = redis()->lRange('room:'.$roomId, $start, $end);
        if(!empty($list)){
            $list = array_reverse($list);
            foreach ($list as &$val) {
                $val = json_decode($val, true);
            }
        }else{
            $list = [];
        }
        return $list;
    }
}