<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\ProductModel;


/**
 * 产品管理仓库
 *
 * @author  yangwei <yangwei@stnts.com>
 * @package App\Repositories
 */
class ProductRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return ProductModel::class;
    }

    /**
     * 根据产品ID 获得产品数据 编辑
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|null|static
     */
    public function getProductById($id){
        return $this->model->where('id','=',$id)->first();
    }



    /**
     *返回全部产品数组下拉框数据
     * @author YangWei<yangwei@stnts.com>
     * @$selecteid
     * @return array
     */
    public function returnProductNameArray($selecteid=''){
        $data=$this->model->lists('id', 'name')->toArray();
        $returnArr=array();

        foreach($data as $k=>$v){
            $selecte=0;
            if(!empty($selecteid) && $selecteid==$v){$selecte=1;}
            $returnArr[]=array('key'=>$v,'value'=>$k,'s'=>$selecte);
        }
        return $returnArr;
    }

    /**
     *返回游戏的属性
     * @author YangWei<yangwei@stnts.com>
     *
     * @return array
     */
    public function returnGameAttributes(){
        $game_screen_array=['3D','2.5D','2D'];//游戏画面
        $mode_type_array=['策略','动作','回合','即时战略','角色扮演','竞速','棋牌','射击','体育','音乐舞蹈',];//模式类型
        $game_theme_array=['奇幻','玄幻','魔幻','科幻','武侠','历史','其他'];//游戏题材
        $screen_style_array=['Q版','卡通','写实'];//画面风格
        $business_model_array=['独立','联合'];//运营模式
        $charging_mode_array=['道具','时间'];//收费模式
        $game_type_array=['MMO','休闲'];//游戏类型

        return ['game_screen'=>$game_screen_array,
                'mode_type'=>$mode_type_array,
                'game_theme'=>$game_theme_array,
                'screen_style'=>$screen_style_array,
                'business_model'=>$business_model_array,
                'charging_mode'=>$charging_mode_array,
                'game_type'=>$game_type_array,
        ];
    }


    /**
     * 保存
     *
     * @param array $attributes
     * @return mixed
     */
    public function storeProduct(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $model->save();
        return $model->id;
    }

    /**
     * 更新
     *
     * @param array $attributes
     * @return bool
     */
    public function updateProduct(array $attributes)
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
     *获得所有产品的id-name  lisy
     * @author YangWei<yangwei@stnts.com>
     *
     * @param int $type
     * @return array [$id=>$name]
     */
    public function getAllProductList(){
        $data=$this->model->lists( 'name','id')->toArray();
        /*$returnArr=array();
        foreach($data as $k=>$v){
            $returnArr[$v]=$k;
        }*/
        return $data;
    }

    /**
     *根据客户ID获得对应的产品数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $id
     */
    public function getProductListByCompanyId($id=''){
        $where=' 1=1 ';
        if(!empty($id)){
            $where=' company_id ='.$id;
        }
        $data=$this->model->whereRaw($where)->lists('id', 'name')->toArray();
         $returnArr=array();

        foreach($data as $k=>$v){
            $returnArr[$k]=$v;
        }
        return $returnArr;
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $updated_at
     * @return array
     */
    public function getProductToApi($updated_at){
        return $this->model
                ->whereRaw(' updated_at >"'.$updated_at.'"')
                ->get([
                        'id',
                        'name',
                        'company_id',
                        'created_at',
                        'updated_at',
                ])
                ->toArray();
    }




}
