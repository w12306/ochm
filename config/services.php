<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, Mandrill, and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => '',
        'secret' => '',
    ],

    'mandrill' => [
        'secret' => '',
    ],

    'ses' => [
        'key'    => '',
        'secret' => '',
        'region' => 'us-east-1',
    ],

    'stripe'        => [
        'model'  => App\Models\AdminUser::class,
        'key'    => '',
        'secret' => '',
    ],

    /*
	|--------------------------------------------------------------------------
	| UAMS配置
	|--------------------------------------------------------------------------
	|
	*/
    'uams'          => [
        //APP ID
        'app_id'         => env('UAMS_APP_ID'),
        //APP KEY
        'app_key'        => env('UAMS_APP_KEY'),
        //API请求的基本地址，不带结尾的“/”，例如：“https://api.uams.tnt.com”
        'api_url_prefix' => env('UAMS_API_URL_PREFIX'),
        //UAMS的登陆地址
        'login_url'      => env('UAMS_LOGIN_URL'),
    ],

    /*
    |--------------------------------------------------------------------------
    | 管理员认证
    |--------------------------------------------------------------------------
    |
    */
    'admin'         => [
        //管理员用户模型
        'model' => \App\Models\AdminUser::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | ADPS JAVA接口
    |--------------------------------------------------------------------------
    |
    */
    'adps_java_api' => [
        'base' => 'http://192.168.2.99:8082',
    ],

    /*
    |--------------------------------------------------------------------------
    | 管理员操作日志格式配置
    |--------------------------------------------------------------------------
    |
    */
    'actionLog'     => [
        /**
         * 功能模块
         * 这里的配置会显示在“管理员操作记录”页面的“功能模块”下拉中，同时日志格式也会关联
         * 到某一个功能模块（指定到模块的数组KEY，这个KEY也是数据库中module字段的值）。
         */
        'modules'  => [
            'business'         => [
                'name' => '业务管理',
            ],
            'delivery'         => [
                'name' => '执行额管理',
            ],
            'contract'         => [
                'name' => '合同管理',
            ],
            'invoice'          => [
                'name' => '发票管理',
            ],
            'backcash'         => [
                'name' => '回款管理',
            ],
            'expenses'         => [
                'name' => '支出管理',
            ],
            'stand-expenses'   => [
                'name' => '独立支出管理',
            ],
            'payment-expenses' => [
                'name' => '支出付款管理',
            ],
            'stand-payment-expenses' => [
                'name' => '独立支出付款管理',
            ],
            'badcash'          => [
                'name' => '坏账管理',
            ],
            'earnestcash'      => [
                'name' => '保证金管理',
            ],
            'advancecash'      => [
                'name' => '预收款管理',
            ],
            'product'          => [
                'name' => '产品管理',
            ],
            'company'          => [
                'name' => '客户管理',
            ],
            'under-company'    => [
                'name' => '下游客户管理',
            ],
            'partner'          => [
                'name' => '合作方管理',
            ],
            'under-partner'    => [
                'name' => '下游合作方管理',
            ],
            'permission-role'  => [
                'name' => '权限角色管理',
            ],
            'admin-user'       => [
                'name' => '用户管理',
            ],
        ],

        /**
         * 名词对照表
         * 日志中的英文变量名会查询指定的名词对照表映射为中文名称，如果不存在则保留英文
         * 变量名。
         */
        'termSets' => [
            'business'       => [
                'business_key' => '业务编号',
            ],
            'contract'       => [
                'id'           => '合同ID',
                'ckey'         => '合同编号',
                'business_key' => '业务编号',
            ],
            'stand-expenses' => [
                'id' => '独立支出ID',
            ],
            'invoice'       => [
                'amount' => '发票金额',
            ],
            'admin-user'     => [
                'real_name' => '真实姓名',
            ],
        ],

        /**
         * 日志格式
         * 数组KEY为调用ActionLog->log方法必须带上的$formatName参数；
         * module是日志对应的功能模块；
         * termSet是日志对应的名词对照表；
         * mainIdKey是日志操作对象的主要ID；
         * message是日志的简要信息内容；
         */
        'formats'  => [
            //业务 -------------------------------------------------------------

            //创建业务
            'business.create'         => [
                'module'       => 'business',
                'termSet'      => 'business',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '创建新业务 业务编号：{business_key}',
            ],
            //编辑业务
            'business.edit'           => [
                'module'       => 'business',
                'termSet'      => 'business',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '修改业务信息 业务编号：{business_key}',
            ],
            //删除业务
            'business.delete'         => [
                'module'       => 'business',
                'termSet'      => 'business',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '删除业务信息',
            ],
            //审核业务
            'business.audit'          => [
                'module'       => 'business',
                'termSet'      => 'business',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '{action_text}',
            ],

            //执行额 -----------------------------------------------------------

            //增加执行额
            'delivery.create'         => [
                'module'       => 'delivery',
                'termSet'      => 'delivery',
                'mainIdKey'    => 'id',
                'message'      => '业务[{business_key}]:新增{month}执行额 执行金额：{amount}',
            ],
            //修改执行额
            'delivery.edit'           => [
                'module'       => 'delivery',
                'termSet'      => 'delivery',
                'mainIdKey'    => 'id',
                'message'      => '修改 业务[{business_key}]的{month}月执行额',
            ],
            //删除执行额
            'delivery.delete'         => [
                'module'       => 'delivery',
                'termSet'      => 'delivery',
                'mainIdKey'    => 'id',
                'message'      => '删除 业务[{business_key}]的{month}月执行额',
            ],

            //合同 -------------------------------------------------------------

            //创建合同
            'contract.create'         => [
                'module'       => 'contract', //对应的功能模块
                'termSet'      => 'contract',
                'mainIdKey'    => 'id',
                //'companyIdKey' => 'company_id',
                'message'      => '创建新合同 合同号：{ckey}',
            ],
            //编辑合同
            'contract.edit'           => [
                'module'       => 'contract',
                'termSet'      => 'contract',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '修改合同信息',
            ],
            //合同归档
            'contract.archive'        => [
                'module'       => 'contract',
                'termSet'      => 'contract',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '合同已归档',
            ],

            //发票 -----------------------------------------------------------

            //录入发票
            'invoice.archive'         => [
                'module'       => 'invoice',
                'termSet'      => 'invoice',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '录入发票 发票金额：{amount}',
            ],
            //编辑发票
            'invoice.edit'            => [
                'module'       => 'invoice',
                'termSet'      => 'invoice',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '修改发票信息',
            ],
            //删除发票
            'invoice.delete'          => [
                'module'       => 'invoice',
                'termSet'      => 'invoice',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '删除发票',
            ],

            //回款 -----------------------------------------------------------

            //录入回款
            'backcash.create'         => [
                'module'       => 'backcash',
                'termSet'      => 'backcash',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '录入回款 回款金额{amount}',
            ],
            //编辑回款
            'backcash.edit'           => [
                'module'       => 'backcash',
                'termSet'      => 'backcash',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '修改回款信息',
            ],
            //删除回款
            'backcash.delete'         => [
                'module'       => 'backcash',
                'termSet'      => 'backcash',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '删除回款信息',
            ],

            //支出 -----------------------------------------------------------

            //录入支出
            'expenses.create'         => [
                'module'       => 'expenses',
                'termSet'      => 'expenses',
                'mainIdKey'    => 'id',
                'message'      => '录入支出 支出金额：{amount}',
            ],
            //编辑支出
            'expenses.edit'           => [
                'module'       => 'expenses',
                'termSet'      => 'expenses',
                'mainIdKey'    => 'id',
                'message'      => '修改支出单信息',
            ],
            //删除支出
            'expenses.delete'         => [
                'module'       => 'expenses',
                'termSet'      => 'expenses',
                'mainIdKey'    => 'id',
                'message'      => '删除支出单',
            ],
            //独立支出----------------------------------------------------------
            //录入支出
            'stand-expenses.create'   => [
                'module'       => 'stand-expenses',
                'termSet'      => 'stand-expenses',
                'mainIdKey'    => 'id',
                'message'      => '录入独立支出 支出金额：{amount}',
            ],
            //编辑支出
            'stand-expenses.edit'           => [
                        'module'       => 'stand-expenses',
                        'termSet'      => 'stand-expenses',
                        'mainIdKey'    => 'id',
                        'message'      => '修改独立支出信息',
            ],
            //删除支出
            'stand-expenses.delete'         => [
                        'module'       => 'stand-expenses',
                        'termSet'      => 'stand-expenses',
                        'mainIdKey'    => 'id',
                        'message'      => '删除独立支出',
            ],
            //支出付款 ---------------------------------------------------------

            //录入付款
            'payment-expenses.create' => [
                'module'       => 'payment-expenses',
                'termSet'      => 'payment-expenses',
                'mainIdKey'    => 'id',
                'message'      => '录入付款 付款金额：{amount}',
            ],
            //编辑付款
            'payment-expenses.edit'   => [
                'module'       => 'payment-expenses',
                'termSet'      => 'payment-expenses',
                'mainIdKey'    => 'id',
                'message'      => '修改付款信息',
            ],
            //删除付款
                'payment-expenses.delete' => [
                        'module' => 'payment-expenses',
                        'termSet' => 'payment-expenses',
                        'mainIdKey' => 'id',
                        'message' => '删除付款',
                ],
            //独立支出付款 ---------------------------------------------------------

            //录入付款
                'stand-payment-expenses.create' => [
                        'module' =>  'stand-payment-expenses',
                        'termSet' => 'stand-payment-expenses',
                        'mainIdKey' => 'id',
                        'message' => '录入独立付款 付款金额：{amount}',
                ],
            //编辑付款
                'stand-payment-expenses.edit' => [
                        'module'  => 'stand-payment-expenses',
                        'termSet' => 'stand-payment-expenses',
                        'mainIdKey' => 'id',
                        'message' => '修改独立付款信息',
                ],
            //删除付款
                'stand-payment-expenses.delete' => [
                        'module'  => 'stand-payment-expenses',
                        'termSet' => 'stand-payment-expenses',
                        'mainIdKey' => 'id',
                        'message' => '删除独立付款',
                ],

            //坏账 -----------------------------------------------------------

            //新增坏账
                'badcash.create' => [
                        'module' => 'badcash',
                        'termSet' => 'badcash',
                        'mainIdKey' => 'id',
                        'message' => '新增坏账 坏账金额：{amount}',
                ],
            //修改坏账
            'badcash.edit'            => [
                'module'       => 'badcash',
                'termSet'      => 'badcash',
                'mainIdKey'    => 'id',
                'message'      => '修改坏账信息 坏账金额：{amount}',
            ],
            //删除坏账
            'badcash.delete'          => [
                'module'       => 'badcash',
                'termSet'      => 'badcash',
                'mainIdKey'    => 'id',
                'message'      => '删除坏账',
            ],

            //保证金 -----------------------------------------------------------

            //录入保证金
            'earnestcash.create'      => [
                'module'       => 'earnestcash',
                'termSet'      => 'earnestcash',
                'mainIdKey'    => 'id',
                'message'      => '录入保证金 保证金金额：{amount}',
            ],
            //编辑保证金
            'earnestcash.edit'        => [
                'module'       => 'earnestcash',
                'termSet'      => 'earnestcash',
                'mainIdKey'    => 'id',
                'message'      => '修改保证金信息',
            ],
            //删除保证金
            'earnestcash.delete'          => [
                        'module'       => 'earnestcash',
                        'termSet'      => 'earnestcash',
                        'mainIdKey'    => 'id',
                        'message'      => '删除保证金',
            ],
            //保证金抵款
            'earnestcash.deduct'      => [
                'module'       => 'earnestcash',
                'termSet'      => 'earnestcash',
                'mainIdKey'    => 'id',
                'message'      => '保证金抵款 抵款金额：{amount}',
            ],
            //保证金退款
            'earnestcash.refund'      => [
                'module'       => 'earnestcash',
                'termSet'      => 'earnestcash',
                'mainIdKey'    => 'id',
                'message'      => '保证金退款 退款金额：{amount}',
            ],
            //预收款 -----------------------------------------------------------

            //录入预收款
                'advancecash.create'      => [
                        'module'       => 'advancecash',
                        'termSet'      => 'advancecash',
                        'mainIdKey'    => 'id',
                        'message'      => '录入预收款 预收款金额：{amount}',
                ],
            //编辑预收款
                'advancecash.edit'        => [
                        'module'       => 'advancecash',
                        'termSet'      => 'advancecash',
                        'mainIdKey'    => 'id',
                        'message'      => '修改预收款信息',
                ],
            //删除预收款
                'advancecash.delete'          => [
                        'module'       => 'advancecash',
                        'termSet'      => 'advancecash',
                        'mainIdKey'    => 'id',
                        'message'      => '删除预收款',
                ],
            //预收款抵款
                'advancecash.deduct'      => [
                        'module'       => 'advancecash',
                        'termSet'      => 'advancecash',
                        'mainIdKey'    => 'id',
                        'message'      => '预收款抵款 抵款金额：{amount}',
                ],
            //预收款退款
                'advancecash.refund'      => [
                        'module'       => 'advancecash',
                        'termSet'      => 'advancecash',
                        'mainIdKey'    => 'id',
                        'message'      => '预收款退款 退款金额：{amount}',
                ],

            //产品 -----------------------------------------------------------

            //新增产品
            'product.create'          => [
                'module'       => 'product',
                'termSet'      => 'product',
                'mainIdKey'    => 'id',
                'message'      => '新增产品 产品名称：{pname}',
            ],
            //修改产品
            'product.edit'            => [
                'module'       => 'product',
                'termSet'      => 'product',
                'mainIdKey'    => 'id',
                'message'      => '修改产品信息',
            ],

            //客户 -----------------------------------------------------------

            //新增客户
            'company.create'          => [
                'module'       => 'company',
                'termSet'      => 'company',
                'mainIdKey'    => 'id',
                'message'      => '新增客户 客户名称：{company_name}',
            ],
            //编辑客户
            'company.edit'            => [
                'module'       => 'company',
                'termSet'      => 'company',
                'mainIdKey'    => 'id',
                'message'      => '修改客户信息',
            ],

            //下游客户 ---------------------------------------------------------

            //新增下游客户
            'under-company.create'    => [
                'module'       => 'under-company',
                'termSet'      => 'under-company',
                'mainIdKey'    => 'id',
                'message'      => '新增下游客户 下游客户名称：{company_name}',
            ],
            //编辑下游客户
            'under-company.edit'      => [
                'module'       => 'under-company',
                'termSet'      => 'under-company',
                'mainIdKey'    => 'id',
                'message'      => '修改下游客户信息',
            ],

            //合作方 -----------------------------------------------------------

            //新增合作方
            'partner.create'          => [
                'module'       => 'partner',
                'termSet'      => 'partner',
                'mainIdKey'    => 'id',
                'message'      => '新增合作方 合作方名称：{company_name}',
            ],
            //编辑合作方
            'partner.edit'            => [
                'module'       => 'partner',
                'termSet'      => 'partner',
                'mainIdKey'    => 'id',
                'message'      => '修改合作方信息',
            ],

            //下游合作方 -------------------------------------------------------

            //新增下游合作方
            'under-partner.create'    => [
                'module'       => 'under-partner',
                'termSet'      => 'under-partner',
                'mainIdKey'    => 'id',
                'message'      => '新增下游合作方 下游合作方名称：{company_name}',
            ],
            //编辑下游合作方
            'under-partner.edit'      => [
                'module'       => 'under-partner',
                'termSet'      => 'under-partner',
                'mainIdKey'    => 'id',
                'message'      => '修改下游合作方信息',
            ],

            //权限角色 ---------------------------------------------------------

            //新增角色
            'permission-role.create'  => [
                'module'       => 'permission-role',
                'termSet'      => 'permission-role',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '新增角色 角色名称：{name}',
            ],
            //编辑角色
            'permission-role.edit'    => [
                'module'       => 'permission-role',
                'termSet'      => 'permission-role',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '修改角色信息',
            ],

            //用户 -----------------------------------------------------------

            //新增用户
            'admin-user.create'       => [
                'module'       => 'admin-user',
                'termSet'      => 'admin-user',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '新增用户 用户名称：{real_name}',
            ],
            //修改用户
            'admin-user.edit'         => [
                'module'       => 'admin-user',
                'termSet'      => 'admin-user',
                'mainIdKey'    => 'id',
                'companyIdKey' => 'company_id',
                'message'      => '修改用户信息',
            ],

        ],
    ],

];
