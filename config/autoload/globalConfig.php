<?php

return [
    'global_config' => [
        [
            "id"         => "1",
            "type"       => "boolean",
            "key_name"   => "maintain_switch",
            "name"       => "维护模式开关",
            "remark"     => "用来控制系统维护模式是否开启开关 默认为关",
            "data"       => "0",
            "created_at" => "2021-6-17 11:01:19",
            "updated_at" => "2021-6-18 10:28:33"
        ],
        [
            "id"         => "2",
            "type"       => "boolean",
            "key_name"   => "simple_maintain_switch",
            "name"       => "简易维护模式",
            "remark"     => "用来维护系统的简易维护模式开关",
            "data"       => "0",
            "created_at" => "2021-6-17 11:17:34",
            "updated_at" => "2021-6-18 10:28:35"
        ],
        [
            "id"         => "3",
            "type"       => "boolean",
            "key_name"   => "register_switch",
            "name"       => "后台注册入口开关",
            "remark"     => "后台注册入口开关",
            "data"       => "1",
            "created_at" => "2021-6-17 11:18:18",
            "updated_at" => "2021-6-17 20:06:43"
        ]
    ]
];