<?php namespace App\Exceptions;

use Common\Exceptions\BusinessException as BaseBusinessException;
use App\Services\AdminMailer;

/**
 * 接口文件中的异常处理 抛出指定的json格式
 *
 * @author AaronLiu <liukan0926@stnts.com>
 * @package App\Exceptions
 */
class ApiException extends BaseBusinessException
{
	public function __construct($message,$code,$sendEmail=false){
        $str=json_encode(['info'=>$message,'status'=>$code]);
        if($sendEmail){
            $mail=new AdminMailer();
            $mail->sendErrorIndex("接口发生错误！",$message,'接口错误警告');
        }
		echo  $this->decodeUnicode($str);exit;
	}

    /**
     *处理json转码 将Unicode 转换成汉字
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $str
     * @return mixed
     */
    function decodeUnicode($str){
        return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches',
            'return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));'), $str);
    }

}