<?php

namespace App\Http\Controllers\Api;
use App\Http\Controllers\Api\Controller;
use App\Repositories\BusinessRepository;
use App\Repositories\CompanyRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Repositories\ProductRepository;
use App\Exceptions\BusinessException;
use DB;
use Admin;

/**
 * 业务数据对外接口
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Http\Controllers\Admin
 */
class BusinessApiController extends Controller
{
    protected $productRepository;

    protected $businessRepository;

    protected $companyRepository;


    public function __construct(ProductRepository $productRepository,
                                BusinessRepository $businessRepository,
                                CompanyRepository $companyRepository)
    {
        $this->productRepository = $productRepository;
        $this->businessRepository = $businessRepository;
        $this->companyRepository = $companyRepository;
    }

    /**
     *获得游戏名数据 和厂商（客户）的 关系数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getGames(Request $request){
        try{
            $where=$this->getTime($request);
        }catch (\Exception $e){
            return $this->errorReturn(-1,$e->getMessage());
        }

        $games=$this->productRepository->with([
            'company',
        ])->applyWhere($where)->all();

        $returnData=[];
        foreach($games as $game){
            $returnData[]=[
                'id'=>$game->id,
                'game_name'=>$game->name,
                'type'=>$game->type,
                'company_id'=>$game->company_id,
                'company_name'=>$game->company->company_name,
                'updated_at'=>$game->updated_at->toDateTimeString(),
            ];
        }
        return $this->returnData($returnData);
    }

    /**
     * 获得后台业务的数据 和 和厂商（客户）的 关系数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getBusinessies(Request $request){
        try{
            $where=$this->getTime($request);

            /*过滤 删除 未审核的  （暂不需要过滤）
             * $where[]=function ($query)  {
                $query->where('audit_status' ,'=', 1);
            };
            $where[]=function ($query)  {
                $query->where('del_time' ,'=', '0000-00-00 00:00:00');
            };*/

        }catch (\Exception $e){
            return $this->errorReturn(-1,$e->getMessage());
        }

        $businessies=$this->businessRepository->with([
            'company',
            'product',
        ])->applyWhere($where)->all();
        $returnData=[];
        foreach($businessies as $business){
            $returnData[]=[
                'id'=>$business->id,
                'business_key'=>$business->business_key,
                'company_id'=>$business->company_id,
                'company_name'=>$business->company->company_name,
                'game_id'=>$business->product_id,
                'game_name'=>$business->product->name,
                'updated_at'=>$business->updated_at->toDateTimeString(),
            ];
        }
        return $this->returnData($returnData);
    }

    /**
     *  获得 厂商（客户的）数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getCompanys(Request $request){
        try{
            $where=$this->getTime($request);
        }catch (\Exception $e){
            return $this->errorReturn(-1,$e->getMessage());
        }

        $companys=$this->companyRepository->applyWhere($where)->all();
        $returnData=[];
        foreach($companys as $company){
            $returnData[]=[
                'id'=>$company->id,
                'company_name'=>$company->company_name,
                'updated_at'=>$company->updated_at->toDateTimeString(),
            ];
        }
        return $this->returnData($returnData);
    }

    /**
     *  返回数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $returnData
     * @return \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function returnData($returnData){
        if(empty($returnData)){
            return $this->errorReturn(-1,'没有找到数据');
        }else{
            return $this->successReturn($returnData);
        }
    }

    /**
     *  按时间增量过滤数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $time
     * @return array|\Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getTime($request){
        $time=$request->input('t');
        $where=[];
        if(!empty($time)){
            if(!strtotime($time)){
                throw new BusinessException('参数格式有误!');
            }
            $where[]=function ($query) use ($time) {
                $query->where('updated_at' ,'>=', $time);
            };
        }
        return $where;
    }

}
