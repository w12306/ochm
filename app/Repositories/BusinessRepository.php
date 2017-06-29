<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\BusinessModel;
use App\Models\BusinessTeamModel;
use Illuminate\Contracts\Foundation\Application;

/**
 * 业务管理仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class BusinessRepository extends BaseRepository
{
    protected  $businessTeamModel;
    /**
     * @param Application $app
     */
    public function __construct(Application $app, BusinessTeamModel $businessTeamModel)
    {
        parent::__construct($app);

        $this->businessTeamModel = $businessTeamModel;
    }
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return BusinessModel::class;
    }

    /**
     *根据业务iD查询所属执行小组的执行金额数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $bid
     */
    public function getBusinessTeamBykey($b_key=''){
        if(empty($b_key)){return [];}
        return $this->businessTeamModel
                ->where('business_key','=',$b_key)
                ->where('isshow','=',1)
                ->get();
    }

    /**
     *根据ID获得业务的所有数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $bid
     */
    public function getBusinessById($bid=''){
        return $this->model->where('id','=',$bid)->first();
    }

    /**
     * @param       $column
     * @param       $values
     * @param array $columns
     * @return mixed
     */
    public function getIn($column, $values, $columns = ['*'])
    {
        return $this->wrap(function ($column, $values, $columns = ['*']) {

            return $this->model->whereIn($column, $values)->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 保存业务
     *
     * @param array $attributes
     * @return mixed
     */
    public function storeBusiness(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        return $model->id;
    }

    /**
     * @param       $key
     * @param array $columns
     * @return BusinessModel
     */
    public function findByKey($key, $columns = ['*'])
    {
        return $this->wrap(function ($key, $columns = ['*']) {

            return $this->model->where('business_key', '=', $key)->firstOrFail($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 更新广告位信息
     *
     * @param array $attributes
     * @return bool
     */
    public function updateSpace(array $attributes)
    {
        if ($this->model->where('id', $attributes['id'])
            ->update($attributes)
        ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *组织页面上 业务状态的数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $selected
     * @return array
     */
    public  function getConfirmStatus($selected=''){
        $confirm_status_array=[0=>'未发', 1=>'已发未确认' , 2=>'已发已确认'];
        $returnDtat=array();
        foreach($confirm_status_array as $k=>$v){
            $select=0;
            if($selected==$k){$select=1;}
            $returnDtat[]=['key'=>$k,'value'=>$v,'s'=>$select];
        }
        return $returnDtat;
    }

    /**
     *业务这边判断合同的状态
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function  checkContractStatus($contract_type,$contractData){
        if(empty($contractData->ckey) || $contract_type==4){//4=特殊无合同
            return '无合同';
        }else if(!empty($contractData->ckey)){
            return '已发合同未回';
        }else if(!empty($contractData->ckey) && !empty($contractData->savetime)){
            return '合同已存档';
        }
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getBusinessToApi($updated_at){
        return $this->model
                ->whereRaw(' updated_at >"'.$updated_at.'"')
                ->get([
                        'id',
                        'business_key',
                        'company_id',
                        'product_id',
                        'created_at',
                        'updated_at',
                ])
                ->toArray();
    }

    /**
     *返回当前登录人所有用所有的业务id  用于验证合同列表的权限
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return string
     */
    public function getUserBusinessIds(){
        $data=$this->model
                ->with(['businessteams'])
                ->where(function ($query)  {
                    $query->whereRaw(session('company_sql'));
                })
                ->where(
                        function ($query) {
                            $query->whereHas('businessteams', function ($subQuery)  {
                                $subQuery->whereRaw(session('team_sql'));
                            });
                        }
                )->get(['id']);

        $idArr=[];
        foreach($data as $d){
            $idArr[$d->id]=$d->id;
        }
        if(!empty($idArr)){
            return implode(',',$idArr);
        }else{
            return '';
        }
    }


}
