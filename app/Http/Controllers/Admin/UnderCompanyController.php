<?php
namespace App\Http\Controllers\Admin;

use App\Models\UnderCompanyTeamModel;
use App\Repositories\DictionaryRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\UnderCompanyRepository;
use App\Models\UnderCompanyModel;
use App\Repositories\UnderPartnersRepository;
use App\Models\UnderCompanyPartnerModel;
use File;
use Validator;
use DB;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use Admin;
use App\Services\Admin\ActionLog;
/**
 * 下游客户管理管理
 *
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class UnderCompanyController extends Controller
{
    use CommonResponse;

    protected $companyModel;

    protected $companyRepository;

    protected $loginUser;

    protected $partnersRepository;

    protected $companyPartnerModel;

    protected $underCompanyTeamModel;

    protected $dictionaryRepository;

    protected $actionLog;

    /**
     * 
     */
    public function __construct(UnderCompanyModel $companyModel,
                                UnderCompanyRepository $companyRepository,
                                UnderPartnersRepository $partnersRepository,
                                AdminGuard $loginUser,
                                UnderCompanyPartnerModel $companyPartnerModel,
                                UnderCompanyTeamModel $underCompanyTeamModel,
                                DictionaryRepository $dictionaryRepository,
                                ActionLog $actionLog)
    {
        $this->companyModel     =$companyModel;
        $this->companyRepository=$companyRepository;
        $this->loginUser           = $loginUser->user();
        $this->partnersRepository   =$partnersRepository;
        $this->companyPartnerModel  =$companyPartnerModel;
        $this->underCompanyTeamModel=$underCompanyTeamModel;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->actionLog            = $actionLog;
    }

    /**
     * 客户列表列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $company_name=trim($request->get('company_name'));

        $where=' 1=1 ';
        if(!empty($company_name)){
            $where.=' and company_name like "%'.$company_name.'%"';
        }

        $listdata = $this->companyModel->whereRaw($where)->orderBy('id', 'desc')->paginate(20);
        return view('admin.undercompany.list',['listdata'=>$listdata]);
    }

    /**
     *创建客户信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function createCompany($id=0){


        return view('admin.undercompany.edit',['id'=>$id]);
    }

    /**
     *保存客户信息
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return string
     */
    public function storeCompany(Request $request){
        $id=$request->get('id');

        $data['company_name']       =$request->get('company_name');
        $data['remark']             =$request->get('remark');//备注

        $data['team']               =$request->get('team');//执行小组

        //合作方
        $team               =$request->get('team');//执行小组
        $partner            =$request->get('partner_id');
        if(empty($team) || empty($partner)){
            return $this->ajaxError('数据未填写完整！');exit;
        }
        $data['partner_id']         =implode(',',$partner);//合作方

        $this->checkCompanyName($data['company_name'],$id);

        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $teamText='';
        foreach($team as $v){
            if(empty($teamText)){
                $teamText=$teamDept[$v];
            }else{
                $teamText=$teamText.','.$teamDept[$v];
            }
        }
        $data['team']               =$teamText;



        $data['userid']   = $this->loginUser->id;//登录人的ID
        $data['real_name'] = $this->loginUser->real_name;//登录人的ID

        if(!empty($id)){//编辑
            $this->updateCompany($id,$data,$team);
            return $this->ajaxSuccess('更新客户成功！', [
                    'url' =>route('admin.toolbox.under-company-list')
            ]);
            exit;
        }

        DB::beginTransaction();//事务开始

        $companyt= $this->companyRepository->create($data);//创建客户信息

        //更新客户与合作方关联表
        $partner_id_arr=$request->get('partner_id');
        $company_id =$companyt->id;//所属上游客户ID
        foreach($partner_id_arr as $k=>$v){
            $updata['company_id'] =$company_id;//所属上游客户ID
            $updata['partner_id'] =$v;
            $this->companyPartnerModel->create($updata);
        }

        //更新客户与小组关联表
        foreach($team as $k=>$v) {
            $tupdata['company_id'] =$company_id;
            $tupdata['team'] =$v;
            $this->underCompanyTeamModel->create($tupdata);
        }
        $this->actionLog->log('under-company.create',
                Admin::user()->id,
                [  'id'             =>$company_id,
                   'company_name'   =>$data['company_name'],
                ]
        );

        DB::commit();//事务提交


        return $this->ajaxSuccess('添加客户成功！', [
                'url' =>route('admin.toolbox.under-company-list')
        ]);
    }

    /**
     *检查添加的公司名称是否有重名
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $name
     * @param $id
     * @throws BusinessException
     */
    public function checkCompanyName($name,$id){
        if($this->companyRepository->checkCompanyName($name,$id)){
            throw new BusinessException('公司名称已经存在！');
        }
    }

    /**
     *更新数据
     * @author YangWei<yangwei@foxmail.com>
     * @$id 编辑ID
     * @$data 更新的数据
     */
    public function updateCompany($id,$data,$team){

        DB::beginTransaction();//事务开始
        $this->companyRepository->update($id,$data);//更新客户信息

        //删除这个客户原有合作方信息
        $this->companyPartnerModel->whereRaw(' company_id='.$id)->delete();

        //删除这个客户原有小组的关系
        $this->underCompanyTeamModel->whereRaw(' company_id='.$id)->delete();

        //重新关联上本次更新的数据
        //更新客户与合作方关联表
        $partner_id_arr=explode(',',$data['partner_id']);
        foreach($partner_id_arr as $k=>$v){
            $updata['company_id'] =$id;//所属上游客户ID
            $updata['partner_id'] =$v;
            $this->companyPartnerModel->create($updata);
        }

        //更新客户与小组关联表
        foreach($team as $k=>$v) {
            $tupdata['company_id'] =$id;
            $tupdata['team'] =$v;
            $this->underCompanyTeamModel->create($tupdata);
        }

        $this->actionLog->log('under-company.edit',
                Admin::user()->id,
                [  'id'             =>$id,
                   'company_name'   =>$data['company_name'],
                ]
        );
        DB::commit();//事务提交

        return true;
    }



}
