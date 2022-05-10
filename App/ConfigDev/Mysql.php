<?php
/**
 * @desc
 * @author     文明<wenming@ecgtool.com>
 * @date       2021-07-20 23:01
 */
return [
    'product'  => [
        'host'          => '10.10.7.6',
        'port'          => 3306,
        'user'          => 'root',
        'password'      => 'wycIblla5u(;',
        'database'      => 'yixiaobao_test',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'table_prefix' => '',
        'debug' => true, // 调试，记录 SQL
        'pool' => [
            'maxnum' => 1000, // 最大连接数
            'minnum' => 10, // 最小连接数
            'timeout' => 3, // 获取对象超时时间，单位秒
            'idletime' => 30, // 连接池对象存活时间，单位秒
            'checktime' => 10000, // 多久执行一次回收检测，单位毫秒
        ]
    ],
    'service1'  => [
        'host'          => 'bdm769854332.my3w.com',
        'port'          => 3306,
        'user'          => 'bdm769854332',
        'password'      => 'Azhanshengziji9',
        'database'      => 'bdm769854332_db',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'table_prefix' => '',
        'debug' => true, // 调试，记录 SQL
        'autoPing'      => 5, // 自动 ping 客户端链接的间隔
        'strict_type'   => false, // 不开启严格模式
        'fetch_mode'    => false,
        'returnCollection'  => false, // 设置返回结果为 数组
        // 配置 数据库 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 6, // 设置 连接池最大数量
        'minObjectNum'  => 5, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],
    'service'  => [
        'host'          => '192.168.39.101',
        'port'          => 3306,
        'user'          => 'root',
        'password'      => 'wycIblla5u(;',
        'database'      => 'wow',
        'timeout'       => 5,
        'charset'       => 'utf8mb4',
        'table_prefix' => '',
        'debug' => true, // 调试，记录 SQL
        'autoPing'      => 5, // 自动 ping 客户端链接的间隔
        'strict_type'   => false, // 不开启严格模式
        'fetch_mode'    => false,
        'returnCollection'  => false, // 设置返回结果为 数组
        // 配置 数据库 连接池配置，配置详细说明请看连接池组件 https://www.easyswoole.com/Components/Pool/introduction.html
        'intervalCheckTime' => 15 * 1000, // 设置 连接池定时器执行频率
        'maxIdleTime'   => 10, // 设置 连接池对象最大闲置时间 (秒)
        'maxObjectNum'  => 6, // 设置 连接池最大数量
        'minObjectNum'  => 5, // 设置 连接池最小数量
        'getObjectTimeout'  => 3.0, // 设置 获取连接池的超时时间
        'loadAverageTime'   => 0.001, // 设置 负载阈值
    ],
];