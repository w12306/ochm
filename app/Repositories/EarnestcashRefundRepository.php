<?php

namespace App\Repositories;

use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\EarnestcashRefund;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 保证金-退款记录
 *
 * @package App\Repositories
 */
class EarnestcashRefundRepository extends BaseRepository
{

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return EarnestcashRefund::class;
    }

    /**
     *  查询某个保证金下面所有的退款金额之和
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return mixed
     */
    public function sumAmountByPid($id){
        return $this->model->where('earnestcash_id',$id)->sum('amount');
    }

}
