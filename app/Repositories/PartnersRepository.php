<?php

namespace App\Repositories;

use Illuminate\Contracts\Foundation\Application;
use App\Exceptions\BusinessException;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\PartnersModel;


/**
 * 合作方管理仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class PartnersRepository extends BaseRepository
{
	protected $companyRepository;
	/**
	 * @param Application        $application
	 * @param BusinessRepository $businessRepository
	 */
	public function __construct(Application $application,
								CompanyRepository $companyRepository)
	{
		parent::__construct($application);

		$this->companyRepository = $companyRepository;
	}
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return PartnersModel::class;
    }

    /**
     *根据ID获得 合作方编辑
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getPartnerById($id){
        return $this->model->where('id','=',$id)->first();
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $name
     */
    public function checkPartnerName($name,$id){
        if(empty($id)){
            return $this->model->where('company_name','=',$name)->exists();
        }else{
            return $this->model->whereRaw('company_name="'.$name.'" and id!='.$id)->exists();
        }
    }
    /**
     *根据类型获得 合作方数据/ 含用于数据权限
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $type 1=上游 2=下游
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getPartnersList(){
        return  $this->model->with("company")
                ->whereHas('company',
                    function ($subQuery) {
                        $subQuery->whereRaw(session('company_id_sql'));
                    }
                )->get();
    }

    /**
     *获得全部合作方数据/ 不含用于数据权限
     * @author YangWei<yangwei@stnts.com>
     * 添加客户时 弹出层中的合作方数据
     * @param $type 1=上游 2=下游
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAllPartnersPageList(){
        return  $this->model->get();
    }


    /**
     * 返回数据权限下合作方信息
     * @author YangWei<yangwei@stnts.com>
     *
     * @param int $type  1= 上游 2= 下游
     * $selected  被选中的数据ID
     * @return array
     */
    public  function getAllPartversArray($selecteid=''){
        $data=$this->model->with("company")
                ->whereHas('company',
                        function ($subQuery) {
                            $subQuery->whereRaw(session('company_id_sql'));
                        }
                )->lists('id', 'company_name')
                ->toArray();
        $returnArr=array();
        foreach($data as $k=>$v){
            $selecte=0;
            if(!empty($selecteid) && $selecteid==$v){$selecte=1;}
            $returnArr[]=array('key'=>$v,'value'=>$k,'s'=>$selecte);
        }
        return $returnArr;
    }

    /**
     * 返回全部合作方信息
     * @author YangWei<yangwei@stnts.com>
     *
     * @param int $type  1= 上游 2= 下游
     * $selected  被选中的数据ID
     * @return array
     */
    public  function getAllPartnersArray($selecteid=''){
        $data=$this->model
                ->lists('id', 'company_name')
                ->toArray();
        $returnArr=array();
        foreach($data as $k=>$v){
            $selecte=0;
            if(!empty($selecteid) && $selecteid==$v){$selecte=1;}
            $returnArr[]=array('key'=>$v,'value'=>$k,'s'=>$selecte);
        }
        return $returnArr;
    }

    /**
     *组织 合作方类型数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param string $selected
     * @return array
     */
    public function getPartnerTypeArray($selected=''){
        $typeArray=PartnersModel::$typeArray;

        foreach($typeArray as $k=>$v){
            $select=0;
            if(!empty($selected) && $selected==$k){$select=1;}
            $returnData[]=['key'=>$k,'value'=>$v,'s'=>$select];
        }
        return $returnData;
    }

    /**
     *获得所有合作方的id-name  lisy
     * @author YangWei<yangwei@stnts.com>
     *
     * @param int $type
     * @return array
     */
    public function getAllPartnerList(){
        $data=$this->model->with("company")
                ->whereHas('company',
                        function ($subQuery) {
                            $subQuery->whereRaw(session('company_id_sql'));
                        }
                )->lists('id', 'company_name')->toArray();
        $returnArr=array();
        foreach($data as $k=>$v){
            $returnArr[$v]=$k;
        }
        return $returnArr;
    }


	/**
	 *根据客户ID返回下属所有的合作方数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $companyid 客户ID
	 * @param int $selectid 被选中的值
	 * @param string $ty 函数调用或者接口调用
	 * @return string
	 * @internal param 客户ID $companyId
	 *
	 */
	public function getPartnerByCompanyId($companyid,$selectid=0,$ty='api'){
		if($companyid==0){return [];}
		$companyData=$this->companyRepository->getCompanyById($companyid);
		if($companyData->partner_id==""){return '';}
		$res=$this->model->whereRaw('id in ('.$companyData->partner_id.')')->lists('id','company_name');

		$returnData=array();
		foreach($res as $v=>$k){
			$select=0;

			if(is_array($selectid) && !empty($selectid)){//用于多个合作方的情况
				if(in_array($k,$selectid)){$select=1;}
			}else{
				if($selectid!=0 && $selectid==$k){$select=1;}
			}

			$returnData[]=['key'=>$k,'value'=>$v,'s'=>$select];
		}
		if ($ty=='api'){
			return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
		}else{
			return $returnData;
		}
	}

}
