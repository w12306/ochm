<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\UnderPartnersModel;


/**
 * 下游合作方管理仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class UnderPartnersRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return UnderPartnersModel::class;
    }

    /**
     *根据ID获得 合作方编辑
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getPartnerById($id){
        return $this->model->where('id','=',$id)->first();
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
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
     *根据类型获得 合作方数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getPartnersList(){
        return $this->model->get();
    }

    /**
     * 返回全部合作方信息
     * @author YangWei<yangwei@foxmail.com>
     *
     * $selected  被选中的数据ID
     * @return array
     */
    public  function getAllPartversArray($selecteid=''){
        $data=$this->model->lists('id', 'company_name')->toArray();
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
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param string $selected
     * @return array
     */
    public function getPartnerTypeArray($selected=''){
        $typeArray=UnderPartnersModel::$typeArray;

        foreach($typeArray as $k=>$v){
            $select=0;
            if(!empty($selected) && $selected==$k){$select=1;}
            $returnData[]=['key'=>$k,'value'=>$v,'s'=>$select];
        }
        return $returnData;
    }

    /**
     *获得所有合作方的id-name  lisy
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param int $type
     * @return array
     */
    public function getAllPartnerList(){
        $data=$this->model->lists('id', 'company_name')->toArray();
        $returnArr=array();

        foreach($data as $k=>$v){
            $returnArr[$v]=$k;
        }
        return $returnArr;
    }
}
