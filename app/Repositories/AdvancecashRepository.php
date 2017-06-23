<?php

namespace App\Repositories;

use App\Models\Advancecash;
use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 预收款
 *
 * @package App\Repositories
 */
class AdvancecashRepository extends BaseRepository
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
        return Advancecash::class;
    }
    /**
     *软删除
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|int|mixed
     */
    public function deleteAdvancecash($id){
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        return $this->model->where('id',$id)->update($arr);
    }

}
