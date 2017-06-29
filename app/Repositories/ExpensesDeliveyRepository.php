<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\ExpensesDeliveyModel;
use Housekeeper\Eloquent\BaseRepository;



/**
 * 支付拆分管理仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class ExpensesDeliveyRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return ExpensesDeliveyModel::class;
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function findByDeliveryId($delivery_id){
        return $this->model->where('delivey_id','=',$delivery_id)->where('isshow','=',1)->count();
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function findByExpensesId($expenses_id){
        return $this->model->where('expenses_id','=',$expenses_id)->where('isshow','=',1)->count();
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
