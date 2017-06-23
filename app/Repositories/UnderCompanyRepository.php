<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\UnderCompanyModel;


/**
 * 下游客户管理仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class UnderCompanyRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return UnderCompanyModel::class;
    }


    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getCompanyById($id){
        return $this->model->where('id','=',$id)->first();
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
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
     * @author YangWei<yangwei@stnts.com>
     *
     * $selected  被选中的数据ID
     * @return array
     */
    public  function getAllCompanyArray($selecteid=''){
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
     *获得所有客户的id-name  lisy
     * @author YangWei<yangwei@stnts.com>
     *
     * @param int $type
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


}
