<?php

namespace App\Repositories;

use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\EarnestcashMortgage;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 保证金-抵款记录
 *
 * @package App\Repositories
 */
class EarnestcashMortgageRepository extends BaseRepository
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
        return EarnestcashMortgage::class;
    }

    /**
     *  查询某个保证金下面所有的抵款金额之和
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return mixed
     */
    public function sumAmountByPid($id){
        return $this->model->where('earnestcash_id',$id)->sum('amount');
    }

}
