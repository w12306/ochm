<?php
namespace App\Http\Controllers\Admin;

use App\Models\ContractBusiness;
use App\Repositories\ExecutiveRepository;
use App\Repositories\InvoiceDeliveryRepository;
use App\Repositories\BackcashInvoiceRepository;
use App\Repositories\BadcashRepository;
use App\Repositories\ExpensesDeliveyRepository;
use App\Repositories\PaymentExpensesRepository;
use Bican\Roles\Models\Permission;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\BusinessRepository;
use App\Models\BusinessModel;
use App\Models\BusinessTeamModel;
use App\Repositories\BusinessTeamRepository;
use App\Repositories\DictionaryRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\ProductRepository;
use App\Repositories\ContractRepository;
use App\Repositories\DeliveryRepository;
use App\Models\DeliveryModel;
use App\Models\Contract;
use App\Services\Admin\PermissionJudge;
use App\Services\Admin\ActionLog;
use File;
use Mockery\CountValidator\Exception;
use Validator;
use DB;
use Admin;
/**
 * 业务管理
 *
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class BusinessController extends Controller
{
    use CommonResponse;

    protected  $businessRepository;

    protected  $businessModel;

    protected  $businessTeamModel;

    protected  $dictionaryRepository;

    protected  $companyRepository;

    protected  $partnersRepository;

    protected  $productRepository;

    protected  $contract;

    protected  $contractRepository;

    protected  $deliveryRepository;

    protected  $businessTeamRepository;

    protected  $deliveryModel;

    protected  $invoiceDeliveryRepository;

	protected  $actionLog;

	protected  $contractBusiness;

	protected  $backcashInvoiceRepository;

	protected  $badcashRepository;

	protected  $expensesDeliveyRepository;

	protected  $paymentExpensesRepository;

	protected  $executiveRepository;
    protected  $permissionJudge;

    /**
     * 
     */
    public function __construct(BusinessRepository $businessRepository,
                                BusinessModel $businessModel,
                                BusinessTeamModel $businessTeamModel,
                                DictionaryRepository $dictionaryRepository,
                                DeliveryRepository $deliveryRepository,
                                CompanyRepository $companyRepository,
                                PartnersRepository $partnersRepository,
                                ProductRepository $productRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                Contract $contract,
                                ContractBusiness $contractBusiness,
                                DeliveryModel $deliveryModel,
                                ContractRepository $contractRepository,
                                InvoiceDeliveryRepository $invoiceDeliveryRepository,
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                BadcashRepository $badcashRepository,
                                ExpensesDeliveyRepository $expensesDeliveyRepository,
                                PaymentExpensesRepository $paymentExpensesRepository,
                                ActionLog $actionLog,
								ExecutiveRepository $executiveRepository,
                                PermissionJudge $permissionJudge )
    {
        $this->businessRepository   =$businessRepository;
        $this->businessModel        =$businessModel;
        $this->businessTeamModel    =$businessTeamModel;
        $this->dictionaryRepository =$dictionaryRepository;
        $this->companyRepository    =$companyRepository;
        $this->partnersRepository   =$partnersRepository;
        $this->productRepository    =$productRepository;
        $this->contract             =$contract;
        $this->contractRepository   =$contractRepository;
        $this->deliveryRepository   =$deliveryRepository;
        $this->deliveryModel        =$deliveryModel;
        $this->businessTeamRepository=$businessTeamRepository;
        $this->invoiceDeliveryRepository=$invoiceDeliveryRepository;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
        $this->badcashRepository    =$badcashRepository;
        $this->expensesDeliveyRepository=$expensesDeliveyRepository;
        $this->paymentExpensesRepository=$paymentExpensesRepository;
        $this->actionLog            =$actionLog;
        $this->contractBusiness     =$contractBusiness;
		$this->executiveRepository  =$executiveRepository;
        $this->permissionJudge      =$permissionJudge;
    }


    private function formDataConstruct(){
        //组织合作方
        $viewData['companyData']=$this->companyRepository->getAllCompanyArray();

        //组织业务类型 查询
        $viewData['business_type']=$this->dictionaryRepository->returnTypeArray('business_type');

        //组织合作方
        $viewData['partnersData']=$this->partnersRepository->getAllPartversArray();

        //组织业务类型 查询
        $viewData['business_type']=$this->dictionaryRepository->returnTypeArray('business_type');

        //组织业务线 查询
        $viewData['business_line']=$this->dictionaryRepository->returnTypeArray('business_line');

        //组织执行小组 查询
        $viewData['team']=$this->dictionaryRepository->madeDeptTeamArray();

        $viewData['audit_status']=BusinessModel::$audit_status;

        //查询小组所属的部门
        $viewData['teamDept']=$this->dictionaryRepository->getDeptTeam();

        return $viewData;
    }

    private function seach($request){
        $where=[];
        //客户
        $company_id=$request->get('company_id');
        if(!empty($company_id)){
            $where[]=function ($query) use ($company_id) {
                $query->whereHas('business', function ($subQuery) use ($company_id) {
                    $subQuery->whereRaw('  company_id in ('.$company_id.')');
                });
            };
        }
        //合作方
        $partner_id=$request->get('partner_id');
        if(!empty($partner_id)){
            $where[]=function ($query) use ($partner_id) {
                $query->whereHas('business', function ($subQuery) use ($partner_id) {
                    $subQuery->whereRaw('  partner_id in ('.$partner_id.')');
                });
            };
        }

        $business_type=$request->get('business_type');
        if(!empty($business_type)){
            $where[]=function ($query) use ($business_type) {
                $query->whereHas('business', function ($subQuery) use ($business_type) {
                    $subQuery->whereIn('business_type',$business_type);
                });
            };
        }

        $business_line=$request->get('business_line');
        if(!empty($business_line)){
            foreach($business_line as $k=>$v){
                if(empty($business_line_str)){
                    $business_line_str=' business_line like "%'.$v.'%" ';
                }else{
                    $business_line_str.=' or business_line like "%'.$v.'%" ';
                }
            }
            $where[]=function ($query) use ($business_line_str) {
                $query->whereHas('business', function ($subQuery) use ($business_line_str) {
                    $subQuery->whereRaw('    ( '.$business_line_str.' )');
                });
            };
        }

        $team=$request->get('team');
        if(!empty($team)){
            $where[]=function ($query) use ($team) {
                $query->whereIn('team',$team);
            };
        }

        $audit_status=$request->get('audit_status');
        if(!empty($audit_status)){
            $where[]=function ($query) use ($audit_status) {
                $query->whereHas('business', function ($subQuery) use ($audit_status) {
                    $subQuery->whereIn('audit_status',$audit_status);
                });
            };
        }

        //回款状态
        $backcash_status=$request->get('backcash_status');
        if(!empty($backcash_status)){
            $where[]=function ($query) use ($backcash_status) {
                $query->whereIn('backcash_status',$backcash_status);
            };
        }

        //合同状态
        $contract_status=$request->get('contract_status');
        if(!empty($contract_status)){
            $where[]=function ($query) use ($contract_status) {
                $query->whereHas('business.contract', function ($subQuery) use ($contract_status) {
                    $subQuery->whereIn('status',$contract_status);
                });
            };
        }

        $key=trim($request->get('key'));
        if(!empty($key)){
            $key=static::serialInStr($key);
            $where[]=function ($query) use ($key) {
                $query->whereRaw('business_key in ('.$key.')');
            };
        }

        //增加客户范围限制
        $where[]=function ($query){
            $query->whereHas('business', function ($subQuery) {
                $subQuery->whereRaw(session('company_sql'));
            });
        };
        //增加小组范围限制
        $where[]=function ($query)  {
            $query->whereRaw(session('team_sql') );
        };


        return $where;
    }

    /**
     * 业务列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $viewData=$this->formDataConstruct();
        //页面查询
        $where=$this->seach($request);
        $viewData['listdata'] = $this->businessTeamRepository->with([
            'deliverys',
            'invoice_deliveys',
            'business',
            'business.partner',
            'business.company',
            'business.contracts',
            'expenses_deliveys',//计算支出总额
            'backcash_invoices',//计算回款总额
        ])
            ->applyWhere($where)
            ->applyOrder('business_key', 'desc')
            ->paginate(20);
        $viewData['audit_edit']=$this->permissionJudge->isAllowed('business.audit-edit');
        return view('admin.business.all-list',$viewData);
    }

    /**
     *创建业务
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function createBusiness($id=0){
        return view('admin.business.edit',['bid'=>$id,'executive_id'=>0]);
    }

	public function createBusinessForExecutive($executive_id){
		return view('admin.business.edit',['bid'=>0,'executive_id'=>$executive_id]);
	}

    /**
     *保存业务
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return string
     */
    public function storeBusiness(Request $request){

        $id=$request->get('id');
        if(!empty($id)){
            if($this->updateBusiness($request)){
                return $this->ajaxSuccess('编辑成功！',['url' => route('admin.business.all-business-list')]);
                exit;
            }
        }
        if(strtotime($request->get('btime'))>strtotime($request->get('etime'))){
            return $this->ajaxError('日期范围有误！');
        }

        $saveData['company_id']         =$request->get('company_id');//上游客户ID
        $saveData['partner_id']         =$request->get('partner_id');//上游合作方
        $saveData['product_id']         =$request->get('product_id');//产品ID
        $saveData['business_type']      =$request->get('business_type');//业务类型
        $saveData['business_line']      =implode(',',$request->get('business_line'));//业务线 多个用逗号分隔
        $saveData['contract_type']      =$request->get('contract_type');//合同类型
        $saveData['contract_id']        =$request->get('contract_ckey');//合同编号
        $saveData['btime']              =$request->get('btime');//约定开始时间
        $saveData['etime']              =$request->get('etime');//约定结束时间
        $saveData['paytime']            =$request->get('paytime');//约定结款时间
        $saveData['amount']             =$request->get('amount');//预算金额
        $saveData['active_amount']      =$request->get('active_amount');//实际金额
        $saveData['confirm_status']     =$request->get('confirm_status');//确认函状态
        $saveData['remark']             =$request->get('remark');//业务补充
		$saveData['executive_id']       =$request->get('execution_id');//执行单ID

        DB::beginTransaction();//事务开始

        $teamArr=$request->get('team');


        //保存业务数据
        $business=$this->businessRepository->create($saveData);//保存业务数据
        $business_id=$business->id;
        $business_key=static::makeNumber($business_id,'');//生成单号

        //更新业务单号
        $updateData['business_key']             =    $business_key;
        $this->businessRepository->update($business_id,$updateData);

        //判断是否需要更新与合同之间的关系
        if(!empty($saveData['contract_type']) && !empty($saveData['contract_id'])){
            $this->contractBusiness->create(['contract_id'=>$saveData['contract_id'],'business_id'=>$business_id]);
        }

		//更新执行单数据
		if(!empty($saveData['executive_id'])){
			//检查基础数据是否改变 如果 改变则更新关联业务那边的数据
			$this->synExecutiveData($business_id,$saveData);
		}

        //保存业务小组数据
        //$ck_amount=0.00;//用于后端检验 填写的预算/实际金额 和拆分后的 是否一致
        //$ck_active_amount=0.00;
        $this->checkBusinessAmount($saveData['amount'],$saveData['active_amount'],$teamArr,$request);
        foreach ($teamArr as $k => $v) {
            //保存小组金额拆分
            if(count($teamArr)==1){
                $business_team_data = ['business_id' => $business_id,'business_key'=>$business_key,'team' =>$v,
                                        'amount' =>$saveData['amount'],'active_amount' => $saveData['active_amount']];
            }else {
                $amount = $request->get('y_' . $v);
                $active_amount = $request->get('act_' . $v);
                $business_team_data = ['business_id' => $business_id,'business_key'=>$business_key,'team' => $v,
                                        'amount' => $amount, 'active_amount' => $active_amount];
            }
            $this->businessTeamModel->create($business_team_data);
        }
        //记录日志
        $this->actionLog->log('business.create',
                Admin::user()->id,
                 [  'id'    =>$business_id,
                    'company_id' => $saveData['partner_id'],
                    'business_key'  =>$business_key   ,
                ]
        );

        DB::commit();

        return $this->ajaxSuccess('新增成功！',['url' => route('admin.business.all-business-list')]);
    }

    /**
     *更新标记 业务数据
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return string
     */
    public function updateBusiness(Request $request){
        $business_id=$request->get('id');
        $businessData=$this->businessRepository->find($business_id);

        $saveData['company_id']         =$request->get('company_id');//上游客户ID
        $saveData['partner_id']         =$request->get('partner_id');//上游合作方
        $saveData['product_id']         =$request->get('product_id');//产品ID
        $saveData['business_type']      =$request->get('business_type');//业务类型
        $saveData['business_line']      =implode(',',$request->get('business_line'));//业务线 多个用逗号分隔
        $saveData['contract_type']      =$request->get('contract_type');//合同类型
        $saveData['contract_id']        =$request->get('contract_ckey');//合同编号
        $saveData['btime']              =$request->get('btime');//约定开始时间
        $saveData['etime']              =$request->get('etime');//约定结束时间
        $saveData['paytime']            =$request->get('paytime');//约定结款时间
        $saveData['amount']             =$request->get('amount');//预算金额
        $saveData['active_amount']      =$request->get('active_amount');//实际金额
        $saveData['confirm_status']     =$request->get('confirm_status');//确认函状态
        $saveData['remark']             =$request->get('remark');//业务补充
        $saveData['audit_status']       =$businessData->audit_status;
		$saveData['executive_id']       =$request->get('execution_id');//执行单ID

        DB::beginTransaction();//事务开始
        $teamArr=$request->get('team');//本次提交的小组数据

        //1.检查对比 本次提交的小组数据 和上次是否存在差异
        $deleted_teams=$this->getEditBusinessDelTeamIds($teamArr,$business_id);//获得被删除的小组id
        if(!empty($deleted_teams)){
            foreach($deleted_teams as $d){
                $this->deleteBusinessTeam($d,1);//逐个检查删除业务的执行小组
            }
        }

        //2.更新业务数据
        if($businessData->audit_status==2){
            $saveData['audit_status']             =0;//等待审核
        }
        $this->businessModel->whereRaw('business_key="'.$businessData->business_key.'"')->update($saveData);

        //3.判断是否需要更新与合同之间的关系
        if(!empty($saveData['contract_type']) && !empty($saveData['contract_id']) && $businessData->contract_id!=$saveData['contract_id']){
            //先删除原来的关系
            if(!empty($businessData->contract_id)){
                $this->contractBusiness->whereRaw('business_id='.$business_id.' and contract_id='.$businessData->contract_id)->delete();
            }
            //保存新关系
            $this->contractBusiness->create(['contract_id'=>$saveData['contract_id'],'business_id'=>$business_id]);
        }
        //4.比较填写的实际金额和预算金额拆分是否相等
        $this->checkBusinessAmount($saveData['amount'],$saveData['active_amount'],$teamArr,$request);

		//5.检查执行单是否被编辑 如果改变则更新原执行单
		if(!empty($saveData['executive_id'])) {
			if ($businessData->executive_id != $saveData['executive_id']) {
				if(!empty($businessData->executive_id)) {//更新被替换的执行单数据
					$executiveData_old = ['business_id' => 0, 'status' => 2];
					$this->executiveRepository->update($businessData->executive_id, $executiveData_old);
				}
			}
			//检查基础数据是否改变 如果 改变则更新关联业务那边的数据
			$this->synExecutiveData($business_id,$saveData);
		}

        //根据提交的小组挨个检查是否存在 如果存在则update  如果不存在则add
        foreach ($teamArr as $k => $v) {
            $isbl=$this->businessTeamRepository->checkBusinessTeam($businessData->business_key,$v);

            $active_amount = $request->get('act_' . $v);
            $amount = $request->get('y_' . $v);
            if($isbl){//不存在 则添加(目前不会进入 因为暂时不支持小组的修改)
                //业务小组表新增
                if(count($teamArr)==1){
                    $business_team_data = ['business_id' =>$businessData->id,'business_key'=>$businessData->business_key,
                            'team' =>$v, 'amount' =>$saveData['amount'],'active_amount' =>$saveData['active_amount']];
                }else {
                    $business_team_data = ['business_id' =>$businessData->id,'business_key'=>$businessData->business_key,
                                            'team' => $v, 'amount' => $amount, 'active_amount' => $active_amount];
                }
                $this->businessTeamModel->create($business_team_data);
            }else{//存在则更新
                if(empty($active_amount)){$active_amount=$saveData['active_amount'];}
                if(empty($amount)){$amount=$saveData['amount'];}
                $business_team_data = ['amount' => $amount,'active_amount' =>$active_amount];

                $this->businessTeamModel->whereRaw('business_key="'.$businessData->business_key.'" and team="'.$v
                        .'"')->first()->update($business_team_data);
            }
        }
        //记录日志
        $this->actionLog->log('business.edit',
                Admin::user()->id,
                [  'id'    =>$business_id,
                   'company_id' => $saveData['partner_id'],
                   'business_key'  =>$businessData->business_key   ,
                ]
        );
        DB::commit();
        return true;
    }

    /**
     * 获得编辑业务时 被勾选删除的小组ID
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $formTeamArr  表单提交的
     * @param $business_id
     * @return array
     */
    public function getEditBusinessDelTeamIds($formTeamArr,$business_id){
        $oldTeamArr=$this->businessTeamRepository->applyWhere([
                ['business_id','=',$business_id],
                ['isshow','=',1]
        ])->all();

        $returnArr=[];
        foreach($oldTeamArr as $v){
            if(!in_array($v->team,$formTeamArr)){
                $returnArr[$v->id]=$v->id;
            }
        }
        return $returnArr;
    }

    /**
     *  比较填写的实际金额和预算金额拆分是否相等
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $c_amount
     * @param $c_active_amount
     * @param $teamArr
     * @param $request
     * @throws BusinessException
     */
    function  checkBusinessAmount($c_amount,$c_active_amount,$teamArr,$request){
        $active_amount_all=0.00;
        $amount_all=0.00;
        $teamCount=count($teamArr);
        if($teamCount>1) {
            foreach ($teamArr as $k => $v) {
                $active_amount = $request->get('act_' . $v);
                $amount = $request->get('y_' . $v);
                if ($active_amount == "") {
                    $active_amount = 0;
                }
                if ($amount == "") {
                    $amount = 0;
                }
                $active_amount_all = $active_amount_all + $active_amount;
                $amount_all = $amount_all + $amount;
            }
        }else{
            $active_amount_all = $c_active_amount;
            $amount_all = $c_amount;
        }
        if($c_amount!=$amount_all  ){
            DB::rollBack();
            throw new BusinessException('预算金额拆分必须要等于预算金额');
        }
        if( $c_active_amount!=$active_amount_all){
            DB::rollBack();
            throw new BusinessException('实际金额拆分必须要等于实际金额');
        }
    }


    /**
     *业务详情页面
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public  function detailBusiness($id){
        $businessData=$this->businessRepository->with([
                'businessteams',
                'partner',
                'company',
                'product',
                'contracts',
                'backcash_invoices',
                'expenses_deliveys',
                'payment_expensess',
                'badcashs',

                ])
                ->applyWhere([
                        function ($query)  {
                            $query->whereRaw( session('company_sql'));
                        },
                        function ($query){
                            $query->whereHas('businessteams', function ($subQuery) {
                                $subQuery->whereRaw( session('team_sql') );
                            });
                        },
                ])
                ->find($id);
        //业务的执行总额
        $amount=$this->deliveryRepository->getAmountByAllBusiness($businessData->business_key);
        $businessData->all_team_amount=$amount;//业务的执行总额

        //业务的发票总额
        $invoince_amount=$this->invoiceDeliveryRepository->getBusinessInvoiceSumByKey($businessData->business_key);
        $businessData->invoince_amount=$invoince_amount;//业务的执行总额

        //组织预算小组拆分
        $team_amount=$this->businessTeamModel->whereRaw('business_key="'.$businessData->business_key.'"')->get();


        //合同相关
        $contractKeyArr=[];
        $contractStatusArr=[];
        foreach($businessData->contracts as $ct){
            //if($ct->type!=1){
                $contractKeyArr[$ct->ckey]=$ct->ckey;
                $contractStatusArr[$ct->ckey]=$ct->status_text;
            //}
        }
        $contractInfo['key']=implode(',',$contractKeyArr);
        $contractInfo['status']=$contractStatusArr;
        if(!empty($businessData->parent_contract_id)){
            $contractInfo['key']=$businessData->parent_contract_id.'(框),'.$contractInfo['key'];
            $this->contractRepository->getContractByCKey($contractKeyArr);
        }


        //计算业务的回款状态: 业务小面的各个执行小组只要一个未结清 则业务状态未结清

        $businessData->backcash_status=$this->getBusinessBackcashStatus($businessData->businessteams);

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();

        $audit_edit=$this->permissionJudge->isAllowed('business.audit-edit');
        return view('admin.business.business-detail',['businessData'=>$businessData,
                                                    'team_amount'=>$team_amount,
                                                    'teamDept'=>$teamDept,
                                                    'contractInfo'=>$contractInfo,
													'executive_id'=>$businessData->executive_id,
                                                    'audit_edit'=>$audit_edit,
                ]);
    }

    /**
     *  计算业务的回款状态
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getBusinessBackcashStatus($businessTeam){
        if($businessTeam->isEmpty()){return '未结清';}
        foreach($businessTeam as $d){
            if($d->backcash_status==0){
                return '未结清';
                break;
            }
        }
        return '已结清';
    }

    /**
     *审核操作
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function auditDo(Request $request){
        $id=$request->get('id');//业务id
        $auditstatus=$request->get('type');

        $data['audit_status']=$auditstatus;

        $business=$this->businessRepository->update($id,$data);
        //记录日志
        $auditstatus_text="审核通过";
        if($auditstatus==2){
            $auditstatus_text="审核不通过";
        }
        $this->actionLog->log('business.audit',
                Admin::user()->id,
                [  'id'    =>$id,
                   'company_id' => $business->partner_id,
                   'business_key'  =>$business->business_key   ,
                   'action_text'    =>"业务审核:".$auditstatus_text,
                ]
        );

        return $this->ajaxSuccess('审核成功！');
    }

    /**
     *业务待审核列表
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function auditBusiness(Request $request){
        $viewData=$this->formDataConstruct();

        //页面查询

        $where=$this->seach($request);

        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->where('audit_status','=',0);
            });
        };

        $viewData['listdata'] = $this->businessTeamRepository->with([
                                                'business',
                                                'business.partner',
                                                'business.company',
                                                'deliverys',

                                                ])
                                                ->applyWhere($where)
                                                ->applyOrder('id', 'desc')
                                                ->paginate(20);

        return view('admin.business.audit-list',$viewData);
    }

    /**
     *更新 业务详细 页面的 文本框数据
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updateBusinessApi(Request $request){
        $id=$request->get('id');//业务id
        $name=$request->get('name');
        $value=$request->get('value');

        //查询业务属性
        $businessdata=$this->businessModel->where('id','=',$id)->first();

        if(strpos($name,'#')){//金额拆分更新
            $nameArr=explode('#',$name);
            $data[$nameArr[0]]=$value;
            $this->businessTeamModel->whereRaw('business_key="'.$businessdata->business_key.'" and team="'.$nameArr[1]
                    .'"')
                                    ->update($data);
        }else {
            $data[$name] = $value;
            $this->businessRepository->update($id, $data);
        }
        return $this->ajaxSuccess('更新成功！');
    }

    /**
     * 录入执行额列表
     * @author YangWei<yangwei@foxmail.com>
     * 审核通过的数据才可以录入执行额
     */
    public function addAmountBusiness(Request $request){
        $viewData=$this->formDataConstruct();

        //页面查询

        $where=$this->seach($request);

        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->where('audit_status','=',1);
            });
        };

        $viewData['listdata']= $this->businessTeamRepository->with([
                'business',
                'business.partner',
                'business.company',
                'business.contract',
                'deliverys',
                'invoice_deliveys',
                'expenses_deliveys',//计算支出总额
                'backcash_invoices',//计算回款总额
        ])
                ->applyWhere($where)
                ->applyOrder('id', 'desc')
                ->paginate(20);
        $viewData['audit_edit']=$this->permissionJudge->isAllowed('business.audit-edit');
        return view('admin.business.addamount-list',$viewData);
    }

    /**
     * 列表 删除业务小组
     * 列表中只删除业务下面的小组（如果只有一个小组则联通业务数据一起删除）
     * 删除小组 需要检测是否有关联数据
     * $id  业务小组的ID（business_team）
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteBusiness($id){
        if(empty($id) ){return $this->ajaxError('参数错误！');}
        if($this->deleteBusinessTeam($id)){
            return $this->ajaxSuccess('删除成功！');
        }
        return $this->ajaxError('删除失败！');
    }

    /**
     *  执行单个业务小组的删除操作
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id  业务小组的id
     * $ty 0=列表中删除小组，1=编辑业务的时候删除小组
     * @return bool
     * @throws BusinessException
     */
    public function deleteBusinessTeam($id,$ty=0){
        DB::beginTransaction();//事务开始

        $businessTeamData=$this->businessTeamRepository->find($id);//业务小组数据
        //删除前验证 是否有关联数据
        $this->checkBusinessRelation($businessTeamData);

        //检查该业务有多少个执行小组
        $wh[]=['business_key','=',$businessTeamData->business_key];
        $wh[]=['isshow','=',1];
        $businessTeamNum=$this->businessTeamRepository->applyWhere($wh)->all()->count();

        //1.软删除 业务与小组关联表 business_team数据
        $this->businessTeamModel->where('id','=',$id)->update(['isshow'=>0]);

        //2.软删除 月执行额 表数据 admp_delivery
        $this->deliveryModel->where('team_id','=',$id)->update(['isshow'=>0]);

        //3.更新业务数据中的 实际金额和预算金额（减去本次删除的金额）
        $business=$this->businessRepository->find($businessTeamData->business_id);
        $data['amount']=$business->amount-$businessTeamData->amount;
        $data['active_amount']=$business->active_amount-$businessTeamData->active_amount;

        //4.判断是否是最后一个执行小组  如果是 则更新业务表数据的状态
        if($businessTeamNum<=1 && $ty==0){
            $data['audit_status']=-1;//删除状态
            $data['del_time']    =date('Y-m-d H:i:s',time());
			if(!empty($business->executive_id)){
				$data['executive_id']    =0;
				//更新 执行单那边的关联数据ID
				$executive=$this->executiveRepository->find($business->executive_id);
				$up['business_id']=0;
				$up['status']=2;
				$executive->update($up);
			}
        }
        $this->businessModel->where('business_key','=',$businessTeamData->business_key)->update($data);
        DB::commit();
        return true;
    }

    /**
     *  检测 业务 下面是否还有关联数据
     *  涉及 发票 支出 回款 付款 坏账
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function checkBusinessRelation($businessTeamData)
    {
        $where[]=['team_id','=',$businessTeamData->id];
        $where[]=['isshow','=',1];
        $invoiceNum=$this->invoiceDeliveryRepository->applyWhere($where)->all()->count();

        $backcashNum=$this->backcashInvoiceRepository->applyWhere($where)->all()->count();

        $expensesNum=$this->expensesDeliveyRepository->applyWhere($where)->all()->count();

        $paymentNum=$this->paymentExpensesRepository->applyWhere($where)->all()->count();

        $badcashNum=$this->badcashRepository->applyWhere($where)->all()->count();

        $title='';
        if($invoiceNum>0){
            $title=$title.' 【发票】 ';
        }
        if($backcashNum>0){
            $title=$title.' 【回款】 ';
        }
        if($expensesNum>0){
            $title=$title.' 【支出】 ';
        }
        if($paymentNum>0){
            $title=$title.' 【付款】 ';
        }
        if($badcashNum>0){
            $title=$title.' 【坏账】 ';
        }
        if(!empty($title)){
            $teamDept=$this->dictionaryRepository->getDeptTeam();
            throw new BusinessException($teamDept[$businessTeamData->team].' 关联的 '.$title.'
            数据正在生效中！需全部清除关联数据，才可以执行删除操作!');
        }
        return true;
    }

	/**
	 *  同步业务那边的 客户 合作方 产品 数据
	 *  @author YangWei<yangwei@foxmail.com>
	 *
	 */
	public function synExecutiveData($business_id,$saveData){
		if(empty($business_id) || empty($saveData)){return ;}
		$executiveData=[
				'business_id'=>$business_id,
				'status'=>3,
				'company_id'=>$saveData['company_id'],
				'partner_id'=>$saveData['partner_id'],
				'product_id'=>$saveData['product_id'],
		];

		DB::beginTransaction();//事务开始
		$this->executiveRepository->update($saveData['executive_id'],$executiveData);
		DB::commit();

		return ;
	}

    


}
