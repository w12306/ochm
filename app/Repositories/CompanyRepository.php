<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use App\Models\CompanyPartnerModel;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\CompanyModel;
use Illuminate\Contracts\Foundation\Application;

/**
 * 客户管理仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class CompanyRepository extends BaseRepository
{
    protected $companyPartnerModel;
    /**
     * @param Application $application
     */
    public function __construct(Application $application,CompanyPartnerModel $companyPartnerModel)
    {
        $this->companyPartnerModel=$companyPartnerModel;
        parent::__construct($application);
    }
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return CompanyModel::class;
    }


    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getCompanyById($id){
        return $this->model->where('id','=',$id)->first();
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $name
     */
    public function checkCompanyName($name,$id){
        if(empty($id)){
            return $this->model->where('company_name','=',$name)->exists();
        }else{
            return $this->model->whereRaw('company_name="'.$name.'" and id!='.$id)->exists();
        }
    }

    /**
     *返回全部客户信息
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param int $type  1= 上游客户  2= 下游客户
     * $selected  被选中的数据ID
     * @return array
     */
    public  function getAllCompanyArray($selecteid=''){
        $data=$this->model->whereRaw(session('company_id_sql'))->lists('id', 'company_name')->toArray();
        $returnArr=array();
        foreach($data as $k=>$v){
            $selecte=0;
            if(!empty($selecteid) && $selecteid==$v){$selecte=1;}
            $returnArr[]=array('key'=>$v,'value'=>$k,'s'=>$selecte);
        }
        return $returnArr;
    }

    /**
     * 获得所有客户的id-name  lisy
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return array
     */
    public function getAllCompanyList(){
        $data=$this->model->lists('id', 'company_name')->toArray();
        $returnArr=array();

        foreach($data as $k=>$v){
            $returnArr[$v]=$k;
        }
        return $returnArr;
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $companyIds
     * @return string
     */
    public function  getPartneridsByCompanyId($companyIds=''){
        if(empty($companyIds)){
            return '';
        }
        return $this->companyPartnerModel->whereRaw('company_id in ('.$companyIds.')')->get();
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $updated_at
     * @return array
     */
    public function getCompanyToApi($updated_at){
        return $this->model
                ->whereRaw(' updated_at >"'.$updated_at.'"')
                ->get([
                        'id',
                        'company_name',
                        'created_at',
                        'updated_at',
                ])
                ->toArray();
    }

}
