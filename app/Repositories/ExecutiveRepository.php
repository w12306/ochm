<?php

namespace App\Repositories;

use App\Models\Executive;
use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 执行单 主表
 *
 * @package App\Repositories
 */
class ExecutiveRepository extends BaseRepository
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
        return Executive::class;
    }

	/**
	 *	下单时选择已有的执行单下拉数据
	 * @author YangWei<yangwei@foxmail.com>
	 *
	 */
	public function getExecutiveBusinessSelect($selecteid=''){
		$where=' isshow=1 and status=2 ';//business_id < 1
		if(!empty($selecteid)){
			$where.=' or id='.$selecteid;
		}
		$data=$this->model->with("product")->whereRaw($where)->orderBy('id','desc')->get();
		$returnArr=array();
		foreach($data as $row){
			$selecte=0;
			if(!empty($selecteid) && $selecteid==$row->id){$selecte=1;}
			$returnArr[]=array('key'=>$row->id,'value'=>'('.$row->key.')'.$row->product->name,'s'=>$selecte);
		}
		return $returnArr;
	}
}
