<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\UnderPartnersRepository;
use App\Models\UnderPartnersModel;
use File;
use Validator;
use DB;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use App\Services\ExcelMaker;
use Admin;
use App\Services\Admin\ActionLog;
/**
 * 下游合作方管理
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class UnderPartnersController extends Controller
{
    use CommonResponse;

    protected $partnersRepository;

    protected $partnersModel;

    protected $loginUser;

    protected $actionLog;

    /**
     * 
     */
    public function __construct(UnderPartnersRepository $partnersRepository,
                                UnderPartnersModel $partnersModel,
                                AdminGuard $loginUser,
                                ActionLog $actionLog)
    {
        $this->partnersRepository   =$partnersRepository;
        $this->partnersModel        =$partnersModel;
        $this->loginUser            = $loginUser->user();
        $this->actionLog            = $actionLog;
    }

    private function seach($request){
        $where=' 1=1 ';

        $company_name=$request->get('company_name');
        if(!empty($company_name)){
            $where.=' and company_name like "%'.$company_name.'%"';
        }

        $company_type_arr=$request->get('company_type');
        if(!empty($company_type_arr)){
            $company_type_str=implode(',',$company_type_arr);
            $where.=' and company_type in ('.$company_type_str.')';
        }
        return $where;
    }

    /**
     *获得合作方数据集合
     * @author YangWei<yangwei@stnts.com>
     * @param $request
     * @param int $limit 每次查询条数  空为全部
     * @return mixed
     */
    private function getPartnerListData($request,$limit=""){
        $where=$this->seach($request);
        if(empty($limit)){
            return $this->partnersModel
                    ->with(['undercompany'])
                    ->whereRaw($where)
                    ->orderBy('id', 'desc')
                    ->paginate(20);
        }else{
            return $this->partnersModel
                    ->with(['undercompany'])
                    ->whereRaw($where)
                    ->orderBy('id', 'desc')
                    ->get();
        }
    }


    /**
     * 合作方表列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $where=' 1=1 ';



        $listdata = $this->getPartnerListData($request);

        $company_type=UnderPartnersModel::$typeArray;

        return view('admin.underpartners.list',['listdata'=>$listdata,
                                            'company_type'=>$company_type,
        ]);
    }


    /**
     *保存合作方信息
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return string
     */
    public function storePartner(Request $request){
        $id=$request->get('id');

        $data['company_type']       =$request->get('company_type');
        $data['company_name']       =$request->get('company_name');
        $data['balance']            =$request->get('balance');
        $data['taxkey']             =$request->get('taxkey');
        $data['nickname']           =$request->get('nickname');
        $data['address']            =$request->get('address');
        $data['tel']                =$request->get('tel');
        $data['boss']               =$request->get('boss');

        $data['userid']             = $this->loginUser->id;//登录人的ID
        $data['real_name']          = $this->loginUser->real_name;//登录人的ID

        $this->checkPartnerName($data['company_name'] ,$id);

        if(!empty($id)){//编辑
            $this->updatePartner($id,$data);
            return $this->ajaxSuccess('更新合作方成功！', [
                    'url' =>route('admin.toolbox.under-partner-list')
            ]);
            exit;
        }
        $partner= $this->partnersRepository->create($data);

        $this->actionLog->log('under-partner.create',
                Admin::user()->id,
                [  'id'             =>$partner->id,
                   'company_name'   =>$data['company_name'],
                ]
        );

        return $this->ajaxSuccess('添加合作方成功！', [
                'url' =>route('admin.toolbox.under-partner-list'),
                ['id'=>$partner->id,'name'=>$data['company_name']]
        ]);
    }

    /**
     *检查添加的公司名称是否有重名
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $name
     * @param $id
     * @throws BusinessException
     */
    public function checkPartnerName($name,$id){
        if($this->partnersRepository->checkPartnerName($name,$id)){
            throw new BusinessException('合作方名称已经存在！');
        }
    }

    /**
     *更新数据
     * @author YangWei<yangwei@stnts.com>
     * @$id 编辑ID
     * @$data 更新的数据
     */
    public function updatePartner($id,$data){
        $this->partnersRepository->update($id,$data);
        $this->actionLog->log('under-partner.edit',
                Admin::user()->id,
                [  'id'             =>$id,
                   'company_name'   =>$data['company_name'],
                ]
        );
        return true;
    }


    /**
     * 返回客户数据json
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function partnerExcel(Request $request, ExcelMaker $excelMaker){
        //表头
        $headers = [
                '合作方ID',
                '公司全称',
                '公司类型',
                '初始余额',
                '纳税识别码',
                '公司简称',
                '公司地址',
                '公司电话',
                '联系人',
                '所属上游客户',


        ];

        $datas=$this->getPartnerListData($request,'all');

        $rows = $datas->map(function ($data) {
            $companys="";
            foreach($data->undercompany as $d){
                $companys.=$d->company_name.',';
            }
            return [
                    $data->id,
                    $data->company_name,
                    $data->companytype_text,
                    $data->balance,
                    $data->taxkey,
                    $data->nickname,
                    $data->address,
                    $data->tell,
                    $data->boss,
                    $companys,
            ];
        });

        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }




}
