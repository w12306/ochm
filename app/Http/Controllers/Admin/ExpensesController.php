<?php
namespace App\Http\Controllers\Admin;

use App\Models\PaymentExpenses;
use App\Models\UnderPartnersModel;
use App\Repositories\PaymentExpensesRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use File;
use Validator;
use DB;

use Common\Packages\Admin\Contracts\Guard as AdminGuard;

use App\Repositories\UnderPartnersRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\DictionaryRepository;
use App\Repositories\ExpensesDeliveyRepository;
use App\Models\ExpensesModel;
use App\Models\ExpensesDeliveyModel;
use App\Services\Admin\ActionLog;
use App\Services\ExcelMaker;
use Admin;

/**
 * 支出管理
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class ExpensesController extends Controller {
    use CommonResponse;

    protected $loginUser;

    protected $deliveryRepository;

    protected $partnersRepository;

    protected $dictionaryRepository;

    protected $underPartnersRepository;

    protected $expensesDeliveyRepository;

    protected $expensesModel;

    protected $expensesDeliveyModel;

    protected $paymentExpensesRepository;

    protected $actionLog;
    /**
     *
     */
    public function __construct(AdminGuard $loginUser,
                                DeliveryRepository $deliveryRepository,
                                PartnersRepository $partnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                ExpensesDeliveyRepository $expensesDeliveyRepository,
                                ExpensesModel $expensesModel,
                                ExpensesDeliveyModel $expensesDeliveyModel,
                                PaymentExpensesRepository $paymentExpensesRepository,
                                ActionLog $actionLog) {
        $this->loginUser = $loginUser->user();
        $this->deliveryRepository = $deliveryRepository;
        $this->partnersRepository   =$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->underPartnersRepository=$underPartnersRepository;
        $this->expensesDeliveyRepository=$expensesDeliveyRepository;
        $this->expensesModel=$expensesModel;
        $this->expensesDeliveyModel=$expensesDeliveyModel;
        $this->paymentExpensesRepository=$paymentExpensesRepository;
        $this->actionLog                     = $actionLog;
    }

    /**
     * 支出查询列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request) {
        //加载下游游合作方数据
        $viewData['under_partnerData']=$this->underPartnersRepository->all();
        //加载支出类型
        $viewData['expenses_type']=$this->dictionaryRepository->returnList('expenses_type');

        return view('admin.expenses.list', $viewData);
    }


    /**
     *录入支出信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function createExpenses($partner_id="",$business_key="") {
        //加载上游合作方数据
        $viewData['partnerData']=$this->partnersRepository->getAllPartnersPageList();
        //加载下游合作方数据
        $viewData['under_partnerData']=$this->underPartnersRepository->all();
        //加载下游合作方式数据
        $viewData['cooperation']=$this->dictionaryRepository->returnList('cooperation_type');
        //加载支出类型
        $viewData['expenses_type']=$this->dictionaryRepository->returnTypeArray('expenses_type');
        $viewData['partner_id']=$partner_id;
        $viewData['business_key']=$business_key;

        return view('admin.expenses.edit', $viewData);
    }

    /**
     *录入支出信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function editExpenses($expenses_id="") {
        //加载上游合作方数据
        $viewData['partnerData']=$this->partnersRepository->getAllPartnersPageList();
        //加载下游合作方数据
        $viewData['under_partnerData']=$this->underPartnersRepository->all();
        //加载下游合作方式数据
        $viewData['cooperation']=$this->dictionaryRepository->returnList('cooperation_type');
        //加载支出类型
        $viewData['expenses_type']=$this->dictionaryRepository->returnTypeArray('expenses_type');

        //编辑的情况下 加载支出信息
        if(!empty($expenses_id)){
            $expensesData=$this->expensesModel->where('id','=',$expenses_id)->first();
            $viewData['expensesData']=$expensesData;
            $expensesDeliveyData=$this->expensesDeliveyModel->where('expenses_id','=',$expenses_id)->first();
            $viewData['partner_id']=$expensesDeliveyData->partner_id;
        }

        return view('admin.expenses.edit', $viewData);
    }

    /**
     * 保存录入支出信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function storeExpenses(Request $request){
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        $where=' 1=1 ';
        if(!empty($partner_id)){
            $where.=' and partner_id = '.$partner_id;
        }else{
            $partner_id=0;
        }

        if(!empty($business_key)){
            $where .=' and  business_key ="'. $business_key.'"';
        }
        $returnParm='/'.$partner_id.'/'.$business_key;

        //支出单基础信息
        $storeData['expenses_type']          =$request->get('expenses_type');
        $storeData['payment_time']           =$request->get('payment_time');
        $storeData['cooperation_type']       =$request->get('cooperation_type');
        $storeData['under_partner_id']       =$request->get('under_partner_id');
        $storeData['amount']                 =$request->get('amount');
        $storeData['contract_key']           =$request->get('contract_key');
        $storeData['contract_btime']         =$request->get('btime');
        $storeData['contract_etime']         =$request->get('etime');
        $storeData['remark']                 =$request->get('remark');


        $expenses_id=$request->get('expenses_id');//编辑的时候会有iD传回
        if(!empty($expenses_id)){
            if($this->updateExpenses($storeData,$request)){
                return $this->ajaxSuccess('修改支出单成功！',['url'=>route('admin.expenses.list-expenses')]);
                exit;
            }
        }

        //查询出所有执行月的数据
        $data = $this->deliveryRepository->with(['business'])
                ->applyWhere([
                        function ($query) use ($where) {
                            $query->whereHas('business', function ($subQuery) use ($where) {
                                $subQuery->whereRaw($where);
                            });
                        },
                        ['isshow', '=', 1]
                ])->all();

        DB::beginTransaction();//事务开始
        //保存支出主体数据
        $expenses=$this->expensesModel->create($storeData);
        $expenses_id=$expenses->id;

        //更新支出单号
        $updateData['expenses_key']     =static::makeNumber($expenses_id,'ZC');
        $this->expensesModel->where('id','=',$expenses_id)->update($updateData);

        //保存支付信息拆分数据
        $saveData['expenses_id']     =   $expenses_id;
        foreach($data as $d){
            $saveData['delivey_id']         =   $d->id;
            $saveData['team_id']            =   $d->team_id;
            $active_amount                  =   $request->get($d->id.'_text');
            if($active_amount==""){continue;}
            $saveData['active_amount']      =   $active_amount;
            //冗余数据
            $saveData['expenses_key']       =   $updateData['expenses_key'];
            $saveData['under_partner_id']   =   $storeData['under_partner_id'];
            $saveData['partner_id']         =   $d->business->partner_id;
            $saveData['product_id']         =   $d->business->product_id;
            $saveData['team']               =   $d->team;
            $saveData['team_month']         =   $d->month;
            $saveData['team_amount']        =   $d->amount;
            $saveData['business_key']       =   $d->business->business_key;

            $this->expensesDeliveyRepository->create($saveData);
        }
        //记录日志
        try{
            $this->actionLog->log('expenses.create',
                    Admin::user()->id,
                    [  'id'         =>$expenses_id,
                       'amount'     =>$storeData['amount'] ,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }

        DB::commit();
        return $this->ajaxSuccess('录入支出成功！',['url'=>route('admin.expenses.add-expenses').$returnParm]);
    }

    /**
     *更新支出单信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function updateExpenses($saveData,$request){
        $expenses_id=$request->get('expenses_id');//编辑的时候会有iD传回


        //查询出所有执行月的数据
        $data =$this->expensesDeliveyRepository
                ->with(['expenses'])
                ->applyWhere([
                      ['expenses_id', '=', $expenses_id]
                ])->all();

        DB::beginTransaction();//事务开始

        //保存主体数据
        $this->expensesModel->whereRaw(' id='.$expenses_id)->update($saveData);

        //更新支出拆分金额数据
        foreach($data as $d){
            $active_amount              =   $request->get($d->id.'_text');
            if($active_amount==""){continue; }
            $storeData['under_partner_id']       =$request->get('under_partner_id');
            $storeData['active_amount']  = $active_amount;

            $expensesDelivey=$this->expensesDeliveyRepository->find($d->id);
            $expensesDelivey->update($storeData);
        }
        //记录日志
        try{
            $this->actionLog->log('expenses.edit',
                    Admin::user()->id,
                    [  'id'         =>$expenses_id,
                       'amount'     =>$saveData['amount'] ,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return true;
    }

    /**
     *删除支出明细
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function deleteExpensesDelivey($id){

        DB::beginTransaction();//事务开始
        $expensesDelivey=$this->expensesDeliveyRepository->find($id);
        $expensesDelivey->update(['isshow'=>0]);
        DB::commit();
        return '';
    }

    /**
     * 删除支出明细
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxdeleteExpensesDelivey(Request $request){
        $id=$request->input('id');
        $returnData=$this->deleteExpensesDelivey($id);

        return $this->ajaxSuccess('删除成功!',$returnData);
    }
    /**
     *删除支出
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function deleteExpenses(Request $request ){
        $expenses_id=$request->get('id');
        DB::beginTransaction();//事务开始
        //删除前检查 下属有没有关联的付款单
        if($this->paymentExpensesRepository->findByExpensesID($expenses_id)>0){
            return $this->ajaxError('检测到该支出单有未删除的付款单据，请先删除付款单据！');
        }
        //删除支出主体(软删除)
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        $this->expensesModel->where('id','=',$expenses_id)->update($arr);
        //删除支出详细关联
        $this->expensesDeliveyModel->where('expenses_id','=',$expenses_id)->get()->map( function ($data){
            $this->deleteExpensesDelivey($data['id']);return true;
        });
        //记录日志
        try{
            $this->actionLog->log('expenses.delete',
                    Admin::user()->id,
                    [  'id'         =>$expenses_id,
                       'amount'     =>0,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return $this->ajaxSuccess('删除支出信息成功！',['url'=>route('admin.expenses.list-expenses')]);
    }



    /**
     * 添加支出时  查询月执行额 数据的信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function addExpenses(Request $request) {
        $business_key = trim($request->get('business_key'));//业务编号
        $partner_id = $request->get('partner_id');//上游合作方id
        $expenses_id=$request->get('id');//支出主体信息ID 判断是否是编辑发票金额

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        if(!empty($expenses_id)){
            $returnJsonData=$this->editExpensesDataApi($request);
            return json_encode($returnJsonData);
        }
        //查询业务表
        $where=' 1=1 ';
        if (!empty($business_key)) {
            $where .= ' and business_key="' . $business_key . '" ';
        }
        if (!empty($partner_id)) {
            $where .= ' and partner_id=' . $partner_id ;
        }


        $data = $this->deliveryRepository->with([
                                                'business',
                                                'business.partner',
                                                'business.product',
                                                'expenses_deliveys',
        ])
                                            ->applyWhere([
                                                    function ($query) use ($where) {
                                                        $query->whereHas('business', function ($subQuery) use ($where) {
                                                            $subQuery->whereRaw($where);
                                                        });
                                                    },
                                                    ['isshow', '=', 1]
                                            ])->all();
        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($data as $d){
            $business_key=$d->business->business_key;
            $partner=$d->business->partner->company_name;
            $product_name=$d->business->product->name;
            $product_type=$d->business->product->type;
            $team=$teamDept[$d->team];
            $month=$d->month;
            $amount=$d->amount;
            //加载已经添加到某个业务执行月上的支出 用于显示
            foreach($d->expenses_deliveys as $expenses){
                $table[]=[
                        "business_id"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key,"value"=>$partner],
                        "team"=>["key"=>$business_key.$team,"value"=>$team],
                        "product_name"=>["key"=>$business_key.$team,"value"=>$product_name],
                        "product_type"=>["key"=>$business_key.$team,"value"=>$product_type],
                        "backcash_status"=>["key"=>$d->id,"value"=>$d->businessTeam->backcashstatus_text],
                        "month"=>["key"=>$d->id,"value"=>$month],
                        "month_amount"=>["key"=>$d->id,"value"=>$amount],
                        "active_amount"=>[
                                "key"=>$expenses->id,
                                "value"=>$expenses->active_amount,
                                "name"=>'',
                                "type"=>"show",
                        ],
                ];
            }
            //加载一个新的 未关联的支出 用于填写新的支出数据
            $table[]=[
                    "business_id"=>["key"=>$business_key,"value"=>$business_key],
                    "partner_id" =>["key"=>$business_key,"value"=>$partner],
                    "team"=>["key"=>$business_key.$team,"value"=>$team],
                    "product_name"=>["key"=>$business_key.$team,"value"=>$product_name],
                    "product_type"=>["key"=>$business_key.$team,"value"=>$product_type],
                    "backcash_status"=>["key"=>$d->id,"value"=>$d->businessTeam->backcashstatus_text],
                    "month"=>["key"=>$d->id,"value"=>$month],
                    "month_amount"=>["key"=>$d->id,"value"=>$amount],
                    "active_amount"=>[
                            "key"=>"new_".$d->id,
                            "value"=>"",
                            "name"=>$d->id.'_text',
                            "type"=>"add",
                    ],
            ];
        }

        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "产品名称", "key" => "product_name", "type" => 1],
                ["name" => "产品类型", "key" => "product_type", "type" => 1],
                ["name" => "回款状态", "key" => "backcash_status", "type" => 1],
                ["name" => "执行月份", "key" => "month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "month_amount", "type" => 1],
                ["name" => "支出金额（元）", "key" => "active_amount", "type" => 1],
        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                                                                    'table' =>$table,
                                                                    ]
        ];
        return json_encode($returnJsonData);
    }

    /**
     * 编辑支出时  查询 支出单子信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function editExpensesDataApi( $request) {

        $expenses_id=$request->get('id');//支出ID 判断是否是编辑

        if(empty($expenses_id)){
            return $this->ajaxError('未找到数据!');
        }

        $data =$this->expensesDeliveyRepository->with([
            'business_team',
            'expenses',
            'product',
            'delivery',
            'business.partner',
        ])
        ->applyWhere([
            ['expenses_id', '=', $expenses_id]
        ])->all();


        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($data as $d){
            $business_key=$d->business_key;
            $partner=$d->business->partner->company_name;
            $team=$d->team;
            $month=$d->delivery->month;
            $team_amount=$d->delivery->amount;
            $product_name=$d->product->name;
            $product_type=$d->product->type;

            $backcash_status=($d->business_team->backcash_status==1)?'已结清':'未结清';//回款状态

            //加载一个新的 未关联的支出 用于填写新的支出数据
            $table[]=[
                    "business_id"=>["key"=>$business_key,"value"=>$business_key],
                    "partner_id" =>["key"=>$business_key,"value"=>$partner],
                    "team"=>["key"=>$business_key.$team,"value"=>$teamDept[$team]],
                    "product_name"=>["key"=>$business_key.$team,"value"=>$product_name],
                    "product_type"=>["key"=>$business_key.$team,"value"=>$product_type],
                    "backcash_status"=>["key"=>$d->id,"value"=>$backcash_status],
                    "month"=>["key"=>$d->id,"value"=>$month],
                    "month_amount"=>["key"=>$d->id,"value"=>$team_amount],
                    "active_amount"=>[
                            "key"=>$d->id,
                            "value"=>$d->active_amount,
                            "name"=>$d->id.'_text',
                            "type"=>"edit",
                    ],
            ];

        }


        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "产品名称", "key" => "product_name", "type" => 1],
                ["name" => "产品类型", "key" => "product_type", "type" => 1],
                ["name" => "回款状态", "key" => "backcash_status", "type" => 1],
                ["name" => "执行月份", "key" => "month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "month_amount", "type" => 1],
                ["name" => "支出金额（元）", "key" => "active_amount", "type" => 1],
        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$table,
        ]
        ];
        return $returnJsonData;
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $request
     * @param $type
     * @return \Illuminate\Database\Eloquent\Collection|mixed|string
     */
    public function getListRes($request,$type){
        $bmonth             =$request->get('bmonth');//执行月份时间段
        $emonth             =$request->get('emonth');
        $under_partner      =$request->get('under_partner');//下游合作方

        $expend_type        =$request->get('expend_type');//支出类型
        $business_key       =$request->get('business_key');



        //页面查询-------------------------------------------------------------
        $where=[];
        //执行月份
        if(!empty($bmonth) && !empty($emonth)){
            $where[] = function ($query) use ($bmonth,$emonth) {
                $query->whereBetween('team_month', [$bmonth, $emonth]);
            };
        }
        //下游合作方
        if(!empty($under_partner) ){
            $where[] = function ($query) use ($under_partner) {
                $query->whereRaw(' under_partner_id in ('.$under_partner.')');
            };
        }

        //支出类型
        if(!empty($expend_type) ){
            $where[] =function ($query) use ($expend_type) {
                $query->whereHas('expenses',
                        function ($query) use ($expend_type) {
                            $query->whereIn('expenses_type', $expend_type);
                        });
            };
        }

        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }
        //-------------------------------------------------------------------------------------
        if($type==1){
            return $this->expensesDeliveyRepository
                    ->with(['business',
                                    'business_team',
                                    'business.product',
                                    'expenses',
                                    'delivery',
                                    'expenses.payment_expensess',
                                    'expenses.under_partner',
                                    'expenses.under_partner.undercompany',
                            ]
                    )
                    ->applyWhere($where)
                    ->applyOrder('updated_at','desc')
                    ->paginate(20)
                    ->toarray();
        }else if($type==2){
            return $this->expensesDeliveyRepository
                    ->with(['business',
                                    'business_team',
                                    'business.product',
                                    'expenses',
                                    'delivery',
                                    'expenses.payment_expensess',
                                    'expenses.under_partner',
                                    'expenses.under_partner.undercompany',
                            ]
                    )
                    ->applyWhere($where)
                    ->applyOrder('updated_at','desc')
                    ->all();
        }
        return '';
    }

    /**
     *支出列表页面 获取生成表格的方法
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function listDataApi(Request $request){

        $listdata=$this->getListRes($request,1);
        $sum_amount=0.00;
        $amount_array=[];
        $sum_active_amount=0.00;
        $sum_team_amount=0.00;

        //获得支出类型数据
        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');
        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($listdata['data'] as $k=>$v){
            //发票基本信息
            $expenses_id    =   $v['expenses']['id'];
            $expenses_key   =   $v['expenses']['expenses_key'];
            $expenses_type  =   $expenses_typeArr[$v['expenses']['expenses_type']];
            $cooperation_type=  $v['expenses']['cooperation_type'];
            $contract_key   =   $v['expenses']['contract_key'];
            $amount         =   $v['expenses']['amount'];//支出单总额
            $remark         =   $v['expenses']['remark'];

            $product_name   =   $v['business']['product']['name'];
            $product_type   =   $v['business']['product']['type'];

            $under_partner  =   $v['expenses']['under_partner']['company_name'];
            $under_company  =   '';
            foreach($v['expenses']['under_partner']['undercompany'] as $ck=>$cv){
                $under_company .=$cv['company_name'].',';
            }
            $backcash_status=($v['business_team']['backcash_status']==1)?'已结清':'未结清';//回款状态
            //计算支出单付款状态
            $payment_status='未付清';
            if(!empty($v['expenses']['payment_expensess'])){
                $payment=0.00;
                foreach($v['expenses']['payment_expensess'] as $pk=>$pv){
                    $payment=$payment+$pv['active_amount'];
                }
                if($payment>=$amount){
                    $payment_status='已付清';
                }
            }
            $opt=[];
            if($v['expenses']['isshow']==1){
                $opt=[
                        ["type"=>"","title"=>'<i hidid="" class="icon i-edit" title="编辑"></i>',
                                "url"=>route('admin.expenses.edit-expenses',
                                        $expenses_id)],
                        ["type"=>"delete","title"=>'<i hidid="" class="icon i-del" title="删除"></i>',
                                "url"=>"","id"=>$expenses_id],
                ];
                $amount_array[$expenses_key]=$amount;
                $sum_active_amount=$sum_active_amount+$v['active_amount'];
                $sum_team_amount=$sum_team_amount+$v['delivery']['amount'];
            }else{
                $opt=[
                        ['type' => '', 'title' => '<font color="#CCCCCC">删除时间'.$v['expenses']['deleted_at'].'</font>'],
                    ];
            }

            $id=$v['id'];
            $table[]=[
                    "expenses_key"=>["key"=>$expenses_key,"value"=>$expenses_key],
                    "expenses_type" =>["key"=>$expenses_key,"value"=>$expenses_type],
                    "under_company"=>["key"=>$expenses_key,"value"=>$under_company],
                    "under_partner"=>["key"=>$expenses_key,"value"=>$under_partner],
                    "cooperation_type"=>["key"=>$expenses_key,"value"=>$cooperation_type],
                    "contract_key"=>["key"=>$expenses_key,"value"=>$contract_key],
                    "payment_status"=>["key"=>$expenses_key,"value"=>$payment_status],
                    "amount"=>["key"=>$expenses_key,"value"=>$amount],
                    "active_amount"=>["key"=>$id,"value"=>$v['active_amount']],
                    "team_amount"=>["key"=>$id,"value"=>$v['delivery']['amount']],
                    "team_month"=>["key"=>$id,"value"=>$v['delivery']['month']],
                    "business_key"=>["key"=>$id,"value"=>$v['business_key']],
                    "team"=>["key"=>$id,"value"=>$teamDept[$v['team']]],
                    "product_name"=>["key"=>$id,"value"=>$product_name],
                    "product_type"=>["key"=>$id,"value"=>$product_type],
                    "backcash_status"=>["key"=>$id,"value"=>$backcash_status],
                    "remark"=>["key"=>$expenses_key,"value"=>$remark],
                    "operate"=>["key"=>$expenses_key,"value"=>$opt],

            ];
        }
        if(!empty($amount_array)){
            $sum_amount=array_sum($amount_array);
        }
        $table[]=[
                "expenses_key"=>["key"=>'',"value"=>'本页合计:'],
                "expenses_type" =>["key"=>'',"value"=>''],
                "under_company"=>["key"=>'',"value"=>''],
                "under_partner"=>["key"=>'',"value"=>''],
                "cooperation_type"=>["key"=>'',"value"=>''],
                "contract_key"=>["key"=>'',"value"=>''],
                "payment_status"=>["key"=>'',"value"=>''],
                "amount"=>["key"=>'',"value"=>static::numFormat($sum_amount)],
                "active_amount"=>["key"=>'',"value"=>static::numFormat($sum_active_amount)],
                "team_amount"=>["key"=>'',"value"=>static::numFormat($sum_team_amount)],
                "team_month"=>["key"=>'',"value"=>''],
                "business_key"=>["key"=>'',"value"=>''],
                "team"=>["key"=>'',"value"=>''],
                "product_name"=>["key"=>'',"value"=>''],
                "product_type"=>["key"=>'',"value"=>''],
                "backcash_status"=>["key"=>'',"value"=>''],
                "remark"=>["key"=>'',"value"=>''],
                "operate"=>["key"=>'',"value"=>''],

        ];
        //表格 标题
        $title = [
                ["name" => "支出单号", "key" => "expenses_key", "type" => 1],
                ["name" => "支出类型", "key" => "expenses_type", "type" => 1],
                ["name" => "下游客户", "key" => "under_company", "type" => 1],
                ["name" => "下游合作方", "key" => "under_partner", "type" => 1],
                ["name" => "下游合作形式", "key" => "cooperation_type", "type" => 1],
                ["name" => "合同编号", "key" => "contract_key", "type" => 0],
                ["name" => "付款状态", "key" => "payment_status", "type" => 0],
                ["name" => "支出金额", "key" => "amount", "type" => 0],
                ["name" => "月执行额下的支出金额", "key" => "active_amount", "type" => 0],
                ["name" => "月执行金额", "key" => "team_amount", "type" => 0],
                ["name" => "执行月份", "key" => "team_month", "type" => 0],
                ["name" => "业务编号", "key" => "business_key", "type" => 0],
                ["name" => "执行小组", "key" => "team", "type" => 0],
                ["name" => "产品名称", "key" => "product_name", "type" => 0],
                ["name" => "产品类型", "key" => "product_type", "type" => 0],
                ["name" => "回款状态", "key" => "backcash_status", "type" => 0],
                ["name" => "备注", "key" => "remark", "type" => 1],
                ["name" => "操作", "key" => "operate", "type" => 1],

        ];
        //分页数据
        $page=['total'=>$listdata['total'], 'perpage'=>$listdata['per_page'],'curpage'=>$listdata['current_page']];
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                                                                    'table' =>$table,
                                                                    'page'  =>$page,
                                                                    ]
        ];
        return json_encode($returnJsonData);

    }

    /**
     * 列表导出Excel
     *
     * @param Request    $request
     * @param ExcelMaker $excelMaker
     */
    public function getExportExcel(Request $request, ExcelMaker $excelMaker)
    {
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();

        //获得支出类型数据
        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');

        $res=$this->getListRes($request,2);

        //表头
        $headers = [
                "支出单号" ,
                "支出类型" ,
                "下游客户"  ,
                "下游合作方"  ,
                "下游合作形式"  ,
                "合同编号"  ,
                "付款状态"  ,
                "支出金额"  ,
                "月执行额下的支出金额"  ,
                "月执行金额" ,
                "执行月份"  ,
                "业务编号"  ,
                "执行小组"  ,
                "产品名称"  ,
                "产品类型" ,
                "回款状态" ,
                "备注"  ,
                "状态"  ,
        ];
        //格式化数据
        $rows=[];
        $res->map(function ($data) use (&$rows,$teamDept,$expenses_typeArr){
            $status='正常';
            if($data->expenses->isshow==0){
                $status='已删除';
            }
            $under_company='';
            foreach($data->expenses->under_partner->undercompany as $cv){
                $under_company .=$cv->company_name.',';
            }

            $amount         =   $data->expenses->amount;//支出单总额

            //计算支出单付款状态
            $payment_status='未付清';
            if(!empty($data->expenses->payment_expensess)){
                $payment=0.00;
                foreach($data->expenses->payment_expensess as $pv){
                    $payment=$payment+$pv->active_amount;
                }
                if($payment>=$amount){
                    $payment_status='已付清';
                }
            }

            $backcash_status=($data->business_team->backcash_status==1)?'已结清':'未结清';//回款状态

            $rows[]=[
                    $data->expenses->expenses_key,
                    $expenses_typeArr[$data->expenses->expenses_type],
                    $under_company,
                    $data->expenses->under_partner->company_name,
                    $data->expenses->cooperation_type,
                    $data->expenses->contract_key,
                    $payment_status,
                    $amount,
                    $data->active_amount,
                    $data->delivery->amount,
                    $data->delivery->month,
                    $data->business_key,
                    $teamDept[$data->team],
                    $data->business->product->name,
                    $data->business->product->type,
                    $backcash_status,
                    $data->expenses->remark,
                    $status,
            ];
        });
        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }



}
