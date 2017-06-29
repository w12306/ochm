<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Exceptions\BusinessException;
use Common\Packages\Admin\Contracts\Guard;
use App\Models\AdminUser;
use App\Repositories\ExecutiveRepository;
use App\Services\DataBaseService;
/**
 * 获得 排期明细数据
 * Class ExecutiveInfo
 *
 * @package App\Services\Admin
 */
class ExecutiveInfo
{

    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable|AdminUser
     */
    protected $adminUser;

    protected $executiveRepository;

    protected $ad_db;//广告部的数据库

    /**
     * @param Guard $auth
     */
    public function __construct(Guard $auth,
                                ExecutiveRepository $executiveRepository,
                                DataBaseService $dataBaseService
    )
    {
        $this->adminUser = $auth->user();
        $this->executiveRepository = $executiveRepository;
        $this->ad_db = $dataBaseService;
    }

    /**
     *获得本次要更新的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $date
     * @return bool
     * @throws ApiException
     */
    public function saveData($date=""){
        if(empty($date)){
            $date=date('Y-m-d',strtotime('-1 day',time()));
        }else{
            if(!strtotime($date)){
                throw new ApiException('时间格式有误!','-1');
            }
        }

        $where[]=['isshow','=',1];
        $where[]=['updated_at','>',$date];
        $list= $this->executiveRepository
            ->with(['company','partner','product','executive_rows'])
            ->applyWhere($where)
            ->applyOrder('created_at', 'desc')
            ->all();

        if(count($list->toArray())<1){
            throw new ApiException('后台没有查询到符合条件的数据!','-1');
        }

        $this->delChangeData($list);//先删除有更新的数据  下面再重新更新过去

        $returnData=[];
        $index=0;
        $time=date('Y-m-d H:i:d',time());
        foreach($list as $rows){
            foreach($rows->executive_rows as $executiveinfo){
                $ad         =json_decode($executiveinfo->advertis_json);//广告位数据
                $sell_type  =json_decode($executiveinfo->sell_type);//售卖方式

                for($i=1;$i<=31;$i++){
                    $day='d'.$i;
                    $dayinfo = json_decode($executiveinfo->$day);//当天的售卖轮数信息
                    if(!empty($dayinfo->value)){
                        $returnData[$index]['cdate']=$executiveinfo->month.'-'.$i;
                        $returnData[$index]['ad_round']=$dayinfo->value;
                        $returnData[$index]['ad_type']=$ad->value;
                        $returnData[$index]['ad_sell']=$sell_type->value;
                        $returnData[$index]['ad_customer']=$rows->company->company_name;
                        $returnData[$index]['ad_partner']=$rows->partner->company_name;
                        $returnData[$index]['schedule_sell']=$rows->business_cooperation;
                        $returnData[$index]['game']=$rows->product->name;
                        $returnData[$index]['sid']=$rows->key;
                        $returnData[$index]['bid']=$rows->business_id;
                        $returnData[$index]['created_at']=$time;
                        $returnData[$index]['updated_at']=$time;
                        $index++;
                    }
                }
            }
        }
        try{
            $this->ad_db->stroe('schedule',$returnData);
            return true;
        }catch (\Exception $e){
            throw new ApiException('广告部排期数据推送接口错误:保存数据失败!','-1',true);
        }
    }

    /**
     *用于大排期中 导出时间间隔 内的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $btime
     * @return array
     */
    public function getDataBySetTime($btime=''){

        if(!strtotime($btime)){
            throw new BusinessException('时间格式有误!');
        }
        $month=date('Y-m',strtotime($btime));
        $day=date('j',strtotime($btime));

        $where[]=['isshow','=',1];
        $where[]=function ($query) use ($month) {
            $query->whereHas('executive_rows', function ($subQuery) use ($month) {
                $subQuery->where('month','=',$month);
            });
        };
        $list= $this->executiveRepository
            ->with(['company','partner','product','executive_rows'])
            ->applyWhere($where)
            ->applyOrder('created_at', 'desc')
            ->all();

        if(count($list->toArray())<1){
            throw new BusinessException('后台没有查询到符合条件的数据!');
        }

        $returnData=[];
        $time=date('Y-m-d H:i:d',time());
        foreach($list as $rows){
            foreach($rows->executive_rows as $executiveinfo){
                if($executiveinfo->month!=$month){continue;}
                $ad         =json_decode($executiveinfo->advertis_json);//广告位数据
                $sell_type  =json_decode($executiveinfo->sell_type);//售卖方式

                $day_field='d'.$day;
                $dayinfo = json_decode($executiveinfo->$day_field);//当天的售卖轮数信息
                if(!empty($dayinfo->value)){
                    $business_key='';
                    if(!empty($rows->business_id)){
                        $business_key=$this->makeNumber($rows->business_id,'');//生成单号
                    }
                    $returnData[]=[
                        $executiveinfo->month.'-'.$day,
                        $ad->value,
                        $rows->company->company_name,
                        $rows->partner->company_name,
                        $rows->business_cooperation,
                        $rows->product->name,
                        $sell_type->value,
                        $dayinfo->value,
                        $rows->key,
                        $business_key,
                        $time
                    ];
                }
            }
        }
        return $returnData;
    }

    /**
     *生成编号 前面两个字符  后面8位数字 不够的补0
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param        $id
     * @param string $prefix
     * @param int    $length
     * @return bool|string
     */
    static public function makeNumber($id, $prefix = '', $length = 5)
    {
        $id_len = strlen($id);
        if ($id_len > $length) {
            return false;
        }
        $lostlen   = $length - $id_len;//获得还需要补多少个0
        $middleStr = '';
        for ($i = 0; $i < $lostlen; $i++) {
            $middleStr .= '0';
        }
        $prefix="";
        return $prefix . $middleStr . $id;
    }

    /**
     *  删除广告部那边 在当前日期内更新过的排期数据，然后后面会重新更新数据
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function delChangeData($list){
        $kyes=[];
        foreach($list as $executive){
            $kyes[]=$executive->key;
        }
        try{
            $this->ad_db->delete('schedule',$kyes);
            $list=null;
            return true;
        }catch (\Exception $e){
            throw new ApiException('广告部排期数据推送接口错误:更新原数据失败!','-1',true);
        }

    }


}