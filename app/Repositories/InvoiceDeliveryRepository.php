<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\InvoiceDeliveyModel;
use App\Models\InvoiceTeamModel;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\CompanyModel;


/**
 * 发票金额拆分管理仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class InvoiceDeliveryRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return InvoiceDeliveyModel::class;
    }


    /**
     *根据业务编号 获得总的发票总额
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $business_key
     */
    public function getBusinessInvoiceSumByKey($business_key){
        $data=$this->model->where('business_key','=',$business_key)->where('isshow','=',1)->get();
        $sum=0.00;
        foreach($data as $d){
            $sum=$d->active_amount+$sum;
        }
        return $sum;
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function findByDeliveryId($delivery_id){
        return $this->model->where('delivey_id','=',$delivery_id)->where('isshow','=',1)->count();
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function findByInvoiceId($invoice_id){
        return $this->model->where('invoice_id','=',$invoice_id)->where('isshow','=',1)->count();
    }

    /**
     *用于检测数据中是否 存在某个业务的数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $business_key
     */
    public function findByBusinessKey($business_key){
        return $this->model->whereRaw('business_key="'.$business_key.'" and isshow=1 ')->count();
    }

}
