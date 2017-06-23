<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\BusinessTeamModel;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\CompanyModel;


/**
 * 业务小组数据理仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class BusinessTeamRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return BusinessTeamModel::class;
    }


    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function checkBusinessTeam($business_key,$team){
        $res= $this->model->whereRaw('business_key="'.$business_key.'" and team="'.$team.'" and isshow=1 ')->first();
        if(empty($res)){
            return true;
        }
        return false;
    }

    /**
     *根据业务编号获得第一个小组信息
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $bid
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getTeamByBusinessId($bid){
        return $this->model->where('business_id',$bid)->first();
    }


}
