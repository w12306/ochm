<?php

namespace App\Repositories;

use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\Earnestcash;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 保证金
 *
 * @package App\Repositories
 */
class EarnestcashRepository extends BaseRepository
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
        return Earnestcash::class;
    }
    /**
     *软删除 坏账
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|int|mixed
     */
    public function deleteEarnestcash($id){
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        return $this->model->where('id',$id)->update($arr);
    }

}
