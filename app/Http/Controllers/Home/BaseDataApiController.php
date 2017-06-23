<?php
namespace App\Http\Controllers\Home;

use App\Exceptions\ApiException;
use App\Repositories\BusinessRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ProductRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use File;
use Mockery\CountValidator\Exception;
use Validator;
use DB;

/**
 * 后台基础数据对外接口处理类
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class BaseDataApiController extends Controller
{
    use CommonResponse;

    protected $keyStr='Stnts@abmp!~2016';//对外接口的密钥

    protected $timestamp='';//当前时间戳

    protected $appkeys=['adps'];//请求者的appkey

    protected $businessRepository;

    protected $productRepository;

    protected $companyRepository;

    /**
     * 
     */
    public function __construct(BusinessRepository $businessRepository,
                                ProductRepository $productRepository,
                                CompanyRepository $companyRepository)
    {
        $this->timestamp=time();
        $this->businessRepository   =   $businessRepository;
        $this->productRepository    =   $productRepository;
        $this->companyRepository    =   $companyRepository;
    }

    /**
     * 获得后台业务数据
     * 请求对象/签名/链接时间戳/拉取数据的时间节点
     * $updated_at(可选参数  不为空则拉取大于这个时间的所有数据  为空则返回当天的数据)
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getBusiness($appkey,$sign,$timestamp,$updated_at=''){

        $this->requestCheck($appkey,$sign,$timestamp);
        if(empty($updated_at)){
            $updated_at=date('Y-m-d',time());
        }
        $businessData=$this->businessRepository->getBusinessToApi($updated_at);
        return json_encode([
            'status'=>200,
            'info'=>'success',
            'data'=>$businessData,
        ]);
    }

    /**
     * 获得后台上游客户信息
     * 请求对象/签名/链接时间戳/拉取数据的时间节点
     * $updated_at(可选参数  不为空则拉取大于这个时间的所有数据  为空则返回当天的数据)
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getCompanys($appkey,$sign,$timestamp,$updated_at=''){
        $this->requestCheck($appkey,$sign,$timestamp);
        if(empty($updated_at)){
            $updated_at=date('Y-m-d',time());
        }
        $companyData=$this->companyRepository->getCompanyToApi($updated_at);
        return json_encode([
                'status'=>200,
                'info'=>'success',
                'data'=>$companyData,
        ]);
    }

    /**
     * 获得后台产品信息
     * 请求对象/签名/链接时间戳/拉取数据的时间节点
     * $updated_at(可选参数  不为空则拉取大于这个时间的所有数据  为空则返回当天的数据)
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getproducts($appkey,$sign,$timestamp,$updated_at=''){
        $this->requestCheck($appkey,$sign,$timestamp);
        if(empty($updated_at)){
            $updated_at=date('Y-m-d',time());
        }
        $productData=$this->productRepository->getProductToApi($updated_at);
        return json_encode([
                'status'=>200,
                'info'=>'success',
                'data'=>$productData,
        ]);
    }

    /**
     * 请求接口时 校验数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $appkey
     * @param $sign
     * @param $timestamp
     * @param string $updated_at
     * @throws ApiException
     */
    public function requestCheck($appkey,$sign,$timestamp){
        //$this->checkTimesTamp($timestamp);//检测时间戳
        $this->checkSign($sign,$this->madeSign($appkey));//校验数字签名
    }

    /**
     *检测请求的时间戳 三分钟有效
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $timestamp
     * @return bool
     * @throws ApiException
     */
    public function checkTimesTamp($timestamp){
        $timestamp=$timestamp+60*3;
        if($this->timestamp>=$timestamp){
            throw new ApiException('链接失效',401);
        }
        return true;
    }

    /**
     *校验数字签名
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $sign
     * @param $ownSign
     * @return bool
     * @throws ApiException
     */
    public function checkSign($sign,$ownSign){
        if($sign!=$ownSign){
            throw new ApiException('签名错误',406);
        }
        return true;
    }

    /**
     * 生成 SING
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function madeSign($appkey){
        if(!in_array($appkey,$this->appkeys)){
            throw new ApiException('参数不合法',403);//参数不合法
        }
        return strtolower(md5($appkey.$this->keyStr));
    }


}
