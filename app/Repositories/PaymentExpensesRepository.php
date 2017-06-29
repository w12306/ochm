<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\ExpensesDeliveyModel;
use App\Models\PaymentExpenses;
use Housekeeper\Eloquent\BaseRepository;



/**
 * 付款-支出管理仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class PaymentExpensesRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return PaymentExpenses::class;
    }


    /**
     *根据 支出总单ID 查找所属的付款单
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function findByExpensesID($expenses_id){
        return $this->model->where('expenses_id','=',$expenses_id)->where('isshow','=',1)->count();
    }

    /**
     *根据 支出总单明细ID 查找所属的付款单
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function findByExpenseseDtailId($expenses_id){
        return $this->model->where('expenses_delivery_id','=',$expenses_id)->where('isshow','=',1)->count();
    }

    /**
     *根据 付款总单 查找所属的明细单
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function findByPaymentId($payment_id){
        return $this->model->where('payment_id','=',$payment_id)->where('isshow','=',1)->count();
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function findByDeliveryId($delivery_id){
        return $this->model->where('delivery_id','=',$delivery_id)->where('isshow','=',1)->count();
    }

    /**
     *用于检测数据中是否 存在某个业务的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $business_key
     */
    public function findByBusinessKey($business_key){
        return $this->model->whereRaw('business_key="'.$business_key.'" and isshow=1 ')->count();
    }
}
