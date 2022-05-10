<?php


namespace EasySwoole\EasySwoole;


use EasySwoole\EasySwoole\AbstractInterface\Event;
use EasySwoole\EasySwoole\Swoole\EventRegister;
use EasySwoole\Http\Request;
use EasySwoole\Http\Response;
use EasySwoole\ORM\DbManager;
use EasySwoole\ORM\Db\Connection;
use EasySwoole\ORM\Db\Config;
use EasySwoole\EasySwoole\Config as SettingConfig;
use App\Utility\Common;

class EasySwooleEvent implements Event
{
    public static function initialize()
    {
        date_default_timezone_set('Asia/Shanghai');
//        $config = new Config(SettingConfig::getInstance()->getConf('mysql.service'));
//        DbManager::getInstance()->addConnection(new Connection($config));
//        $redisConfig1 = new \EasySwoole\Redis\Config\RedisConfig(SettingConfig::getInstance()->getConf('redis.cache'));
//        \EasySwoole\Pool\Manager::getInstance()->register(new \App\Pool\RedisPool($config, $redisConfig1), 'cache');
//        // 载入Config文件夹中的配置文件
//        Common::loadConf();

        // 载入Config文件夹中的配置文件
        Common::loadConf();
        // 注册 Redis 连接池
        Common::registerRedisPool('cache');

    }

    public static function mainServerCreate(EventRegister $register)
    {
        //助手函数
        require_once "App/Common/function.php";

        self::hotReload();
//        $register->add($register::onWorkerStart,function (){
//            // 链接预热
//            // ORM 1.4.31 版本之前请使用 getClientPool()
//            // DbManager::getInstance()->getConnection()->getClientPool()->keepMin();
//
//            DbManager::getInstance()->getConnection()->__getClientPool()->keepMin();
//        });
//        $config = new Config();
//        $config->setDatabase(SettingConfig::getInstance()->getConf('mysql.service.database'));
//        $config->setUser(SettingConfig::getInstance()->getConf('mysql.service.host'));
//        $config->setPassword(SettingConfig::getInstance()->getConf('mysql.service.password'));
//        $config->setHost(SettingConfig::getInstance()->getConf('mysql.service.host'));
//        $config->setTimeout(SettingConfig::getInstance()->getConf('mysql.service.timeout')); // 超时时间
//
//        //连接池配置
//        $config->setGetObjectTimeout(SettingConfig::getInstance()->getConf('mysql.service.pool.timeout')); //设置获取连接池对象超时时间
//        $config->setIntervalCheckTime(SettingConfig::getInstance()->getConf('mysql.service.pool.checktime')); //设置检测连接存活执行回收和创建的周期
//        $config->setMaxIdleTime(SettingConfig::getInstance()->getConf('mysql.service.pool.idletime')); //连接池对象最大闲置时间(秒)
//        try{
//            $config->setMinObjectNum(SettingConfig::getInstance()->getConf('mysql.service.pool.maxnum')); //设置最小连接池存在连接对象数量
//            $config->setMaxObjectNum(SettingConfig::getInstance()->getConf('mysql.service.pool.minnum')); //设置最大连接池存在连接对象数量
//        }catch (\Exception $e){
//        }
//
//        $config->setAutoPing(SettingConfig::getInstance()->getConf('mysql.service.pool.autoping')); //设置自动ping客户端链接的间隔

//        DbManager::getInstance()->addConnection(new Connection($config));

//        // 设置指定连接名称 后期可通过连接名称操作不同的数据库
//        DbManager::getInstance()->addConnection(new Connection($config),'write');

//        $processConfig = new \EasySwoole\Component\Process\Config([
//            'processName' => 'TestProcess', // 设置 进程名称为 TickProcess
//        ]);
//
//        // 【推荐】使用 \EasySwoole\Component\Process\Manager 类注册自定义进程
//        $testProcess1 = new \App\Process\TestProcess($processConfig);
//        $testProcess2 = new \App\Process\TestProcess($processConfig);
//
//        ### 正确的注册进程的示例：重新使用 new 实例化另外 1 个新的自定义进程对象，然后进行注册
//        // 注册进程
//        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($testProcess1);
//        \EasySwoole\Component\Process\Manager::getInstance()->addProcess($testProcess2);
    }



    public static function onRequest(Request $request, Response $response): bool
    {
        // 跨域
        $response->withHeader('Content-Type', 'text/html; charset=utf-8');
        $response->withHeader('Access-Control-Allow-Origin', '*');
        $response->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, OPTIONS, DELETE');
        $response->withHeader('Access-Control-Allow-Credentials', 'true');
        $response->withHeader('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
        if ($request->getMethod() === 'OPTIONS') {
            $response->withStatus(200);
            return false;
        }

        // 设置请求的参数
        $request->withAttribute('request_time', microtime(true));

        return true;
    }

    public static function afterRequest(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
    }

    /**
     * @desc 代码热加载
     * @author Huangbin <huangbin2018@qq.com>
     */
    protected static function hotReload()
    {
        $hotReloadOptions = new \EasySwoole\HotReload\HotReloadOptions;
        // 虚拟机中可以关闭Inotify检测
        $hotReloadOptions->disableInotify(true);
        // 可以设置多个监控目录的绝对路径
        $hotReloadOptions->setMonitorFolder([dirname(__FILE__) . '/App']);
        // 忽略某些后缀名不去检测
        $hotReloadOptions->setIgnoreSuffix(['log', 'txt']);
        // 自定义检测到变更后的事件
        $hotReloadOptions->setReloadCallback(function (\Swoole\Server $server) {
            echo "File change event triggered" . PHP_EOL;  // 可以执行如清理临时目录等逻辑
            $server->reload();  // 接管变更事件 需要自己执行重启
        });
        $hotReload = new \EasySwoole\HotReload\HotReload($hotReloadOptions);
        $hotReloadOptions->setMonitorFolder([EASYSWOOLE_ROOT . '/App']);
        $server = ServerManager::getInstance()->getSwooleServer();
        $hotReload->attachToServer($server);
    }
}