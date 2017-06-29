<?php
namespace App\Http\Controllers\Admin;

use App\Models\PaymentExpenses;
use App\Models\UnderPartnersModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use File;
use Validator;
use DB;
use Admin;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;

use App\Repositories\UnderPartnersRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\DictionaryRepository;
use App\Repositories\ExpensesDeliveyRepository;
use App\Models\ExpensesModel;
use App\Models\ExpensesDeliveyModel;
use App\Repositories\PaymentExpensesRepository;
use App\Models\Payment;
use App\Services\Admin\ActionLog;
use App\Services\ExcelMaker;

/**
 * 付款管理
 *
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class PaymentController extends Controller {
    use CommonResponse;

    protected $loginUser;

    protected $deliveryRepository;

    protected $partnersRepository;

    protected $dictionaryRepository;

    protected $underPartnersRepository;

    protected $expensesDeliveyRepository;

    protected $expensesModel;

    protected $paymentExpenses;

    protected $payment;

    protected $paymentExpensesRepository;

    protected $actionLog;
    /**
     *
     */
    public function __construct(AdminGuard $loginUser,
                                PartnersRepository $partnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                ExpensesDeliveyRepository $expensesDeliveyRepository,
                                ExpensesModel $expensesModel,
                                PaymentExpenses $paymentExpenses,
                                Payment $payment,
                                PaymentExpensesRepository $paymentExpensesRepository,
                                ActionLog $actionLog) {
        $this->loginUser = $loginUser->user();
        $this->partnersRepository   =$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->underPartnersRepository=$underPartnersRepository;
        $this->expensesDeliveyRepository=$expensesDeliveyRepository;
        $this->expensesModel=$expensesModel;
        $this->paymentExpenses=$paymentExpenses;
        $this->payment      =$payment;
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
        //加载下游合作方数据
        $viewData['under_partnerData']=$this->underPartnersRepository->all();

        return view('admin.payment.list', $viewData);
    }


    /**
     *录入付款信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function createPayment($partner_id="",$business_key="") {
        //加载上游合作方数据
        $viewData['partnerData']=$this->partnersRepository->getAllPartnersPageList();
        $viewData['partner_id']=$partner_id;
        $viewData['business_key']=$business_key;

        return view('admin.payment.edit', $viewData);
    }

    /**
     *编辑付款信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function editPayment($payment_id="") {
        //加载上游合作方数据
        $viewData['partnerData']=$this->partnersRepository->getAllPartnersPageList();

        //编辑的情况下 加载支出信息
        if(!empty($payment_id)){
            $viewData['paymentData']=$this->payment->where('id','=',$payment_id)->first();

            $paymentExpenses=$this->paymentExpenses->where('payment_id','=',$payment_id)->first();
            $viewData['partner_id']=$paymentExpenses->partner_id;
        }
        return view('admin.payment.edit', $viewData);
    }
    /**
     * 保存录入付款信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function storePayment(Request $request){
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        $where=[];

        if(!empty($partner_id)){
            $where[]=['partner_id', '=', $partner_id];
        }else{
            $partner_id=0;
        }

        if(!empty($business_key)){
            $where[]=['business_key', '=', $business_key];
        }
        $returnParm='/'.$partner_id.'/'.$business_key;

        //付款单基础信息
        $storeData['payment_time']          =$request->get('payment_time');
        $storeData['invoice_num']           =$request->get('invoice_num');
        $storeData['invoice_amount']        =$request->get('invoice_amount');
        $storeData['invoice_type']          =$request->get('invoice_type');
        $storeData['amount']                =$request->get('amount');
        $storeData['remark']                =$request->get('remark');



        $payment_id=$request->get('payment_id');//编辑的时候会有iD传回
        if(!empty($payment_id)){
            if($this->updatePayment($storeData,$request)){
                return $this->ajaxSuccess('修改付款单成功！',['url'=>route('admin.payment.list')]);
                exit;
            }
        }

        //查询出所有执行月的数据
        $data = $this->expensesDeliveyRepository
                ->with(['expenses','business'])
                ->applyWhere($where)
                ->all();

        DB::beginTransaction();//事务开始
        //保存付款主体数据
        $payment=$this->payment->create($storeData);
        $payment_id=$payment->id;

        //更新付款单号
        $updateData['payment_key']     =static::makeNumber($payment_id,'FK');
        $this->payment->where('id','=',$payment_id)->update($updateData);

        //保存支付信息拆分数据
        $saveData['payment_id']     =   $payment_id;

        foreach($data as $d){
            $saveData['payment_key']         =   $updateData['payment_key'];
            $saveData['expenses_id']         =   $d->expenses->id;
            $saveData['expenses_delivery_id']=   $d->id;

            $active_amount                  =   $request->get($d->id.'_text');

            if($active_amount==""){continue;}
            $saveData['active_amount']      =   $active_amount;
            //冗余数据
            $saveData['expenses_type']      =   $d->expenses->expenses_type;
            $saveData['delivery_id']        =   $d->delivey_id;
            $saveData['team_id']            =   $d->team_id;
            $saveData['partner_id']         =   $d->partner_id;
            $saveData['cooperation_type']   =   $d->expenses->cooperation_type;
            $saveData['under_partner_id']   =   $d->expenses->under_partner_id;
            $saveData['team']               =   $d->team;
            $saveData['team_month']         =   $d->team_month;
            $saveData['team_amount']        =   $d->team_amount;
            $saveData['business_key']       =   $d->business_key;
            $this->paymentExpensesRepository->create($saveData);
        }

        //记录日志
        try{
            $this->actionLog->log('payment-expenses.create',
                    Admin::user()->id,
                    [  'id'         =>$payment_id,
                       'amount'     =>$storeData['amount'],
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return $this->ajaxSuccess('录入付款成功！',['url'=>route('admin.payment.add').$returnParm]);
    }

    /**
     *更新支出单信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updatePayment($saveData,$request){
        $payment_id=$request->get('payment_id');//编辑的时候会有iD传回

        $data =$this->paymentExpensesRepository
                ->applyWhere([
                          ['payment_id', '=', $payment_id]
                ])->all();
        DB::beginTransaction();//事务开始

        //保存主体数据
        $this->payment->whereRaw(' id='.$payment_id)->update($saveData);

        //更新付款拆分金额数据
        foreach($data as $d){
            $active_amount              =   $request->get($d->id.'_text');
            if($active_amount==""){
                $this->deletePaymentExpenses($d->id);//删除被修改成0的付款明细
                continue;
            }
            $paymentExpensesData['active_amount']  = $active_amount;
            $this->paymentExpenses->whereRaw(' id='.$d->id )->update($paymentExpensesData);
        }
        //记录日志
        try{
            $this->actionLog->log('payment-expenses.edit',
                    Admin::user()->id,
                    [  'id'         =>$payment_id,
                       'amount'     =>$saveData['amount'],
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return true;
    }


    /**
     *删除付款单比记录
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deletePaymentExpenses($id){
        //删除支出详细关联
        DB::beginTransaction();//事务开始

        $returnData=['url'=>route('admin.payment.list')];

        $this->paymentExpenses->where('id','=',$id)->first()->update(['isshow'=>0]);
        DB::commit();
        return $returnData ;
    }



    public function ajaxdeletePaymentExpenses(Request $request){
        $id=$request->input('id');
        $returnData=$this->deletePaymentExpenses($id);
        return $this->ajaxSuccess('删除成功!',$returnData);
    }



    /**
     *删除付款总单
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deletePayment(Request $request){
        $payment_id=$request->get('id');

        DB::beginTransaction();//事务开始
        //删除  软删除
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        $this->payment->where('id','=',$payment_id)->update($arr);
        //删除支出详细关联
        $this->paymentExpenses->where('payment_id','=',$payment_id)->update(['isshow'=>0]);

        //记录日志
        try{
            $this->actionLog->log('payment-expenses.delete',
                    Admin::user()->id,
                    [  'id'         =>$payment_id,
                       'amount'     =>0,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return $this->ajaxSuccess('删除付款信息成功！',['url'=>route('admin.payment.list')]);
    }

    /**
     * 添加付款时API  查询支出数据的信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function addPayment(Request $request) {
        $business_key = trim($request->get('business_key'));//业务编号
        $partner_id = $request->get('partner_id');//上游合作方id
        $payment_id=$request->get('id');//付款主体信息ID 判断是否是编辑发票金额

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        if(!empty($payment_id)){
            $returnJsonData=$this->editPaymentDataApi($request);
            return json_encode($returnJsonData);
        }
        //查询业务表
        $where=[];
        if (!empty($business_key)) {
            $where []= [ 'business_key','=' , $business_key ] ;
        }
        if (!empty($partner_id)) {
            $where []= [ 'partner_id','=' , $partner_id ] ;
        }

        $where[]=['isshow','=',1];
        //需要在列表显示的数据
        $data = $this->expensesDeliveyRepository
                ->with([
                    'expenses',
                    'delivery',
                    'expenses.under_partner',
                    'payment_expensess',
                    'business',
                    'business.partner',
                 ])
                 ->applyWhere($where )
                 ->applyOrder('updated_at','desc')
                 ->all();
        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月份", "key" => "team_month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "team_amount", "type" => 1],
                ["name" => "支出金额（元）", "key" => "amount", "type" => 1],
                ["name" => "支出类型", "key" => "expenses_type", "type" => 1],
                ["name" => "下游合作方", "key" => "under_partner_id", "type" => 1],
                ["name" => "预付款时间", "key" => "payment_time", "type" => 1],
                ["name" => "付款金额", "key" => "active_amount", "type" => 1],
        ];

        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $table=[];
        foreach($data as $d){
            $business_key=$d->business_key;
            $team=$teamDept[$d->team];
            $month=$d->delivery->month;
            $amount=$d->delivery->amount;
            $expenses_type=$expenses_typeArr[$d->expenses->expenses_type];
            $payment_time=$d->expenses->payment_time;
            $under_partner=$d->expenses->under_partner->company_name;

            $partner=$d->business->partner->company_name;
            //加载已经添加到某个业务执行月上的支出 用于显示
            $payment_amount_ext=0.00;//某个支出单下面已经录入过的付款金额
            foreach($d->payment_expensess as $payment){
                if($payment->isshow!=1){continue;}//这里由于上面的isshow条件加进去无效才进行过滤
                $table[]=[
                        "business_id"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key,"value"=>$partner],
                        "team"=>["key"=>$business_key.$team,"value"=>$team],
                        "team_month"=>["key"=>$business_key.$team.$month,"value"=>$month],
                        "team_amount"=>["key"=>$business_key.$team.$month,"value"=>$amount],
                        "amount"=>["key"=>$d->id,"value"=>$d->active_amount],
                        "expenses_type"=>["key"=>$d->id,"value"=>$expenses_type],
                        "under_partner_id"=>["key"=>$d->id,"value"=>$under_partner],
                        "payment_time"=>["key"=>$d->id,"value"=>$payment_time],
                        "active_amount"=>[
                                "key"=>$payment->id,
                                "value"=>$payment->active_amount,
                                "name"=>'',
                                "type"=>"show",
                        ],
                ];
                $payment_amount_ext=$payment_amount_ext+$payment->active_amount;
            }
            //加载一个新的 未关联的支出 用于填写新的支出数据
            $table[]=[
                    "business_id"=>["key"=>$business_key,"value"=>$business_key],
                    "partner_id" =>["key"=>$business_key,"value"=>$partner],
                    "team"=>["key"=>$business_key.$team,"value"=>$team],
                    "team_month"=>["key"=>$business_key.$team.$month,"value"=>$month],
                    "team_amount"=>["key"=>$business_key.$team.$month,"value"=>$amount],
                    "amount"=>["key"=>$d->id,"value"=>$d->active_amount],
                    "expenses_type"=>["key"=>$d->id,"value"=>$expenses_type],
                    "under_partner_id"=>["key"=>$d->id,"value"=>$under_partner],
                    "payment_time"=>["key"=>$d->id,"value"=>$payment_time],
                    "active_amount"=>[
                            "key"=>"new_".$d->id,
                            "value"=>"",
                            "name"=>$d->id.'_text',
                            "type"=>"add",
                            "max" => round($d->active_amount-$payment_amount_ext,2) ,
                            "tips" =>"输入的付款金额必须小于或等于支出金额！",
                    ],
            ];
        }
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                                                                    'table' =>$table,
                                                                    ]
        ];
        return json_encode($returnJsonData);
    }

    /**
     * 编辑
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function editPaymentDataApi( $request) {

        $payment_id=$request->get('id');//支出ID 判断是否是编辑
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');
        if(empty($payment_id)){
            return $this->ajaxError('未找到数据!');
        }

        $data =$this->paymentExpensesRepository
                ->with(['payment',
                        'delivery',
                        'expenses_delivey',
                        'expenses_delivey.expenses',
                        'expenses_delivey.under_partner',
                        'business.partner',
                ])
                ->applyWhere([
                        ['payment_id', '=', $payment_id],
						['isshow', '=', 1]
                ])->all();
        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $table=[];
        $paymentAmountArr=$this->sumPaymentAmount($partner_id,$business_key);
        foreach($data as $d){

            $business_key=$d->business_key;
            $team=$teamDept[$d->team];
            $month=$d->delivery->month;
            $amount=$d->delivery->amount;
            $expenses_type=$expenses_typeArr[$d->expenses_delivey->expenses->expenses_type];
            $payment_time=$d->expenses_delivey->expenses->payment_time;
            $under_partner=$d->expenses_delivey->under_partner->company_name;
            $artner=    $d->business->partner->company_name;
            $key=$business_key.$d->delivey_id.$d->expenses_delivery_id;
            //dd($d->active_amount."#".$paymentAmountArr[$key]."#".$d->active_amount);
                $table[]=[
                        "business_id"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key,"value"=>$artner],
                        "team"=>["key"=>$business_key.$team,"value"=>$team],
                        "team_month"=>["key"=>$business_key.$team.$month,"value"=>$month],
                        "team_amount"=>["key"=>$business_key.$team.$month,"value"=>$amount],
                        "amount"=>["key"=>$d->id,"value"=>$d->expenses_delivey->active_amount],
                        "expenses_type"=>["key"=>$d->id,"value"=>$expenses_type],
                        "under_partner_id"=>["key"=>$d->id,"value"=>$under_partner],
                        "payment_time"=>["key"=>$d->id,"value"=>$payment_time],
                        "payment_amount_all"=>["key"=>$d->id,"value"=>static::numFormat
                        ($paymentAmountArr[$key]-$d->active_amount)],
                        "active_amount"=>[
                                "key"=>$d->id,
                                "value"=>$d->active_amount,
                                "name"=>$d->id.'_text',
                                "type"=>"edit",
                                "max" =>round($paymentAmountArr[$key]-($paymentAmountArr[$key]-$d->active_amount),2) ,
                                "tips" =>"输入的付款金额必须小于或等于剩余支出金额！",
                        ],
                ];

        }

        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月份", "key" => "team_month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "team_amount", "type" => 1],
                ["name" => "支出金额（元）", "key" => "amount", "type" => 1],
                ["name" => "支出类型", "key" => "expenses_type", "type" => 1],
                ["name" => "下游合作方", "key" => "under_partner_id", "type" => 1],
                ["name" => "预付款时间", "key" => "payment_time", "type" => 1],
                ["name" => "已录入付款金额", "key" => "payment_amount_all", "type" => 1],
                ["name" => "付款金额", "key" => "active_amount", "type" => 1],
        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$table,
        ]
        ];
        return $returnJsonData;
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $request
     * @param $type
     */
    public function getListRes($request,$type){

        $bmonth             =$request->get('bmonth');//执行月份时间段

        $emonth             =$request->get('emonth');

        $btime              =$request->get('btime');//付款时间段

        $etime              =$request->get('etime');

        $under_partner      =$request->get('under_partner');//下游合作方

        $invoice_type       =$request->get('invoice_type');//发票类型

        $business_key       =$request->get('business_key');



        //页面查询-------------------------------------------------------------
        $where=[];
        //执行月份
        if(!empty($bmonth) && !empty($emonth)){
            $where[] = function ($query) use ($bmonth,$emonth) {
                $query->whereBetween('team_month', [$bmonth, $emonth]);
            };
        }
        //付款时间
        if(!empty($btime) && !empty($etime)){
            $where[] =function ($query) use ($btime,$etime) {
                $query->whereHas('payment',
                        function ($query) use ($btime,$etime) {
                            $query->whereBetween('payment_time', [$btime, $etime]);
                        });
            };
        }
        //下游合作方
        if(!empty($under_partner) ){
            $where[] = function ($query) use ($under_partner) {
                $query->whereRaw(' under_partner_id in ('.$under_partner.')');
            };
        }

        //发票类型
        if(!empty($invoice_type) ){
            $where[] =function ($query) use ($invoice_type) {
                $query->whereHas('payment',
                        function ($query) use ($invoice_type) {
                            $query->whereRaw(' invoice_type like "%'.$invoice_type.'%"');
                        });
            };
        }

        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }

        if($type==1){
            return $this->paymentExpensesRepository
                    ->with(['payment',
                                    'delivery',
                                    'business',
                                    'expenses_delivey',
                                    'expenses_delivey.expenses',
                                    'expenses_delivey.under_partner',
                                    'expenses_delivey.under_partner.undercompany'
                            ]
                    )
                    ->applyWhere($where)
                    ->applyOrder('payment_key','desc')
                    ->paginate(20)
                    ->toarray();
        }else if($type==2){
            return $this->paymentExpensesRepository
                    ->with(['payment',
                                    'delivery',
                                    'business',
                                    'expenses_delivey',
                                    'expenses_delivey.expenses',
                                    'expenses_delivey.under_partner',
                                    'expenses_delivey.under_partner.undercompany'
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
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function listDataApi(Request $request){

        $listdata=$this->getListRes($request,1);

        $sum_amount=0.00;
        $amount_array=[];
        $sum_invoice_amount=0.00;
        $invoice_amount_array=[];
        $sum_expenses_amount=0.00;
        $expenses_amount_array=[];
        $sum_team_amount=0.00;
        //-------------------------------------------------------------------------------------
        //表格 标题
        $title = [
                ["name" => "付款单号", "key" => "payment_key", "type" => 1],
                ["name" => "付款金额", "key" => "amount", "type" => 1],
                ["name" => "付款时间", "key" => "payment_time", "type" => 1],
                ["name" => "发票金额", "key" => "invoice_amount", "type" => 1],
                ["name" => "发票编号", "key" => "invoice_num", "type" => 1],
                ["name" => "发票类型", "key" => "invoice_type", "type" => 0],
                ["name" => "支出金额(元)", "key" => "expenses_amount", "type" => 0],
                ["name" => "支出类型", "key" => "expenses_type", "type" => 0],
                ["name" => "下游客户", "key" => "undercompany", "type" => 0],
                ["name" => "下游合作方", "key" => "under_partner", "type" => 0],
                ["name" => "下游合作形式", "key" => "cooperation_type", "type" => 0],
                ["name" => "月执行金额", "key" => "team_amount", "type" => 0],
                ["name" => "执行月份", "key" => "team_month", "type" => 0],
                ["name" => "业务编号", "key" => "business_key", "type" => 0],
                ["name" => "执行小组", "key" => "team", "type" => 0],
                ["name" => "备注", "key" => "remark", "type" => 1],
                ["name" => "操作", "key" => "operate", "type" => 1],
        ];

        //获得支出类型
        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $table=[];
        foreach($listdata['data'] as $k=>$v){
            $expenses_type=(isset($expenses_typeArr[$v['expenses_delivey']['expenses']['expenses_type']]))?
                            $expenses_typeArr[$v['expenses_delivey']['expenses']['expenses_type']]:"";
            //付款信息
            $payment_id     =   $v['payment']['id'];
            $payment_key    =   $v['payment']['payment_key'];
            $amount         =   $v['payment']['amount'];
            $payment_time   =   $v['payment']['payment_time'];
            $invoice_amount =   $v['payment']['invoice_amount'];
            $invoice_num    =   $v['payment']['invoice_num'];
            $invoice_type   =   $v['payment']['invoice_type'];
            $remark         =   $v['payment']['remark'];
            //支出信息
            $expenses_amount    =   $v['expenses_delivey']['expenses']['amount'];
            $cooperation_type   =   $v['expenses_delivey']['expenses']['cooperation_type'];

            $team_amount    =   $v['delivery']['amount'];
            $team_month     =   $v['delivery']['month'];
            $business_key   =   $v['expenses_delivey']['business_key'];
            $team           =   $v['expenses_delivey']['team'];

            //可能会有多个客户
            $undercompany='';
            if(!empty($v['expenses_delivey']['under_partner']['undercompany'])) {
                foreach ($v['expenses_delivey']['under_partner']['undercompany'] as $ck => $cv) {
                    $undercompany .= $cv['company_name'] . ',';
                }
            }
            $under_partner  =   $v['expenses_delivey']['under_partner']['company_name'];


            $opt=[];
            if($v['payment']['isshow']==1){
                $opt=[
                        ["type"=>"","title"=>'<i hidid="" class="icon i-edit" title="编辑"></i>',
                                "url"=>route('admin.payment.edit',$payment_id)],
                        ["type"=>"delete","title"=>'<i hidid="" class="icon i-del" title="删除"></i>',
                                "url"=>"","id"=>$payment_id],
                ];
                $amount_array[$payment_id]=$amount;
                $invoice_amount_array[$payment_id]=$invoice_amount;
                $expenses_amount_array[$payment_id]=$expenses_amount;
                $sum_team_amount=$sum_team_amount+$team_amount;
            }else{
                $opt=[
                        ['type' => '', 'title' => '<font color="#CCCCCC">删除时间'.$v['payment']['deleted_at'].'</font>'],
                ];
            }


            $id=$v['id'];
            $table[]=[
                    "payment_key"=>["key"=>$payment_id,"value"=>$payment_key],
                    "amount" =>["key"=>$payment_id,"value"=>$amount],
                    "payment_time"=>["key"=>$payment_id,"value"=>$payment_time],
                    "invoice_amount"=>["key"=>$payment_id,"value"=>$invoice_amount],
                    "invoice_num"=>["key"=>$payment_id,"value"=>$invoice_num],
                    "invoice_type"=>["key"=>$payment_id,"value"=>$invoice_type],
                    "expenses_amount"=>["key"=>$payment_id,"value"=>$expenses_amount],
                    "expenses_type"=>["key"=>$payment_id,"value"=>$expenses_type],
                    "undercompany"=>["key"=>$id,"value"=>$undercompany],
                    "under_partner"=>["key"=>$id,"value"=>$under_partner],
                    "cooperation_type"=>["key"=>$id,"value"=>$cooperation_type],
                    "team_amount"=>["key"=>$id,"value"=>$team_amount],
                    "team_month"=>["key"=>$id,"value"=>$team_month],
                    "business_key"=>["key"=>$id,"value"=>$business_key],
                    "team"=>["key"=>$id,"value"=>$teamDept[$team]],
                    "remark"=>["key"=>$payment_id,"value"=>$remark],
                    "operate"=>["key"=>$payment_id,"value"=>$opt],

            ];
        }
        if(!empty($amount_array)){
            $sum_amount=array_sum($amount_array);
        }
        if(!empty($invoice_amount_array)){
            $sum_invoice_amount=array_sum($invoice_amount_array);
        }
        if(!empty($expenses_amount_array)){
            $sum_expenses_amount=array_sum($expenses_amount_array);
        }
        $table[]=[
                "payment_key"=>["key"=>'',"value"=>'本页合计:'],
                "amount" =>["key"=>'',"value"=>static::numFormat($sum_amount)],
                "payment_time"=>["key"=>'',"value"=>''],
                "invoice_amount"=>["key"=>'',"value"=>static::numFormat($sum_invoice_amount)],
                "invoice_num"=>["key"=>'',"value"=>''],
                "invoice_type"=>["key"=>'',"value"=>''],
                "expenses_amount"=>["key"=>'',"value"=>static::numFormat($sum_expenses_amount)],
                "expenses_type"=>["key"=>'',"value"=>''],
                "undercompany"=>["key"=>'',"value"=>''],
                "under_partner"=>["key"=>'',"value"=>''],
                "cooperation_type"=>["key"=>'',"value"=>''],
                "team_amount"=>["key"=>'',"value"=>static::numFormat($sum_team_amount)],
                "team_month"=>["key"=>'',"value"=>''],
                "business_key"=>["key"=>'',"value"=>''],
                "team"=>["key"=>'',"value"=>''],
                "remark"=>["key"=>'',"value"=>''],
                "operate"=>["key"=>'',"value"=>''],

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
                "付款单号",
                "付款金额",
                "付款时间",
                "发票金额",
                "发票编号",
                "发票类型",
                "支出金额(元)",
                "支出类型",
                "下游客户",
                "下游合作方",
                "下游合作形式",
                "月执行金额",
                "执行月份",
                "业务编号",
                "执行小组",
                "备注",
                "状态",
        ];
        //格式化数据
        $rows=[];
        $res->map(function ($data) use (&$rows,$teamDept,$expenses_typeArr){
            $status='正常';
            if($data->payment->isshow==0){
                $status='已删除';
            }
            $under_company='';
            if(!empty($data->expenses_delivey->under_partner->undercompany)) {
                foreach ($data->expenses_delivey->under_partner->undercompany as $cv) {
                    $under_company .= $cv->company_name . ',';
                }
            }

            $expenses_type=(isset($expenses_typeArr[$data->expenses_delivey->expenses->expenses_type]))?
                    $expenses_typeArr[$data->expenses_delivey->expenses->expenses_type]:"";

            $rows[]=[
                    $data->payment->payment_key,
                    $data->payment->amount,
                    $data->payment->payment_time,
                    $data->payment->invoice_amount,
                    $data->payment->invoice_num,
                    $data->payment->invoice_type,
                    $data->expenses_delivey->expenses->amount,
                    $expenses_type,
                    $under_company,
                    $data->expenses_delivey->under_partner->company_name,
                    $data->expenses_delivey->expenses->cooperation_type,
                    $data->delivery->amount,
                    $data->delivery->month,
                    $data->expenses_delivey->business_key,
                    $teamDept[$data->expenses_delivey->team],
                    $data->payment->remark,
                    $status,
            ];
        });
        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }


    /**
     *根据合作方和业务key获得下面所所属支出的已录入付款金额
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $partner_id
     * @param $business_key
     * @return array
     */
    public function sumPaymentAmount($partner_id,$business_key=''){
        $where=[];
        //合作方
        if(!empty($partner_id) ){
            if(!is_array($partner_id)){
                $partner_id=[$partner_id];
            }
            $where[] = function ($query) use ($partner_id) {
                $query->whereIn('partner_id', $partner_id);
            };
        }

        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }
        $where[]=['isshow','=',1];
        $data = $this->paymentExpensesRepository
                ->applyWhere($where)
                ->all();
        //计算小组执行月的发票总额
        $mont_invoice_arr=[];
        foreach($data as $d){
            $key=$d->business_key.$d->delivey_id.$d->expenses_delivery_id;
            if(!isset($mont_invoice_arr[$key])){
                $mont_invoice_arr[$key]=$d->active_amount;
            }else{
                $mont_invoice_arr[$key]=$mont_invoice_arr[$key]+$d->active_amount;
            }
        }
        return $mont_invoice_arr;
    }



}
