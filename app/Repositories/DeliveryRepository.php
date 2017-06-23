<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\DeliveryModel;


/**
 * 业务月执行额仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class DeliveryRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return DeliveryModel::class;
    }


    public function getByTeamId($id){
        return $this->model->where('team_id','=',$id)->where('isshow','=',1)->orderBy('updated_at','desc')->get();
    }

    public function getById($id){
        return $this->model->where('id',$id)->where('isshow','=',1)->first();
    }

    /**
     *检测 某个业务的某个月执行额是否已经存在
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function checkDelivery($team_id,$month){
        return $this->model->whereRaw('  team_id='.$team_id.' and month="'.$month.'" and isshow=1')->count();
    }

    /**
     *检测 某个小组录入的执行总金额是否大于小组的实际金额
     * 必须要小于或等于小组的实际金额
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $team_id 被检测的小组ID  用于求得已经录入的总金额
     * @param $amount 本次要录入的金额 （包括新增和编辑时填写的金额）
     * @param $teamAmount  小组的实际金额  用于比较 上面的金额不能大于这个值
     * @param $befor_edit_amount 编辑之前的金额
     * @return bool
     */
    public function checkDeliveryAmount($team_id,$amount,$teamAmount,$befor_edit_amount=0){
        $sumAmount=$this->model->whereRaw('  team_id="'.$team_id.'" and isshow=1 ')->sum('amount');
        $sumAmount=$sumAmount-$befor_edit_amount;
        if(($sumAmount+$amount)>$teamAmount){
            return false;
        }
        return true;
    }

    public function getDeliveryYears(){
        $months=$this->model->groupBy('month')->get(["month"]);
        $years=[];
        foreach($months as $d){
            $year=substr($d->month,0,4);
            $years[$year]=$year;
        }
        return $years;
    }


    /**
     * 获得业务编号下的 执行总额
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $business_key
     */
    public function getAmountByAllBusiness($business_key){
        return $this->model->whereRaw('business_key="'.$business_key.'" and isshow=1')->sum('amount');
    }


}
