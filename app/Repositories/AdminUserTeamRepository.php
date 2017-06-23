<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use App\Models\AdminUserTeam;

class AdminUserTeamRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return AdminUserTeam::class;
    }

    public function getUserTeams($userId){
        return $this->model->where('admin_user_id',$userId)->get(['team','company_ids','partner_ids']);
    }

    public function getUsers($teams){
        return $this->model->whereIn('team',$teams)->groupBy('admin_user_id')->get(['admin_user_id']);
    }

    public function getAllUsers($teams){
        return $this->model->whereIn('team',$teams)->get(['admin_user_id','company_ids']);
    }

    public function updateByKey($admin_user_id,$team,$attributes){
        return $this->model->where('admin_user_id','=',$admin_user_id)->where('team','=',$team)->update($attributes);
    }

    public  function getAll(){
        return $this->model->get(['team','admin_user_id','company_ids']);
    }


    /**
     * 删除用户关联的小组 客户 合作方数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $userid
     */
    public function deleteByUserId($userid){
        $this->model->whereRaw('admin_user_id='.$userid)->delete();
    }

    public function updateOrCreate($attributes){
        return $this->model->firstOrNew($attributes);
    }

}
