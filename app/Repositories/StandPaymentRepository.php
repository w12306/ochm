<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;

use App\Models\StandPayment;
use Housekeeper\Eloquent\BaseRepository;



/**
 * 独立支出仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class StandPaymentRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return StandPayment::class;
    }


    /**
     *软删除
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|int|mixed
     */
    public function deleteStandPayment($id){
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        return $this->model->where('id',$id)->update($arr);
    }


}
