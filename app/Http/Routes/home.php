<?php
/**
 * 前台路由
 */

Route::group(['prefix' => 'api','namespace' => 'Home'], function () {

	/**获得业务数据 接口
	 * /{appkey}/{sign}/{ddate}/{timestamp}
	 * 参数依次：请求对象/签名/链接时间戳/拉取数据的时间节点/
	 */
	Route::get('get-business/{appkey}/{sign}/{timestamp}/{updated_at?}', [
		'as'         => 'home.api.get-business',
		'uses'       => 'BaseDataApiController@getBusiness',
	]);
	/**
	 * 获得上游客户接口
	 */
	Route::get('get-companys/{appkey}/{sign}/{timestamp}/{updated_at?}', [
			'as'         => 'home.api.get-companys',
			'uses'       => 'BaseDataApiController@getCompanys',
	]);
	/**
	 * 获得产品数据接口
	 */
	Route::get('get-products/{appkey}/{sign}/{timestamp}/{updated_at?}', [
			'as'         => 'home.api.get-products',
			'uses'       => 'BaseDataApiController@getProducts',
	]);

});






