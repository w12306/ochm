<?php

namespace App\Repositories;

use App\Models\AdvancecashMortgage;
use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 预收款-抵款记录
 *
 * @package App\Repositories
 */
class AdvancecashMortgageRepository extends BaseRepository
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
        return AdvancecashMortgage::class;
    }

    /**
     *  查询某个预收款下面所有的抵款金额之和
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return mixed
     */
    public function sumAmountByPid($id){
        return $this->model->where('advancecash_id',$id)->sum('amount');
    }

}
