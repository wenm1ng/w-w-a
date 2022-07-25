<?php
/*
 * @desc       
 * @author     文明<736038880@qq.com>
 * @date       2022-07-23 16:31
 */
namespace App\Work\Chat\Service;

use App\Work\Validator\ChatValidator;
use App\Exceptions\CommonException;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use User\Service\LoginService;
use App\Work\Chat\Models\WowLogModel;
use Common\Common;

class ChatService
{
    protected $validator;
    protected $userInfo;
    protected $messageMaxLen = 100;
    public function __construct()
    {
        $this->validator = new ChatValidator();
    }

    /**
     * @desc       记录聊天日志
     * @author     文明<736038880@qq.com>
     * @date       2022-07-25 11:39
     * @param array $params
     *
     * @return null
     */
    public function recordLog(array $params){
        $this->validator->checkRecord();
        if (!$this->validator->validate($params)) {
            CommonException::msgException($this->validator->getError()->__toString());
        }
        $insertData = [
            'user_id' => Common::getUserId(),
            'content' => $params['content']
        ];
        WowLogModel::query()->insert($insertData);
        return null;
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
//        $roomId = $params['room_id'];
        $start = ($params['page']-1) * $params['pageSize'];
        $end = $params['page'] * ($params['pageSize'] - 1);
        $list = redis()->lRange('chat_record', $start, $end);
        $todayTime = strtotime(date('Y-m-d').' 23:59:59');
        if(!empty($list)){
            $list = array_reverse($list);
            $newList = [];
            $preTime = 0;
            foreach ($list as &$val) {
                $val = json_decode($val, true);
                $timestamp = strtotime($val['date_time']);
                if($timestamp - $preTime >= 120){
                    if($todayTime - $timestamp <= 3600*24){
                        //当天消息，不需要日期，只要时分秒
                        $time = date('H:i:s', $timestamp);
                    }else{
                        //全局日期消息
                        $time = $val['date_time'];
                    }
                    $newList[] = [
                        'type' => 'time',
                        'content' => $time
                    ];
                }
                $preTime = $timestamp;
                $newList[] = [
                    'type' => 'message',
                    'content' => $val['content'],
                    'user_id' => $val['user_id'],
                    'user_name' => $val['user_name'],
                    'avatar_url' => $val['avatar_url']
                ];
            }
            $list = $newList;
        }else{
            $list = [];
        }
        return $list;
    }

    public function run(EventRegister $register){
//        $server = \EasySwoole\EasySwoole\ServerManager::getInstance()->getSwooleServer();
//
//        $subPort = $server->addlistener('0.0.0.0', 9908, EASYSWOOLE_WEB_SOCKET_SERVER);
//        $subPort->set([
//            // swoole 相关配置
//            'open_length_check' => false,
////            'package_length_type'   => 'N',
////            'package_length_offset' => 0,
////            'package_body_offset'   => 4,
////            'package_max_length'    => 1024*1024
//        ]);
//        $subPort->on($register::onConnect, function (\Swoole\Server $server, int $fd, int $reactor_id) {
//            echo "fd {$fd} connected";
//        });
//
//        $subPort->on($register::onReceive, function (\Swoole\Server  $server, int $fd, int $reactor_id, string $data) {
//            echo "fd:{$fd} send:{$data}\n";
//        });
//
//        $subPort->on($register::onClose, function (\Swoole\Server  $server, int $fd, int $reactor_id) {
//            echo "fd {$fd} closed";
//        });


        $register->set($register::onOpen, function ($ws, $request) {
//            var_dump($request->fd, $request->server);
            dump($request->fd, "hello, welcome\n");
//            $ws->push($request->fd, "message");
//            $list = $ChatService->getRoomMember();
//            if(!empty($list)){
//                foreach($list as $id){
//                    $ws->push();
//                }
//            }
//            $ChatService->addMember($request->fd);
        });

        $register->set($register::onMessage, function (\Swoole\WebSocket\Server $server, \Swoole\WebSocket\Frame $frame) {
            try {
                $data = json_decode($frame->data, true);
                if(empty($data['token'])){
                    return;
                }
                try {
                    $this->userInfo = (new LoginService())->checkToken($data['token']);
                }catch (\Exception $e){
                    return;
                }
                if(empty($data['action'])){
                    $server->push($frame->fd, json_encode(['status'=>400, 'msg' =>'参数错误']));
                    return;
                }
                call_user_func([$this, $data['action']], $server, $frame->fd, $data);
            }catch (\Exception $e){
                $server->push($frame->fd, json_encode(['status'=>400, 'msg' =>$e->getMessage()]));
            }

//            $server->push($frame->fd, "server: {$frame->data}");
        });

        $register->set($register::onClose, function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
            $this->redisLeaveRoom($fd);
        });
    }

    /**
     * @desc       用户进入房间，通知其他用户
     * @example
     * @param \Swoole\WebSocket\Server $server
     * @param int                      $fd
     * @param array                    $data
     */
    public function entryRoom(\Swoole\WebSocket\Server $server, int $fd, array $data = []){
        $info = array_merge([
            'action' => 'entryRoom',
        ], $this->userInfo);
        redis()->sadd('chat_room', $fd);
        //发消息给客户端
        $this->noticeMessage($server, $fd, $info);
    }

    /**
     * @desc   发送消息给客户端
     * @example
     * @param \Swoole\WebSocket\Server $server
     * @param int                      $fd
     * @param array                    $data
     * @param int                      $isMyself 是否需要给自己提示 1是 0否
     */
    public function noticeMessage(\Swoole\WebSocket\Server $server, int $fd, array $data){
        $list = redis()->sMembers('chat_room');
        if(!empty($list)){
            $info = json_encode($data);
            foreach($list as $id){
                $server->push($id, $info);
            }
        }
    }

    /**
     * @desc       退出websocket离开房间
     * @author     文明<736038880@qq.com>
     * @date       2022-07-25 11:09
     * @param \Swoole\WebSocket\Server $server
     * @param int                      $fd
     * @param array                    $data
     */
    public function leaveRoom(\Swoole\WebSocket\Server $server, int $fd, array $data){
        $info = array_merge([
            'action' => 'leaveRoom',
        ], $this->userInfo);
        //发消息给客户端
        $this->noticeMessage($server, $fd, $info);
        $this->redisLeaveRoom($fd);
    }

    /**
     * @desc       移除redis集合
     * @author     文明<736038880@qq.com>
     * @date       2022-07-25 11:20
     * @param int $fd
     */
    public function redisLeaveRoom(int $fd){
        redis()->srem('chat_room', $fd);
    }
    /**
     * @desc   发言监听
     * @example
     * @param \Swoole\WebSocket\Server $server
     * @param int                      $fd
     * @param array                    $data
     */
    public function speak(\Swoole\WebSocket\Server $server, int $fd, array $data = []){
        $jsonData = array_merge([
            'content' => $data['content'],
            'date_time' => date('Y-m-d H:i:s')
        ], $this->userInfo);
        redis()->lpush('chat_record', json_encode($jsonData));
        $len = redis()->llen('chat_record');
        if($len > $this->messageMaxLen){
            $num = $len - $this->messageMaxLen;
            for ($i = 0; $i < $num; $i++){
                redis()->rpop('chat_record');
            }
        }
        $jsonData['action'] = 'speak';
        //通知
        $this->noticeMessage($server, $fd, $jsonData);
    }
}