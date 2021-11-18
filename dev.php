<?php

use EasySwoole\Log\LoggerInterface;

return [
    'SERVER_NAME' => "EasySwoole",
    'MAIN_SERVER' => [
        'LISTEN_ADDRESS' => '0.0.0.0',
        'PORT' => 9909,
        'SERVER_TYPE' => EASYSWOOLE_WEB_SERVER, //可选为 EASYSWOOLE_SERVER  EASYSWOOLE_WEB_SERVER EASYSWOOLE_WEB_SOCKET_SERVER
        'SOCK_TYPE' => SWOOLE_TCP,
        'RUN_MODEL' => SWOOLE_PROCESS,
        'SETTING' => [
            'worker_num' => 8,
            'reload_async' => true,
            'max_wait_time' => 3
        ],
        'TASK' => [
            'workerNum' => 4,
            'maxRunningNum' => 128,
            'timeout' => 15
        ]
    ],
    "LOG" => [
        'dir' => null,
        'level' => LoggerInterface::LOG_LEVEL_DEBUG,
        'handler' => null,
        'logConsole' => true,
        'displayConsole' => true,
        'ignoreCategory' => []
    ],
    'TEMP_DIR' => '/tmp',
    'LOG_DIR' => './Log',
    'DEFAULT_LANG' => 'zh-cn',
    'mysql' => [
//        'host'          => 'bdm769854332.my3w.com',
//        'port'          => 3306,
//        'user'          => 'bdm769854332',
//        'password'      => 'Azhanshengziji9',
        'host'          => '192.168.39.101',
        'port'          => 3306,
        'user'          => 'root',
        'password'      => '123456',
        'database'      => 'bdm769854332_db',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'table_prefix' => '',
        'debug' => true, // 调试，记录 SQL
        'autoPing'      => 5, // 自动 ping 客户端链接的间隔
        'strict_type'   => false, // 不开启严格模式
        'fetch_mode'    => false,
        'returnCollection'  => true, // 设置返回结果为 数组
        // 配置 数据库 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 20, // 设置 连接池最大数量
        'minObjectNum'  => 5, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],
    'redis' => [
        'cache' => [
            'host' => '127.0.0.1',
            'port' => 6379,
            'auth' => '',
            'pool' => [
                'maxnum' => 16, // 最大连接数
                'minnum' => 2, // 最小连接数
                'timeout' => 3, // 获取对象超时时间，单位秒
                'idletime' => 30, // 连接池对象存活时间，单位秒
                'checktime' => 60000, // 多久执行一次回收检测，单位毫秒
            ],
        ],
    ],
];
