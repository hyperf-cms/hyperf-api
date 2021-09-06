<?php

return [

    [
        "dict_name"  => "用户性别",
        "dict_type"  => "sys_user_sex",
        "remark"     => "用户性别列表",
        "status"     => "1",
        "dict_data"  => [

            [
                "dict_sort"  => "1",
                "dict_label" => "男",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "性别男",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "女",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "性别女",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "未知",
                "dict_value" => "2",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "性别未知",
            ],

        ]
    ],

    [
        "dict_name"  => "用户状态",
        "dict_type"  => "sys_user_status",
        "remark"     => "用户状态列表",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "启动",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "用户启动状态",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "禁用",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "用户禁用状态",
            ],

        ]
    ],

    [
        "dict_name"  => "权限状态",
        "dict_type"  => "sys_permission_status",
        "remark"     => "权限状态列表",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "启用",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "权限启用状态",
            ],
            
            [
                "dict_sort"  => "1",
                "dict_label" => "禁用",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "权限禁用状态",
                "dict_data" => "2020-6-10 00:00:00",
            ],
    
        ]
    ],

    [
        "dict_name"  => "权限隐藏是否",
        "dict_type"  => "sys_permission_hidden",
        "remark"     => "权限是否隐藏",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "是",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "隐藏权限",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "否",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "不隐藏权限",
            ],

        ]
    ],

    [
        "dict_name"  => "权限类型",
        "dict_type"  => "sys_permission_type",
        "remark"     => "权限类型列表",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "菜单",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "菜单权限类型",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "按钮",
                "dict_value" => "2",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "按钮权限类型",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "接口",
                "dict_value" => "3",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "接口权限类型",
            ],
    
        ]
    ],

    [
        "dict_name"  => "系统建议类型",
        "dict_type"  => "sys_advice_type",
        "remark"     => "系统建议类型",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "bug",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "bug类型",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "优化",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "优化类型",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "混合",
                "dict_value" => "2",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "混合类型",
            ],
    
        ]
    ],

    [
        "dict_name"  => "系统建议状态",
        "dict_type"  => "sys_advice_status",
        "remark"     => "系统建议状态",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "待解决",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "待解决状态",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "已解决",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "已解决状态",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "关闭",
                "dict_value" => "2",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "关闭状态",
            ],

        ]
    ],

    [
        "dict_name"  => "通知管理状态",
        "dict_type"  => "sys_notice_status",
        "remark"     => "通知管理的状态枚举",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "未发布",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "未发布状态",
            ],

            [
                "dict_sort"  => "1",
                "dict_label" => "已发布",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "已发布状态",
            ],

        ]
    ],

    [
        "dict_name"  => "相册状态",
        "dict_type"  => "blog_album_status",
        "remark"     => "相册的启动状态",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "启动",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "相册启动状态",
            ],

            [
                "dict_sort"  => "2",
                "dict_label" => "禁用",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "相册禁用状态",
            ],

        ]
    ],

    [
        "dict_name"  => "相册类型",
        "dict_type"  => "blog_album_type",
        "remark"     => "相册类型枚举",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "普通相册",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "普通相册",
            ],

            [
                "dict_sort"  => "2",
                "dict_label" => "密码相册",
                "dict_value" => "2",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "密码相册",
            ],

        ]
    ],

    [
        "dict_name"  => "定时任务状态",
        "dict_type"  => "sys_timed_task_status",
        "remark"     => "定时任务的状态枚举",
        "status"     => "1",
        "dict_data" => [

            [
                "dict_sort"  => "1",
                "dict_label" => "启用",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "启用状态",
            ],
            
            [
                "dict_sort"  => "1",
                "dict_label" => "禁用",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "禁用状态",
            ],

        ]
    ],

    [
        "dict_name"  => "权限是否外链",
        "dict_type"  => "sys_permission_is_link",
        "remark"     => "菜单是否外链",
        "status"     => "1",
        "dict_data"  => [

            [
                "dict_sort"  => "1",
                "dict_label" => "是",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "是外链",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "否",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "不是外链",
            ],

        ]
    ],

    [
        "dict_name"  => "参数设置类型枚举",
        "dict_type"  => "sys_global_config_type",
        "remark"     => "参数设置模块的类型相关枚举",
        "status"     => "1",
        "dict_data"  => [

            [
                "dict_sort"  => "1",
                "dict_label" => "文本",
                "dict_value" => "text",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "文本类型(string, int)",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "布尔值",
                "dict_value" => "boolean",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "布尔值类型",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "HTML",
                "dict_value" => "html",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "HTML格式",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "JSON",
                "dict_value" => "json",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "json格式",
            ]

        ]
    ],

    [
        "dict_name"  => "Up主定时统计开关状态",
        "dict_type"  => "lab_up_user_time_status",
        "remark"     => "bilibili助手Up主定时统计开关枚举",
        "status"     => "1",
        "dict_data"  => [

            [
                "dict_sort"  => "1",
                "dict_label" => "开启",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "开启状态",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "关闭",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "0",
                "status"     => "1",
                "remark"     => "关闭状态",
            ]

        ]
    ],

    [
        "dict_name"  => "视频定时统计开关状态",
        "dict_type"  => "lab_video_time_status",
        "remark"     => "Bilibili 视频定时统计开关状态",
        "status"     => "1",
        "dict_data"  => [

            [
                "dict_sort"  => "1",
                "dict_label" => "开启",
                "dict_value" => "1",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "1",
                "status"     => "1",
                "remark"     => "开启状态",
            ],
            [
                "dict_sort"  => "1",
                "dict_label" => "关闭",
                "dict_value" => "0",
                "css_class"  => "",
                "list_class" => "",
                "is_default" => "0",
                "status"     => "1",
                "remark"     => "关闭状态",
            ]

        ]
    ],

];