<?php

namespace App\Repositories;

use App\Exceptions\BusinessException;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\DictionaryModel;
use Housekeeper\Action;

/**
 * 字典管理仓库
 *
 * @author  yangwei <yangwei@foxmail.com>
 * @package App\Repositories
 */
class DictionaryRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return DictionaryModel::class;
    }

    /**
     * 判断某个内容是否存在
     *
     * @param $id
     * @return mixed
     */
    public function exists($id)
    {
        return $this->wrap(function ($id) {

            return $this->model->where('key', $id)->exists();

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     *返回指定类型的数据
     * 用于js 生成表单
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $type  数据类型
     * @return array
     */
    public function returnTypeArray($type, $selecteid = '')
    {
        $where     = ' status=1 and type="' . $type . '"';
        $data      = $this->model->whereRaw($where)->lists('key', 'value')->toArray();
        $returnArr = array();
        foreach ($data as $k => $v) {
            $selecte = 0;
            if (is_array($selecteid)) {
                if ( ! empty($selecteid) && in_array($v, $selecteid)) {
                    $selecte = 1;
                }
            } else {
                if($type=='contract_type' ){
                    if ( ! empty($selecteid) && $selecteid == $k) {
                        $selecte = 1;
                    }
                }else{
                    if ( ! empty($selecteid) && $selecteid == $v) {
                        $selecte = 1;
                    }
                }
            }
            $returnArr[] = array('key' =>$k , 'value' =>$v , 's' => $selecte);
        }

        return $returnArr;
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function madeDeptTeamArray($selecteid = ''){
        $team      = $this->model->whereRaw('status=1 and type="team" and '.session('team_id_sql'))->get()
                ->toArray();

        $dept      = $this->model->whereRaw('status=1 and type="dept"')->get()->toArray();
        $returnData=[];
        foreach($team as $k=>$v){
            foreach($dept as $k1=>$v1){
                if($v['parent_id']==$v1['id']){
                    $selecte = 0;
                    if (is_array($selecteid)) {
                        if ( ! empty($selecteid) && in_array($v['id'], $selecteid)) {
                            $selecte = 1;
                        }
                    } else {
                        if ( ! empty($selecteid) && $selecteid == $v['id']) {
                            $selecte = 1;
                        }
                    }
                    $returnData[$v1['value']][]=array('key' =>$v['key'].'('.$v['v1'].')' , 'value' =>$v['id'] , 's' =>
                            $selecte);
                    break;
                }
            }
        }
        return $returnData;
    }

    /**
     *用于在列表 键值对应
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function returnList($type)
    {
        $where = ' status=1 and type="' . $type . '"';

        return $this->model->whereRaw($where)->lists('key','value' )->toArray();
    }

    /**
     *根据Key得到某个类型的数据的Value
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $type   字典类型
     * @param $key    字典值
     * @return mixed|string
     */
    public function keyToValue($type, $key)
    {
        if ($type == "" || $key == "") {
            return '';
        }
        $data = $this->model->whereRaw('type="' . $type . '" and `key`="' . $key . '"')->first();
        if(isset($data->value)){
            return $data->value;
        }
        return "";
    }

    /**
     * 按照类型获取数据集合
     *
     * @param       $type
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getEntriesByType($type, $columns = ['*'])
    {
        return $this->model
            ->where('status', 1)
            ->where('type', $type)
            ->get($columns);
    }

    /**
     *新增的时候检测 同类型数据是否存在
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $key
     * @param string $type
     * @return mixed
     * @throws BusinessException
     */
    public function checkExists($key,$type=''){
        if(empty($type)){
            throw new BusinessException('参数缺失！');
        }

        if($this->model->where('type',$type)->where('key',$key)->exists()){
            throw new BusinessException('数据已经录入过！');
        }

        return true;
    }

    /**
     *新增的时候检测 同部门下的小组是否存在
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $deptid
     * @param $team
     * @param string $type
     * @return bool
     * @throws BusinessException
     */
    public function checkTeamExists($deptid,$team,$type=''){
        if(empty($type)){
            throw new BusinessException('参数缺失！');
        }

        if($this->model->where('type',$type)->where('key',$team)->where('parent_id',$deptid)->exists()){
            throw new BusinessException('数据已经录入过！');
        }

        return true;
    }

    /**
     *编辑的时候检测 同类型数据是否存在
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $key
     * @param string $id
     * @return mixed
     * @throws BusinessException
     */
    public function checkUpdateExists($key,$id=''){
        if(empty($id)){
            throw new BusinessException('参数缺失！');
        }
        $data=$this->model->where('id',$id)->first();
        if($data->key!=$key){
            return $this->checkExists($key,$data->type);
        }
        return true;
    }

    /**
     *获得小组与部门关系数组
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return array
     */
    public function getDeptTeam(){
        $data=$this->model->where('type','=','team')->where('status','=',1)->orderBy('v1')->get()->toArray();
        if(empty($data)){return [];}
        $returnArr=[];
        foreach($data as $k=>$v){
            $returnArr[$v['id']]=$v['key'].'('.$v['v1'].')';
        }
        return $returnArr;
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $where
     * @param string $Field
     * @return mixed
     */
    public function getInfoByWhere($where,$Field='*'){
        return $this->model->select($Field)->whereRaw($where)->get()->toArray();
    }


}
