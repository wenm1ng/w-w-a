<?php
/**
 * @desc       MQ 配置
 * @author     文明<wenming@ecgtool.com>
 * @date       2022-05-06 11:36
 */

return [
    "listing" => [
        "host" => "192.168.39.101",
        "port" => "15672",
        "user" => "guest",
        "pass" => "guest",
        "vhost" => "/",
        "amqp_debug" => false,
        "queue" => [
            "exchange" => "router",
            "queue_name" => "msgs"
        ]
    ]
];