<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\DictionaryRepository;
use App\Models\DictionaryModel;
use DB;

/**
 * 数据字典管理
 *用于配置后台的数据项
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class DictionaryController extends Controller
{
    use CommonResponse;

    protected  $dictionaryRepository;

    protected  $dictionaryModel;
    /**
     * 
     */
    public function __construct(DictionaryRepository $dictionaryRepository,
                                DictionaryModel $dictionaryModel)
    {
        $this->dictionaryRepository     =$dictionaryRepository;
        $this->dictionaryModel          =$dictionaryModel;
    }

    /**
     * 列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList($type='')
    {
        $viewData['type']=$type;
        //加载所有的类型
        $viewData['typedata']=DictionaryModel::$typeData;
        $def_type=$viewData['type'];
        if(empty($def_type)) {
            foreach ($viewData['typedata'] as $k => $v) {
                $def_type = $k;
                break;
            }
        }
        $where='  type="'.$def_type.'"';

        //如果是添加小组 需要先加载部门数据
        $viewData['deptjson']="";
        if($type=="team"){
            $dept=$this->dictionaryModel->whereRaw(' status=1 and type="dept" ')->get();
            $returnArr=[];
            foreach($dept as $d){
                $returnArr[] = array('key' =>$d->id , 'value' =>$d->value , 's' => 0);
            }
            $viewData['deptjson']=json_encode($returnArr);
            $where.=' and '.session('team_id_sql');
        }


        $viewData['listdata'] = $this->dictionaryModel->whereRaw($where)->orderBy('id', 'desc')->paginate(20);

        return view('admin.dictionary.all-list',$viewData);
    }

    /**
     *保存数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function storeDictionary(Request $request){
        $key    =   $request->get('key');
        //$value  =   $request->get('value');
        $remark  =   $request->get('remark');
        $id     =   $request->get('id');
        $type   =   $request->get('type');

        $saveData['key']=$key;
        $saveData['remark']=$remark;

        if(!empty($id)){
            DB::beginTransaction();//事务开始
            $this->dictionaryRepository->checkUpdateExists($key,$id);

            $saveData['value']=$key;

            //组织添加部门的特殊数据
            if($type=='dept'){
                $saveData['v1']=$request->get('value');
                //部门更新的时候 需要更新所属的所有小组的数据
                $deptData['v1']=$key;
                $deptData['v2']=$request->get('value');
                $this->dictionaryModel->whereRaw(' type="team" and parent_id ='.$id)->update($deptData);
            }
            //组织添加小组的特殊数据
            if($type=='team'){
                $dept=$request->get('dep');
                $deptData=$this->dictionaryRepository->find($dept);

                $saveData['parent_id']=$dept;
                $saveData['v1']=$deptData->value;
                $saveData['v2']=$deptData->v1;
            }

			//组织添加业务的特殊数据
			if($type=='business_line'){
				$btype=$request->get('btype');
				$saveData['v1']=$btype;
			}

            $this->dictionaryModel->where('id','=',$id)->update($saveData);
            DB::commit();
            return $this->ajaxSuccess('更新成功！');
        }else{
            $saveData['type']=$type;
            if($type=='team'){
                $dept=$request->get('dep');
                $this->dictionaryRepository->checkTeamExists($dept,$key,$saveData['type']);
            }else{
                $this->dictionaryRepository->checkExists($key,$saveData['type']);
            }
            //需要自动生成value的数据项
            $madeIndexType=['expenses_type'];
            if(in_array($saveData['type'],$madeIndexType)){
                $data=$this->dictionaryModel
                        ->whereRaw(' type="'.$saveData['type'].'" ')
                        ->orderBy('value','desc')
                        ->first();
                if(empty($data)){
                    $saveData['value']=1;
                }else{
                    $saveData['value']=$data->value+1;
                }
            }else{
                $saveData['value']=$key;

                //组织添加部门的特殊数据
                if($type=='dept'){
                    $saveData['v1']=$request->get('value');
                }
                //组织添加小组的特殊数据
                if($type=='team'){
                    $deptData=$this->dictionaryRepository->find($dept);
                    $saveData['parent_id']=$dept;
                    $saveData['v1']=$deptData->value;
                    $saveData['v2']=$deptData->v1;
                }
				//组织添加业务的特殊数据
				if($type=='business_line'){
					$btype=$request->get('btype');
					$saveData['v1']=$btype;
				}
            }
            $this->dictionaryModel->create($saveData);
            return $this->ajaxSuccess('保存成功！');
        }

    }

    /**
     *更新数据状态
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updateStatus(Request $request){
        $id     =   $request->get('id');
        $status =   $request->get('status');
        $this->dictionaryModel->where('id','=',$id)->update(['status'=>$status]);
        return $this->ajaxSuccess('更新成功！');
    }

    /**API
     *前端获取发票类型数据用于下拉框
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getInvoiceType(){
        $returnData=$this->dictionaryRepository->returnTypeArray('invoice_type');
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *API
     * 通过接口保存字典数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function saveNewDictionary(Request $request){
        $key        =   $request->get('value');
        $type       =   $request->get('type');

        $saveData['type']   =$type;
        $saveData['key']    =$key;
        $saveData['value']  =$key;

        $this->dictionaryModel->create($saveData);
        return $this->ajaxSuccess('保存成功！',['id'=>$key]);
    }


    /**
     *根据小组的ID获得小组被选中的部门数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return string
     */
    public function getDeptJsonApi(Request $request){
        $team='';
        $team_id=$request->get('id');
        if(!empty($team_id)){
            $team=$this->dictionaryRepository->find($team_id);
        }

        $dept=$this->dictionaryModel->whereRaw(' status=1 and type="dept" ')->get();
        $returnArr=[];
        foreach($dept as $d){
            $s=0;
            if(!empty($team) && $team->parent_id==$d->id){
                $s=1;
            }
            $returnArr[] = array('key' =>$d->id , 'value' =>$d->value , 's' =>$s);
        }

        $returnJsonData = ["status"=>"success","info" =>"","data"=>$returnArr];
        return json_encode($returnJsonData);
    }

}
