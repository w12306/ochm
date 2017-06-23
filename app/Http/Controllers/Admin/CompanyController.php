<?php
namespace App\Http\Controllers\Admin;

use App\Models\CompanyTeamModel;
use App\Repositories\AdminUserTeamRepository;
use App\Repositories\DictionaryRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\CompanyRepository;
use App\Models\CompanyModel;
use App\Repositories\PartnersRepository;
use App\Models\CompanyPartnerModel;
use File;
use Validator;
use DB;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use Admin;
use App\Services\Admin\ActionLog;

/**
 * 客户管理管理
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class CompanyController extends Controller
{
    use CommonResponse;

    protected $companyModel;

    protected $companyRepository;

    protected $loginUser;

    protected $partnersRepository;

    protected $companyPartnerModel;

    protected $companyTeamModel;

    protected $dictionaryRepository;

    protected $actionLog;

    protected $adminUserTeamRepository;

    /**
     * 
     */
    public function __construct(CompanyModel $companyModel,
                                CompanyRepository $companyRepository,
                                PartnersRepository $partnersRepository,
                                AdminGuard $loginUser,
                                CompanyPartnerModel $companyPartnerModel,
                                CompanyTeamModel $companyTeamModel,
                                DictionaryRepository $dictionaryRepository,
                                AdminUserTeamRepository $adminUserTeamRepository,
                                ActionLog $actionLog)
    {
        $this->companyModel     =$companyModel;
        $this->companyRepository=$companyRepository;
        $this->loginUser           = $loginUser->user();
        $this->partnersRepository   =$partnersRepository;
        $this->companyPartnerModel  =$companyPartnerModel;
        $this->companyTeamModel     =$companyTeamModel;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->adminUserTeamRepository=$adminUserTeamRepository;
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

        if(!session('isSystem')){
            $where.=$this->getLoginUserCompanyIds();
        }

        //$where.=' and '.session('company_id_sql');

        $listdata = $this->companyModel->whereRaw($where)->orderBy('id', 'desc')->paginate(20);
        return view('admin.company.list',['listdata'=>$listdata]);
    }

    public function getLoginUserCompanyIds(){
        $userid=$this->loginUser->id;
        $user_teams=$this->adminUserTeamRepository->getUserTeams($userid);
        $companys=[];
        foreach($user_teams as $ut){
            if(!empty($ut->company_ids)) {
                $company_row_ids = explode(',', $ut->company_ids);
                foreach ($company_row_ids as $cid) {
                    $companys[$cid] = $cid;
                }
            }
        }
        if(empty($companys)){
            return ' and 1=0 ';
        }else{
            return ' and id in ('.implode(',',$companys).')';
        }
    }


    /**
     *创建客户信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function createCompany($id=0){


        return view('admin.company.edit',['id'=>$id]);
    }

    /**
     *保存客户信息
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return string
     */
    public function storeCompany(Request $request){
        $id=$request->get('id');

        $data['company_name']       =$request->get('company_name');
        $data['remark']             =$request->get('remark');//备注

        $team               =$request->get('team');//执行小组
        $partner            =$request->get('partner_id');
        if(empty($team) || empty($partner)){
            return $this->ajaxError('数据未填写完整！');exit;
        }
        $this->checkCompanyName($data['company_name'],$id);//检查是否重名
        //合作方
        $data['partner_id']         =implode(',',$partner);//合作方

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

        $user_ids=$request->get('user_ids');//被选中的后台用户

        if(!empty($id)){//编辑
            $this->updateCompany($id,$data,$team,$user_ids);
            return $this->ajaxSuccess('更新客户成功！', [
                    'url' =>route('admin.toolbox.company-list')
            ]);
            exit;
        }

        DB::beginTransaction();//事务开始

        $companyt= $this->companyRepository->create($data);//创建客户信息

        //更新客户与合作方关联表
        $partner_id_arr=$request->get('partner_id');
        $company_id =$companyt->id;//所属上游客户ID
        foreach($partner_id_arr as $k=>$v){
            $updata['company_id'] =$company_id;
            $updata['partner_id'] =$v;
            $this->companyPartnerModel->create($updata);
        }

        //更新客户与小组关联表
        foreach($team as $k=>$v) {
            $tupdata['company_id'] =$company_id;
            $tupdata['team'] =$v;
            $this->companyTeamModel->create($tupdata);
        }
        $this->actionLog->log('company.create',
                Admin::user()->id,
                [  'id'         =>$company_id,
                   'company_name'     =>$data['company_name'],
                ]
        );
        //更新客户与后台管理关系
        if(!empty($user_ids)){
            $this->updateUserTeam($user_ids,$team,$company_id,$partner);
        }


        DB::commit();//事务提交


        return $this->ajaxSuccess('添加客户成功！', [
                'url' =>route('admin.toolbox.company-list')
        ]);
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $user_ids
     * @param $teams
     * @param $company_id 本次操作的客户ID
     * @param $partner  本次提交的合作方数组
     */
    public function updateUserTeam($user_ids,$teams,$company_id,$partner){

        $users=$user_ids;//被选中的后台用户（数组）
        $team =$teams;//执行小组(数组)

        foreach($users as $k=>$userid){
            foreach($team as $tk=>$team_id){
                $attributes=['admin_user_id'=>$userid,'team'=>$team_id];

                $admin_user_team=$this->adminUserTeamRepository->updateOrCreate($attributes);
                $saveData=[];
                if(isset($admin_user_team->company_ids)){//数据已经存在 需要执行更新
                    $saveData['company_ids']=$admin_user_team->company_ids.','.$company_id;
                    $old_partners=explode(',',$admin_user_team->partner_ids);
                    $new_partners='';
                    foreach($partner as $p_k=>$p_v){
                        if(!in_array($p_v,$old_partners)){
                            if(empty($new_partners)){
                                $new_partners=$p_v;
                            }else{
                                $new_partners=$new_partners.','.$p_v;
                            }
                        }
                    }
                    $partner_ids_new=$admin_user_team->partner_ids;
                    if(!empty($new_partners)){
                        $partner_ids_new=$partner_ids_new.','.$new_partners;
                    }
                    $saveData['partner_ids']=$partner_ids_new;
                }else{
                    $saveData['company_ids']=$company_id;
                    $saveData['partner_ids']=implode(',',$partner);
                }
                $this->adminUserTeamRepository->updateByKey($userid,$team_id,$saveData);
            }
        }
    }


    /**
     *检查添加的公司名称是否有重名
     * @author YangWei<yangwei@stnts.com>
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
     * @author YangWei<yangwei@stnts.com>
     * @$id 编辑ID
     * @$data 更新的数据
     * @param $team
     * @return bool
     */
    public function updateCompany($id,$data,$team,$user_ids){

        DB::beginTransaction();//事务开始
        $this->companyRepository->update($id,$data);//更新客户信息

        //删除这个客户原有合作方信息
        $this->companyPartnerModel->whereRaw(' company_id='.$id)->delete();
        //删除这个客户原有小组的关系
        $this->companyTeamModel->whereRaw(' company_id='.$id)->delete();

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
            $this->companyTeamModel->create($tupdata);
        }

        //更新客户与后台管理关系
        $usersArr=$user_ids;//被选中的后台用户
        //1.先更新处理被选中人员的信息
        $this->updateUserTeam($usersArr,$team,$id,$partner_id_arr);

        //2.历遍所有数据  检测该客户下面的人员是都已经被取消（如果数据中包含了这个客户 但是人员没有在被选中的人员中 则需要被更新）
        $user_teams=$this->adminUserTeamRepository->getAll();

        foreach($user_teams as $ut){
            $company_row_ids=explode(',',$ut->company_ids);
            if(in_array($id,$company_row_ids)){
                if(!in_array($ut->admin_user_id,$usersArr)){//不在被选中中
                    $new_company_ids=[];
                    foreach($company_row_ids as $cid){
                        if($cid!=$id){
                            $new_company_ids[]=$cid;
                        }
                    }
                    $saveData['company_ids']=implode(',',$new_company_ids);

                    $partner_row_ids=explode(',',$ut->partner_ids);
                    $new_partner_ids=[];
                    foreach($partner_row_ids as $k=>$v){
                        if(!in_array($v,$partner_id_arr)){
                            $new_partner_ids[]=$v;
                        }
                    }
                    $saveData['partner_ids']=implode(',',$new_partner_ids);
                    $this->adminUserTeamRepository->updateByKey($ut->admin_user_id,$ut->team,$saveData);
                }
            }
        }

        $this->actionLog->log('company.edit',
                Admin::user()->id,
                [  'id'         =>$id,
                   'company_name'     =>$data['company_name'],
                ]
        );

        DB::commit();//事务提交

        return true;
    }




}
