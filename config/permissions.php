<?php
	/**
	 * 权限配置
	 * 此表单中的权限用于同步到线上的权限表中，权限名为数组key
	 *
	 * @author AaronLiu <liukan0926@foxmail.com>
	 */


	return [

		/**
		 * 权限列表
		 *
		 * 导入时会遍历所有权限
		 * 展示时会按照以下分组结构展示
		 */
			'groupedPermissions' => [

					'business'         => [
							'name'        => '业务管理',
							'permissions' => [
									[
											'name' => '查看业务信息',
											'slug' => 'business.view',
									],
									[
											'name' => '创建业务',
											'slug' => 'business.create',
									],
									[
											'name' => '编辑业务',
											'slug' => 'business.edit',
									],
									[
											'name' => '删除业务',
											'slug' => 'business.delete',
									],
									[
											'name' => '审核业务',
											'slug' => 'business.audit',
									],
                                    [
                                        'name' => '审核后-业务编辑',
                                        'slug' => 'business.audit-edit',
                                    ],
							]
					],

					'business.amount'  => [
							'name'        => '执行额管理',
							'permissions' => [
									[
											'name' => '增加、修改执行额',
											'slug' => 'business.amount.edit',
									],
									[
											'name' => '删除执行额',
											'slug' => 'business.amount.delete',
									],
							]
					],

					'contract'         => [
							'name'        => '合同管理',
							'permissions' => [
									[
											'name' => '查看合同',
											'slug' => 'contract.view',
									],
									[
											'name' => '创建合同',
											'slug' => 'contract.create',
									],
									[
											'name' => '编辑合同',
											'slug' => 'contract.edit',
									],
									[
											'name' => '合同归档',
											'slug' => 'contract.archive',
									],
							]
					],
					'executive'         => [
							'name'        => '排期管理',
							'permissions' => [
									[
											'name' => '查看执行单',
											'slug' => 'executive.view',
									],
									[
											'name' => '新建执行单',
											'slug' => 'executive.create',
									],
									[
											'name' => '编辑执行单',
											'slug' => 'executive.edit',
									],
									[
											'name' => '删除执行单',
											'slug' => 'executive.delete',
									],
							]
					],
					'advertis'         => [
							'name'        => '广告位管理',
							'permissions' => [
									[
											'name' => '查看广告位',
											'slug' => 'advertis.view',
									],
									[
											'name' => '新建广告位',
											'slug' => 'advertis.create',
									],
									[
											'name' => '编辑广告位',
											'slug' => 'advertis.edit',
									],
									[
											'name' => '删除广告位',
											'slug' => 'advertis.delete',
									],
							]
					],

					'invoice'          => [
							'name'        => '发票管理',
							'permissions' => [
									[
											'name' => '查看发票',
											'slug' => 'invoice.view',
									],
									[
											'name' => '录入发票',
											'slug' => 'invoice.create',
									],
									[
											'name' => '编辑发票',
											'slug' => 'invoice.edit',
									],
									[
											'name' => '删除发票',
											'slug' => 'invoice.delete',
									],
							]
					],

					'backcash'         => [
							'name'        => '回款管理',
							'permissions' => [
									[
											'name' => '查看回款',
											'slug' => 'backcash.view',
									],
									[
											'name' => '录入回款',
											'slug' => 'backcash.create',
									],
									[
											'name' => '编辑回款',
											'slug' => 'backcash.edit',
									],
									[
											'name' => '删除回款',
											'slug' => 'backcash.delete',
									],
							]
					],

					'expenses'         => [
							'name'        => '支出管理',
							'permissions' => [
									[
											'name' => '查看支出',
											'slug' => 'expenses.view',
									],
                                    [
                                            'name' => '查看独立支出',
                                            'slug' => 'stand-expenses.view',
                                    ],
									[
											'name' => '录入支出',
											'slug' => 'expenses.create',
									],
									[
											'name' => '编辑支出',
											'slug' => 'expenses.edit',
									],
									[
											'name' => '删除支出',
											'slug' => 'expenses.delete',
									],
							]
					],

					'payment' => [
							'name'        => '付款管理',
							'permissions' => [
									[
											'name' => '查看付款',
											'slug' => 'payment.view',
									],
                                     [
                                            'name' => '查看独立付款',
                                            'slug' => 'stand-payment.view',
                                    ],
									[
											'name' => '录入付款',
											'slug' => 'payment.create',
									],
									[
											'name' => '编辑付款',
											'slug' => 'payment.edit',
									],
									[
											'name' => '删除付款',
											'slug' => 'payment.delete',
									],
							]
					],

					'badcash'          => [
							'name'        => '坏账管理',
							'permissions' => [
									[
											'name' => '查看坏账',
											'slug' => 'badcash.view',
									],
									[
											'name' => '新增坏账',
											'slug' => 'badcash.create',
									],
									[
											'name' => '修改坏账',
											'slug' => 'badcash.edit',
									],
									[
											'name' => '删除坏账',
											'slug' => 'badcash.delete',
									],
							]
					],

					'earnestcash'      => [
							'name'        => '保证金管理',
							'permissions' => [
									[
											'name' => '查看保证金',
											'slug' => 'earnestcash.view',
									],
									[
											'name' => '录入保证金',
											'slug' => 'earnestcash.create',
									],
									[
											'name' => '编辑保证金',
											'slug' => 'earnestcash.edit',
									],
									[
											'name' => '删除保证金',
											'slug' => 'earnestcash.delete',
									],
									[
											'name' => '保证金抵款',
											'slug' => 'earnestcash.mortgage',
									],
									[
											'name' => '保证金退款',
											'slug' => 'earnestcash.refund',
									],
							]
					],
					'advancecash'      => [
							'name'        => '预收款管理',
							'permissions' => [
									[
											'name' => '查看预收款',
											'slug' => 'advancecash.view',
									],
									[
											'name' => '录入预收款',
											'slug' => 'advancecash.create',
									],
									[
											'name' => '编辑预收款',
											'slug' => 'advancecash.edit',
									],
									[
											'name' => '删除预收款',
											'slug' => 'advancecash.delete',
									],
									[
											'name' => '预收款抵款',
											'slug' => 'advancecash.mortgage',
									],
									[
											'name' => '预收款退款',
											'slug' => 'advancecash.refund',
									],
							]
					],



					'tool'             => [
							'name'        => '工具箱管理',
							'permissions' => [
									[
											'name' => '查看产品',
											'slug' => 'tool.product.view',
									],
									[
											'name' => '新增产品',
											'slug' => 'tool.product.create',
									],
									[
											'name' => '编辑产品',
											'slug' => 'tool.product.edit',
									],
									[
											'name' => '查看客户',
											'slug' => 'tool.company.view',
									],
									[
											'name' => '新增客户',
											'slug' => 'tool.company.create',
									],
									[
											'name' => '编辑客户',
											'slug' => 'tool.company.edit',
									],
									[
											'name' => '查看合作方',
											'slug' => 'tool.partner.view',
									],
									[
											'name' => '新增合作方',
											'slug' => 'tool.partner.create',
									],
									[
											'name' => '编辑合作方',
											'slug' => 'tool.partner.edit',
									],
									[
											'name' => '查看下游客户',
											'slug' => 'tool.undercompany.view',
									],
									[
											'name' => '新增下游客户',
											'slug' => 'tool.undercompany.create',
									],
									[
											'name' => '编辑下游客户',
											'slug' => 'tool.undercompany.edit',
									],
									[
											'name' => '查看下游合作方',
											'slug' => 'tool.underpartner.view',
									],
									[
											'name' => '新增下游合作方',
											'slug' => 'tool.underpartner.create',
									],
									[
											'name' => '编辑下游合作方',
											'slug' => 'tool.underpartner.edit',
									],
									[
											'name' => '数据项查看',
											'slug' => 'tool.dictionary.view',
									],
									[
											'name' => '添加数据项',
											'slug' => 'tool.dictionary.create',
									],
									[
											'name' => '编辑数据项',
											'slug' => 'tool.dictionary.edit',
									],
							]
					],

					'permission'       => [
							'name'        => '权限管理',
							'permissions' => [
									[
											'name' => '查看管理员信息',
											'slug' => 'permission.admin.view',
									],
									[
											'name' => '编辑管理员',
											'slug' => 'permission.admin.edit',
									],
									[
											'name' => '查看权限角色列表',
											'slug' => 'permission.role.view',
									],
									[
											'name' => '添加权限角色',
											'slug' => 'permission.role.create',
									],
									[
											'name' => '编辑权限角色',
											'slug' => 'permission.role.edit',
									],
									[
											'name' => '删除权限角色',
											'slug' => 'permission.role.delete',
									],
							]
					],

					'admin-log'        => [
							'name'        => '操作日志',
							'permissions' => [
									[
											'name' => '查看操作日志',
											'slug' => 'admin.action.log.view',
									],
							]
					],

					'statistics'        => [
							'name'        => '数据统计',
							'permissions' => [
									[
											'name' => '业务执行金额总表',
											'slug' => 'statistics.business.businessaction',
									],
									[
											'name' => '业务财务信息总表',
											'slug' => 'statistics.business.financeinfo',
									],

									[
											'name' => '业务法务信息总表',
											'slug' => 'statistics.business.businesslegal',
									],
									[
											'name' => '业务月利润统计',
											'slug' => 'statistics.business.businessprofit',
									],
									[
											'name' => '财务统计基础表',
											'slug' => 'statistics.business.financialbase',
									],
									[
											'name' => '财务年度基础统计',
											'slug' => 'statistics.business.financialyearbase',
									],
							]
					],
			],
		/**
		 * 默认导入的角色权限
		 * 导入之后会关联所有系统中存在的用户
		 */
			'defaultRoles'       => [
					[
							'name'        => '超级管理员',
							'slug'        => 'super',
							'permissions' => [
									'permission.admin.view',
									'permission.admin.edit',
									'permission.role.view',
									'permission.role.create',
									'permission.role.edit',
									'permission.role.delete',
							]
					],

			],

	];