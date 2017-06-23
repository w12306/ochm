<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\BackcashInvoiceModel;
use App\Models\InvoiceDeliveyModel;
use App\Models\InvoiceTeamModel;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\CompanyModel;


/**
 * 回款金额拆分管理仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class BackcashInvoiceRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return BackcashInvoiceModel::class;
    }

    /**
     *根据发票id查询未被删除的fapui
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $invoice_id
     */
    public function findBackcashByInvoiceId($invoice_id){
        return $this->model->whereRaw('isshow=1 and invoice_id='.$invoice_id)->count();
    }

    /**
     *检测回款中的发票明细
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $invoice_id
     */
    public function findByInvoicedetailId($id){
        return $this->model->whereRaw('isshow=1 and invoice_delivery_id='.$id)->count();
    }

    /**
     * 用于检测数据中是否 含有汇款单下面的明细
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function findByBackcashId($backcash_id){
        return $this->model->where('backcash_id','=',$backcash_id)->where('isshow','=',1)->count();
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function findByDeliveryId($delivery_id){
        return $this->model->where('delivery_id','=',$delivery_id)->where('isshow','=',1)->count();
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
