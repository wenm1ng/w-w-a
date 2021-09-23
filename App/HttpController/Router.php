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
        });

    }

    public function talent(RouteCollector $collector, string $basePath = ''){
        //天赋列表
        $collector->get('/talent/get-talent-list',$basePath.'Talent/Talent/getTalentList');
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

    }
}