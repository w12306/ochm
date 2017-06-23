<?php

namespace App\Repositories;

use App\Models\Advertis;
use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 广告位
 *
 * @package App\Repositories
 */
class AdvertisRepository extends BaseRepository
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
        return Advertis::class;
    }

	/**
	 * 获得广告位ID与名称对应数组
	 * @author YangWei<yangwei@stnts.com>
	 *$executive_id  判断是新增还是编辑
	 * @return array
	 */
	public function getAdvertisList($executive_id){
		$advertis=$this->model->get();
		$returnArr=[];
		foreach($advertis as $d){
			if(empty($executive_id) && $d->isshow!=1){continue;}
			$isshow_text='';
			if($d->isshow!=1){
				$isshow_text='(删除)';
			}
			$returnArr[]=['key'=>$d->id,'value'=>$d->name.$isshow_text];
		}
		return $returnArr;
	}

	/**
	 * 获得广告位ID与规格
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @return array
	 */
	public function getAdvertisRemarkList(){
		$advertis=$this->model->get();
		$returnArr=[];
		foreach($advertis as $d){
			$returnArr[$d->id]=$d->remark;
		}
		return $returnArr;
	}

	/**
	 * 获得已经删除的广告位ID与名称数组
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @return array
	 */
	public function getDelAdvertisNameList(){
		$advertis=$this->model->where('isshow','=',0)->get();
		$returnArr=[];
		foreach($advertis as $d){
			$returnArr[$d->id]=$d->name.'(删除)';
		}
		return $returnArr;
	}

	/**
	 * 获得广告位与最大轮数的关系
	 * $ids 获得某一个广告位的最大轮数 为空则返回全部
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getAdvertisMaxRounds($ids=''){
		$advertis=$this->model->get();
		$returnArr=[];
		foreach($advertis as $d){
			if(!empty($ids) && !is_array($ids) && $d->id==$ids){
					return $d->max_rounds;
			}else if( is_array($ids) && in_array($d->id,$ids)){
				$returnArr[$d->id]=$d->max_rounds;
			}else{
				$returnArr[$d->id]=$d->max_rounds;
			}
		}
		return $returnArr;
	}

    public function getMaxSequence(){
        return $this->model->orderBy('sequence','desc')->first();
    }
}
