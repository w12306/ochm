<?php

namespace App\Repositories;

use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\Badcash;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;
use App\Models\DeliveryModel;

/**
 * 坏账
 *
 * @package App\Repositories
 */
class BadcashRepository extends BaseRepository
{

    private $deliveryModel;
    /**
     * @param Application $application
     */
    public function __construct(Application $application,DeliveryModel $deliveryModel)
    {
        parent::__construct($application);
        $this->deliveryModel=$deliveryModel;
    }

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return Badcash::class;
    }

    /**
     * 判断某个执行月是否已存在坏账信息
     *
     * @param $deliveryId
     * @return mixed
     */
    public function existsByDeliveryId($deliveryId)
    {
        return $this->wrap(function ($deliveryId) {

            return $this->model->where('delivery_id', $deliveryId)->where('isshow','=',1)->exists();

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 验证 录入的坏账金额是否大于月执行额
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $delivery_id  执行月数据id
     * @param $queryBadcashAmount  录入的坏账金额
     * @return bool
     */
    public function checkBadAmount($delivery_id,$queryBadcashAmount){
        $res=$this->deliveryModel
                ->where('id',$delivery_id)
                ->get()->toarray();
        if($queryBadcashAmount>$res[0]['amount']){
            return false;
        }
        return true;
    }

    /**
     * 用于检测数据中是否 这个业务小组执行月的数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function findByDeliveryId($delivery_id){
        return $this->model->where('delivery_id','=',$delivery_id)->where('isshow','=',1)->count();
    }

    /**
     *用于检测数据中是否 存在某个业务的数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $business_key
     */
    public function findByBusinessKey($business_key){
        $deliveryIds=$this->deliveryModel
                ->where('business_key',$business_key)
                ->get()
                ->map(function($data){
                        return $data['id'];
                });
        $deliveryIds=$deliveryIds->toarray();

        return $this->model->whereIn('delivery_id',$deliveryIds)->where('isshow','=',1)->count();
    }

    /**
     *软删除 坏账
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|int|mixed
     */
    public function deleteBadcash($id){
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        return $this->model->where('id',$id)->first()->update($arr);
    }

}
