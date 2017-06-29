/******************************************************
* XT800 Remote Support
* Copyright 2012-2013 CTong. All rights reserved.
* http://www.xt800.cn
******************************************************/

/**
 * XT800远控组件监听的本地端口
 */
var XT_Local_URI = "http://127.0.0.1:21899/xts";

/**
 * 组件的最低版本，低于此版本，会提示用户升级
 */
var XT_MIN_VERSION = "3.0.4";


/**
 * 远程控制指定计算机
 * 
 * @toId 受控端ID
 * @authCode 验证码
 * @signURL 服务器端的签名接口地址
 * @onErrorTips 出现异常时的回调接口
 * @onInstallTips 未安装组件时的回调接口
 */
function xtRemote(toId, authCode, viewonly, signURL, onErrorTips, onInstallTips) {

	// 启动或检查组件运行状态
	xtStartup(function(data) {
		// 组件处理正常运行状态，执行签名
		var fromId = data.uid; // 主控id
		var token = data.token; // 取得远程组件的令牌
		
		$.jsonp({
			url: signURL,
			callbackParameter:"callback",
			callback: "xtSign",
			data: {from:fromId, to:toId, authcode:authCode, token:token},
			success: function (data, status) {
				if (data != null && data.ret == 0) {
					// 从服务器获得签名，执行 远程控制
					var sign = data.sign;
					$.jsonp({
						url: XT_Local_URI,
						callbackParameter:"callback",
						data: {opt:"remoteControl", from:fromId, to:toId, authcode:authCode, sign:sign, viewonly:viewonly},
						success: function (data, status) {
							if (data != null && data.ret == 0) {
								// ok..
							} else {
								onErrorTips(data);
							}
						},
						error: function (xOptions, status){
						
							onInstallTips(); 
						}
					}); // remote control end!
					
				} else {
					
					onErrorTips(data);
				}
			},
			error: function (xOptions, status){
				
				var data = eval("(" + '{ret:1,msg:"Session已过期，请重新登录"}' + ")");
				onErrorTips(data);
			}
		}); // sign end!
		
	}, onErrorTips, onInstallTips); // startup end!
}


/**
 * 启动或初始化XT800远控组件
 * 
 * @onSucceed 启动成功时的回调
 * @onErrorTips 当启动异常时会触发的回调
 * @onInstallTips 检测到未安装组件时的回调
 */
function xtStartup(onSucceed, onErrorTips, onInstallTips) {
	
	$.jsonp({
		url: XT_Local_URI,
		callbackParameter:"callback",
		data: {opt:"appStartup", minversion:XT_MIN_VERSION},
		success: function (data, status) {
			if (data != null && data.ret == 0) {
				onSucceed(data);
			} else {
				onErrorTips(data);
			}
		},
		error: function (xOptions, status){
		
			onInstallTips(); // 未安装，提示
		}
	});
}

