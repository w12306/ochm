<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use Illuminate\Contracts\Routing\ResponseFactory;

class Controller extends BaseController
{
	public function __construct()
	{
	}

	public function successReturn($data=[],$msg=''){
		$return=[
				"code"=>0,
				"data"=>$data
		];
		return  response()->json($return);
	}

	public function errorReturn($code=-1,$msg='',$data=[]){
		$return=[
				"code"=>$code,
				"data"=>$data,
				"msg"=>$msg
		];
		return response()->json($return);
	}

}
