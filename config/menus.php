<?php
/**
 * ABMP菜单配置
 * 第一层为顶部菜单，第二层为左侧菜单header或可点击项目，第三层是左侧菜单可点击的子项目
 *
 * “match”属性是用来计算是否高亮菜单的，有两种格式：
 * 1. 路由规则字符串
 *     例如当前URL的路由别名为“class.user”，那么“match”为“class”或“class.user”都会高亮
 * 2. 正则表达式
 *     当单个路由规则字符串无法满足需求时，例如父级菜单下的两个URL路由别名分别为：“
 *     class.user”和“user.item”，这时两个路由别名中并没有共同的字符串，要高亮父级菜单就
 *     需要使用正则了，例如：“/^(class|user)\..*$/”。
 *
 * @author AaronLiu <liukan0926@foxmail.com>
 */


return [
    'index' => [
        'name'       => '常用功能',
        'route'      => 'admin.executive.list',
        'match'      => '/^admin\.(executive)(\..+)?$/',
        'permission' => '*',

        'children'   => [
            'executive'    => [
                'name'   => '策略管理',
                'match'  => 'admin.executive',
                'permission' => ['any', [
                    'executive.view',
                    'executive.create',
                    'executive.edit',
                    'executive.delete',
                ]],
                'children'   => [
                    'list' => [
                        'name'       => '账户设置',
                        'route'      => 'admin.executive.list',
                        'match'      => 'admin.executive.list',
                        'permission' => 'executive.view',
                    ],
                    'add'  => [
                        'name'       => '系统策略',
                        'route'      => 'admin.executive.create',
                        'match'      => 'admin.executive.create',
                        'permission' => 'executive.create',
                    ],
                    'gather'  => [
                        'name'       => '系统策略-陪标',
                        'route'      => 'admin.executive.gather',
                        'match'      => 'admin.executive.gather',
                        'permission' => 'executive.view',
                    ],
                    'gather2'  => [
                        'name'       => '自定义策略',
                        'route'      => 'admin.executive.gather',
                        'match'      => 'admin.executive.gather',
                        'permission' => 'executive.view',
                    ],
                ]
            ],
            'executive2'    => [
                'name'   => '策略设置',
                'match'  => 'admin.executive',
                'permission' => ['any', [
                    'executive.view',
                    'executive.create',
                    'executive.edit',
                    'executive.delete',
                ]],
                'children'   => [

                    'add'  => [
                        'name'       => '系统策略',
                        'route'      => 'admin.executive.create',
                        'match'      => 'admin.executive.create',
                        'permission' => 'executive.create',
                    ],

                ]
            ],

        ],
    ],


    'config'    => [
        'name'       => '系统管理',
        'route'      => 'admin.config.dictionary.dictionary-list',
        'match'      => '/^admin\.(config|toolbox)(\..+)?$/',
        'permission' => ['any', [
            'tool.dictionary.view',
            'tool.dictionary.create',
            'tool.dictionary.edit',

            'permission.admin.view',
            'permission.admin.edit',
            'permission.role.view',
            'permission.role.create',
            'permission.role.edit',
            'permission.role.delete',

            'admin.action.log.view',
        ]],

        'children'   => [
            'dictionary' => [
                'name'       => '数据项管理',
                'match'      => 'admin.config.dictionary',
                'permission' => 'tool.dictionary.view',

                'children'   => [
                    'dictionary-list' => [
                        'name'       => '数据项列表',
                        'route'      => 'admin.config.dictionary.dictionary-list',
                        'match'      => 'admin.config.dictionary.dictionary-list',
                        'permission' => 'tool.dictionary.view',
                    ],
                ],
            ],

            'permission' => [
                'name'       => '权限管理',
                'match'      => 'admin.config.permission',
                'permission' => ['any', [
                    'permission.admin.view',
                    'permission.admin.edit',
                    'permission.role.view',
                    'permission.role.create',
                    'permission.role.edit',
                    'permission.role.delete',
                ]],

                'children'   => [
                    'admin-user-list' => [
                        'name'       => '管理员列表',
                        'route'      => 'admin.config.permission.admin-user-list',
                        'match'      => 'admin.config.permission.admin-user-list',
                        'permission' => 'permission.admin.view',
                    ],
                    'role.list'       => [
                        'name'       => '角色列表',
                        'route'      => 'admin.config.permission.role-list',
                        'match'      => 'admin.config.permission.role-list',
                        'permission' => 'permission.role.view',
                    ],
                ],
            ],

            'action-log' => [
                'name'       => '历史操作记录',
                'route'      => 'admin.config.action-log.list',
                'match'      => 'admin.config.action-log',
                'permission' => 'admin.action.log.view',

                'children'   => [
                    'list'       => [
                        'name'       => '操作记录列表',
                        'route'      => 'admin.config.action-log.list',
                        'match'      => 'admin.config.action-log.list',
                        'permission' => 'admin.action.log.view',
                    ],
                ],
            ],


        ],
    ],
];
