<?php


namespace App\HttpController;


use EasySwoole\Http\AbstractInterface\AbstractRouter;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use FastRoute\RouteCollector;

class Router extends AbstractRouter
{
    function initialize(RouteCollector $routeCollector)
    {
        $routeCollector->addGroup('/api/v1', function (RouteCollector $collector) {

            $apiBasePathOpenV1 = 'Api/V1/';
            //用户模块
            $this->user($collector, $apiBasePathOpenV1);
            //版本模块
            $this->version($collector, $apiBasePathOpenV1);
            //职业模块
            $this->occupation($collector, $apiBasePathOpenV1);
            //天赋模块
            $this->talent($collector, $apiBasePathOpenV1);
            //伤害测试模块
            $this->damage($collector, $apiBasePathOpenV1);
            //wa模块
            $this->wa($collector, $apiBasePathOpenV1);
            //测试
            $this->test($collector, $apiBasePathOpenV1);
            //聊天室
            $this->chatRoom($collector, $apiBasePathOpenV1);
            //帮助中心
            $this->helpCenter($collector, $apiBasePathOpenV1);
        });

    }

    public function talent(RouteCollector $collector, string $basePath = ''){
        //天赋列表
        $collector->get('/talent/get-talent-list',$basePath.'Talent/Talent/getTalentList');
        //天赋技能树
        $collector->post('/talent/get-talent-tree-list',$basePath.'Talent/Talent/getTalentSkillTree');
        //添加用户天赋信息
        $collector->post('/talent/add-user-talent',$basePath.'Talent/Talent/addUserTalent');
        //修改用户天赋信息
        $collector->post('/talent/update-user-talent',$basePath.'Talent/Talent/updateUserTalent');
        //天赋大厅列表
        $collector->post('/talent/get-talent-hall-list',$basePath.'Talent/Talent/getTalentHallList');
        //用户天赋列表
        $collector->post('/talent/get-user-talent-list',$basePath.'Talent/Talent/getUserTalentList');
        //进行天赋大厅的评论
        $collector->post('/talent/create-comment',$basePath.'Talent/Comment/createComment');
        //获取天赋大厅的评论列表
        $collector->get('/talent/get-talent-comment-list',$basePath.'Talent/Comment/getTalentCommentList');
        //删除自己的评论
        $collector->post('/talent/del-comment',$basePath.'Talent/Comment/delComment');
    }

    public function occupation(RouteCollector $collector, string $basePath = '')
    {
        //职业列表
        $collector->get('/occupation/get-occupation-list',$basePath.'Occupation/Occupation/getOccupationList');

    }

    public function version(RouteCollector $collector, string $basePath = '')
    {
        //版本列表
        $collector->get('/version/get-version-list',$basePath.'Version/Version/getVersionList');

    }

    public function user(RouteCollector $collector, string $basePath = '')
    {
        //保存用户信息
        $collector->post('/user',$basePath.'User/Login/saveUserInfo');
        //用户详情
        $collector->get('/user',$basePath.'User/User/getUserInfo');
        //用户收藏列表
        $collector->get('/user/favorites/list',$basePath.'User/User/getFavoritesList');
        //用户添加收藏
        $collector->post('/user/favorites/add',$basePath.'User/User/addFavorites');
        //用户取消收藏
        $collector->post('/user/favorites/cancel',$basePath.'User/User/cancelFavorites');
        //点赞
        $collector->post('/user/likes/add',$basePath.'User/User/addLikes');
        //取消点赞
        $collector->post('/user/likes/cancel',$basePath.'User/User/cancelLikes');
        //点赞和取消点赞
        $collector->post('/user/likes',$basePath.'User/User/toLikes');
        //获取用户点赞、收藏数
        $collector->get('/user/get-num',$basePath.'User/Login/getNum');
        //获取用户未读消息数
        $collector->post('/user/get-message',$basePath.'User/Login/getMessage');
    }

    public function damage(RouteCollector $collector, string $basePath = ''){

        $collector->post('/test',$basePath.'Damage/Damage/test');
    }

    public function wa(RouteCollector $collector, string $basePath = ''){
        //获取wa tab列表信息
        $collector->get('/wa/get-tab-list',$basePath.'Wa/Wa/getTabList');
        //获取wa内容列表
        $collector->get('/wa/get-wa-list',$basePath.'Wa/Wa/getWaList');
        //获取wa详情
        $collector->get('/wa/get-wa-info',$basePath.'Wa/Wa/getWaInfo');
        //获取wa标签
        $collector->get('/wa/get-wa-label',$basePath.'Wa/Wa/getLabels');
        //获取wa评论
        $collector->get('/wa/get-comment',$basePath.'Wa/Wa/getWaComment');
        //进行评论
        $collector->post('/wa/to-comment',$basePath.'Wa/WaL/toComment');
        //删除评论
        $collector->post('/wa/del-comment',$basePath.'Wa/WaL/delComment');
        //获取wa收藏列表
        $collector->get('/wa/get-wa-favorites-list',$basePath.'Wa/WaL/getWaFavoritesList');
        //获取用户所有wa评论
        $collector->get('/wa/get-comment-all',$basePath.'Wa/WaL/getCommentAll');
        //保存爬虫数据
        $collector->post('/wa/save-fiddler-data',$basePath.'Wa/Wa/saveFiddlerData');
    }

    public function test(RouteCollector $collector, string $basePath = '')
    {
        $collector->get('/test',$basePath.'File/File/uploadImageToBlog');
        $collector->get('/test-new',$basePath.'Test/Test/test');
        $collector->post('/upload',$basePath.'File/File/uploadImage');
    }

    public function chatRoom(RouteCollector $collector, string $basePath = ''){
        //获取聊天室历史记录
        $collector->get('/chat-room/get-history',$basePath.'Chat/Chat/getChatHistory');
        //获取房间当前成员
        $collector->get('/chat-room/get-member',$basePath.'Chat/Chat/getChatMember');
        //记录错误日志
        $collector->post('/chat-room/record-log',$basePath.'Chat/Chat/recordLog');
    }

    public function helpCenter(RouteCollector $collector, string $basePath = ''){
        //获取帮助列表
        $collector->post('/help-center/list',$basePath.'HelpCenter/HelpCenter/getHelpList');
    }

}