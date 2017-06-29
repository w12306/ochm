<?php
namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use File;
use Mockery\CountValidator\Exception;
use Validator;
use DB;
use Admin;
use App\Repositories\InvoiceDeliveryRepository;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;

use App\Repositories\BusinessRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\DictionaryRepository;
use App\Models\InvoiceDeliveyModel;
use App\Repositories\BackcashInvoiceRepository;
use App\Models\BackcashModel;
use App\Models\BackcashInvoiceModel;
use App\Services\Admin\ActionLog;
use App\Services\ExcelMaker;
/**
 * 回款管理
 *
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class BackcashController extends Controller {
    use CommonResponse;

    protected $loginUser;

    protected $invoiceDeliveryRepository;

    protected $businessRepository;

    protected $partnersRepository;

    protected $dictionaryRepository;

    protected $invoiceDeliveyModel;

    protected $backcashInvoiceRepository;

    protected $backcashModel;

    protected $backcashInvoiceModel;

    private    $actionLog;
    /**
     *
     */
    public function __construct(AdminGuard $loginUser,
                                InvoiceDeliveryRepository $invoiceDeliveryRepository,
                                BusinessRepository $businessRepository,
                                PartnersRepository $partnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                InvoiceDeliveyModel $invoiceDeliveyModel,
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                BackcashModel $backcashModel,
                                BackcashInvoiceModel $backcashInvoiceModel,
                                ActionLog $actionLog) {

        $this->loginUser = $loginUser->user();
        $this->invoiceDeliveryRepository = $invoiceDeliveryRepository;
        $this->businessRepository = $businessRepository;
        $this->partnersRepository   =$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->invoiceDeliveyModel  =$invoiceDeliveyModel;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
        $this->backcashModel            =$backcashModel;
        $this->backcashInvoiceModel     =$backcashInvoiceModel;
        $this->actionLog            =$actionLog;
    }

    /**
     * 回款查询列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request) {
        //加载合作方数据
        $viewData['partnerData']=$this->partnersRepository->getPartnersList();
        return view('admin.backcash.list', $viewData);
    }

    /**
     *录入回款信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function createBackcash($partner_id="",$business_key="") {
        $partner_id =trim($partner_id);
        $viewData['partner_id']=$partner_id;
        $viewData['business_key']=$business_key;
        $viewData['backcash_id']="";

        $validator = Validator::make(
                [
                        'partner_id' => $partner_id,
                ],
                [
                        'partner_id' => 'integer',
                ]
        );
        if ($validator->fails())
        {
            echo '<script>alert("参数不符合规则!");history.back(-1);</script>';
        }

        //加载合作方数据
        $viewData['partnerData']=$this->partnersRepository->getPartnersList();

        //加载银行信息
        $viewData['bankData']=$this->dictionaryRepository->returnTypeArray('bank');

        //加载回款方式
        $viewData['backcash_typeData']=$this->dictionaryRepository->returnTypeArray('backcash_type');

        return view('admin.backcash.edit', $viewData);
    }

    /**
     *  编辑录回款
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function editBackcash($backcash_id=0){
        $viewData['backcash_id']=$backcash_id;
        $validator = Validator::make(
                [
                        'backcash_id' => $backcash_id,
                ],
                [
                        'backcash_id' => 'integer',
                ]
        );
        if ($validator->fails())
        {
            echo '<script>alert("参数不符合规则!");history.back(-1);</script>';
        }
        //编辑的情况下 获得这个发票所属的合作方
        $backcashInvoice=$this->backcashInvoiceModel->where('backcash_id','=',$backcash_id)->first();
        $viewData['partner_id'] =$backcashInvoice->partner_id;

        //编辑的情况下 加载发票信息
        $viewData['backcashData']=$this->backcashModel->where('id','=',$backcash_id)->first();
        //加载银行信息
        $viewData['bankData']=$this->dictionaryRepository->returnTypeArray('bank');

        //加载回款方式
        $viewData['backcash_typeData']=$this->dictionaryRepository->returnTypeArray('backcash_type');

        //加载合作方数据
        $viewData['partnerData']=$this->partnersRepository->getPartnersList();

        return view('admin.backcash.edit', $viewData);
    }
    /**
     * 保存录入回款信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function storeBackcash(Request $request){
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        $where=[];
        if (!empty($partner_id)) {
            $where[]= ['partner_id','=',$partner_id];
        }else{
            $partner_id=0;
        }

        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->whereRaw(session('company_sql'));
            });
        };
        $where[]=function ($query)  {
            $query->whereHas('business_team', function ($subQuery) {
                $subQuery->whereRaw(session('team_sql'));
            });
        };


        if (!empty($business_key)) {
            $where[]= ['business_key','=',$business_key];
        }
        $rebackPrm="/".$partner_id.'/'.$business_key;


        $active_amount      =$request->get('amount');
        $backtime           =$request->get('backtime');
        $bank               =$request->get('bank');
        $backtype           =$request->get('backtype');
        $remark             =$request->get('remark');


        $backData['amount']         =$active_amount;
        $backData['backtime']       =$backtime;
        $backData['bank']           =$bank;
        $backData['backtype']       =$backtype;
        $backData['remark']         =$remark;

        //数据格式验证
        if($active_amount==0){return $this->ajaxError('录入的数据有误!');}
        //查询出所有发票执行月的数据
        $data = $this->invoiceDeliveryRepository
                ->with(['backcash_invoices',
                        'invoice',
                        'business',
                        'business_team',
                ])
                ->applyWhere($where)
                ->all();

        $backcash_id=$request->get('backcash_id');//编辑的时候会有iD传回
        if(!empty($backcash_id)){
            if($this->updateBackcash($backData,$request,$data)){
                return $this->ajaxSuccess('修改回款单成功！',['url'=>route('admin.backcash.list')]);
                exit;
            }
        }



        DB::beginTransaction();//事务开始

        //保存回款单主体数据
        $backcash=$this->backcashModel->create($backData);
        $backcash_id=$backcash->id;

        //更新回款单号
        $backUpData['backcash_key']    =static::makeNumber($backcash_id,'HK');
        $this->backcashModel->where('id','=',$backcash_id)->update($backUpData);

        //保存回款单金额拆分数据
        $saveData['backcash_id']     =   $backcash_id;
        foreach($data as $d){
            $saveData['invoice_delivery_id']     =   $d->id;
            $active_amount              =   $request->get($d->id.'_text');
            if($active_amount==""){continue;}
            //冗余数据
            $saveData['invoice_id']    =   $d->invoice_id;
            $saveData['invoice_key']    =   $d->invoice_key;
            $saveData['invoice_amount'] =   $d->invoice->amount;
            $saveData['invoice_type']   =   $d->invoice->invoice_type;
            $saveData['invoice_month_amount']   =$d->active_amount;
            $saveData['delivery_id']    =   $d->delivey_id;
            $saveData['team_id']        =   $d->team_id;
            $saveData['month']          =   $d->team_month;
            $saveData['month_amount']   =   $d->team_amount;
            $saveData['business_key']   =   $d->business_key;
            $saveData['team']           =   $d->team;
            $saveData['active_amount']  =   $active_amount;
            $saveData['partner_id']     =   $d->partner_id;

            $this->backcashInvoiceRepository->create($saveData);
        }
        //记录日志
        try{
            $this->actionLog->log('backcash.create',
                    Admin::user()->id,
                    [  'id'    =>$backcash_id,
                            'company_id' => $partner_id,
                            'amount'  =>$backData['amount']   ,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }

        DB::commit();
        return $this->ajaxSuccess('录入回款成功！',['url'=>route('admin.backcash.list')]);
    }

    /**
     *更新回款信息  只对填写的回款金额/回款基础信息修改
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updateBackcash($invoiceSaveData,$request,$invoiceData){
        $backcash_id=$request->get('backcash_id');//编辑的时候会有iD传回
        $times=$request->get('times');
        //查询出所有执行月的数据

        $where[]=['backcash_id','=',$backcash_id];
        $backcashInvoiceData = $this->backcashInvoiceRepository->applyWhere($where)->all();

        DB::beginTransaction();//事务开始

        //1.修改回款单主体数据
        $this->backcashModel->whereRaw(' id='.$backcash_id)->update($invoiceSaveData);
        //2.更新已经存在的数据
        $partner_id="";
        foreach($backcashInvoiceData as $d){
            $backcashData['active_amount']  = $request->get($d->id.'_up');
            $this->backcashInvoiceModel->whereRaw(' id='.$d->id)->first()->update($backcashData);
            $partner_id=$d->partner_id;
        }
        //3.检查是否有新的回款金额数据
        foreach($invoiceData as $d){
            $saveData['backcash_id']     =   $backcash_id;

            $saveData['invoice_delivery_id']     =   $d->id;
            $active_amount              =   $request->get($d->id.'_text');
            if($active_amount==""){continue;}
            //冗余数据
            $saveData['invoice_id']    =   $d->invoice_id;
            $saveData['invoice_key']    =   $d->invoice_key;
            $saveData['invoice_amount'] =   $d->invoice->amount;
            $saveData['invoice_type']   =   $d->invoice->invoice_type;
            $saveData['invoice_month_amount']   =$d->active_amount;
            $saveData['delivery_id']    =   $d->delivey_id;
            $saveData['team_id']        =   $d->team_id;
            $saveData['month']          =   $d->team_month;
            $saveData['month_amount']   =   $d->team_amount;
            $saveData['business_key']   =   $d->business_key;
            $saveData['team']           =   $d->team;
            $saveData['active_amount']  =   $active_amount;
            $saveData['partner_id']     =   $d->partner_id;
            $this->backcashInvoiceRepository->create($saveData);
        }
        //记录日志
        try{
            $this->actionLog->log('backcash.edit',
                    Admin::user()->id,
                    [  'id'    =>$backcash_id,
                            'company_id' => $partner_id,
                            'amount'  =>$invoiceSaveData['amount']   ,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return true;
    }

    /**
     * 删除回款的明细
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteBackcashInvoice($id){
        DB::beginTransaction();//事务开始

        $backcashInvoice=$this->backcashInvoiceRepository->find($id);
        //删除明细
        $backcashInvoice->update(['isshow'=>0]);

        DB::commit();
        return '';
    }

    /**
     * 删除回款的明细
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function ajaxdeleteBackcashInvoice(Request $request){
        $id=$request->input('id');
        $returnData=$this->deleteBackcashInvoice($id);
        return $this->ajaxSuccess('删除成功!',$returnData);
    }

    /**
     *删除回款
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteBackcash(Request $request){
        $backcash_id=$request->get('id');

        DB::beginTransaction();//事务开始
        //删除回款主体
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        $this->backcashModel->where('id','=',$backcash_id)->update($arr);

        //删除回款详细关联
        $this->backcashInvoiceModel->where('backcash_id','=',$backcash_id)->get()->map( function ($data){
            $this->deleteBackcashInvoice($data['id']);return true;
        });

        //记录日志
        try{
            $this->actionLog->log('backcash.delete',
                    Admin::user()->id,
                    [  'id'    =>$backcash_id,
                            'company_id' => 0,
                            'amount'  =>0 ,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();

        return $this->ajaxSuccess('删除回款数据成功！',['url'=>route('admin.backcash.list')]);
    }

    /**
     * 录入回款时  查询已录入的发票信息信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function seachInvoice(Request $request) {
        $business_key = trim($request->get('business_key'));//业务编号
        $partner_id = $request->get('partner_id');//合作方id
        $backcash_id=$request->get('id');//回款ID 判断是否是编辑回款金额

        $times=$request->get('times');//判断编辑页面是否重新查询合作方的执行月条件0=否 1=是

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        if(!empty($backcash_id) && empty($times)){
            $returnJsonData=$this->editInvoiceDataApi($request);
            return json_encode($returnJsonData);
        }
        //查询业务表
        $where=[];
        if (!empty($business_key)) {
            $business_key_in=static::serialInStr($business_key);
            $where[]= function ($query) use ($business_key_in) {
                $query->whereRaw(' business_key in (' . $business_key_in . ')');
            };
        }
        if (!empty($partner_id)) {
            $where[]= function ($query) use ($partner_id) {
                $query->whereRaw(' partner_id =' . $partner_id);
            };
        }
		$where[]=function ($query)  {//排除已经被删除的发票明细
			$query->where('isshow','=',1);
		};
        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->whereRaw(session('company_sql'));
            });
        };
        $where[]=function ($query)  {
            $query->whereHas('business_team', function ($subQuery) {
                $subQuery->whereRaw(session('team_sql'));
            });
        };

        $data = $this->invoiceDeliveryRepository->with([
                'backcash_invoices',
                'invoice',
                'business',
                'business.partner',
                'delivery',
                'business_team',
        ])
        ->applyWhere($where)
        ->applyOrder('team_month')
        ->all();

        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($data as $d){
            $business_key=$d->business_key;
            $team=$d->team;
            $month=$d->delivery->month;
            $amount=$d->delivery->amount;
            $invoice_type=$d->invoice->invoice_type;
            $partner  =   $d->business->partner->company_name;
            //计算某个执行月下面的回款总额
            $month_backcash_sum=0.00;
            foreach($d->backcash_invoices as $backcash){
                if($backcash->isshow==0){continue;}
                $month_backcash_sum=$month_backcash_sum+$backcash->active_amount;
            }
            $isloadadd=true;
            foreach($d->backcash_invoices as $backcash){
                if($backcash->isshow==0){continue;}
                $invoice_amount=[];
                if($times=='1' && $backcash_id==$backcash->backcash_id){
                    $invoice_amount=[
                            "key"=>$backcash->id,
                            "value"=>$backcash->active_amount,
                            "name"=>$backcash->id."_up",
                            "type"=>"edit",
                            "max" => round($d->active_amount - $month_backcash_sum+$backcash->active_amount,2),
                            "tips" =>"输入的回款金额必须小于或等于当月剩余发票金额！",
                    ];
                    $isloadadd=false;
                }else{
                    $invoice_amount=[
                            "key"=>$backcash->id,
                            "value"=>$backcash->active_amount,
                            "name"=>"",
                            "type"=>"show",
                    ];
                    $isloadadd=true;
                }
                $table[]=[
                        "business_id"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key,"value"=>$partner],
                        "team"=>["key"=>$business_key.$team,"value"=>$teamDept[$team]],
                        "month"=>["key"=>$d->id,"value"=>$month],
                        "amount"=>["key"=>$d->id,"value"=>$amount],
                        "invoice_amount"=>["key"=>$d->id,"value"=>$d->active_amount],
                        "invoice_key"=>["key"=>$d->id,"value"=>$d->invoice_key],
                        "invoice_type"=>["key"=>$d->id,"value"=>$invoice_type],
                        "backcash_amount_sum"=>["key"=>$d->id,"value"=>static::numFormat($month_backcash_sum)],
                        "backcash_amount"=>$invoice_amount,
                ];

            }
            if($isloadadd) {
                $table[] = [
                        "business_id" => ["key" => $business_key, "value" => $business_key],
                        "partner_id" => ["key" => $business_key, "value" => $partner],
                        "team" => ["key" => $business_key . $team, "value" =>$teamDept[$team]],
                        "month" => ["key" => $d->id, "value" => $month],
                        "amount" => ["key" => $d->id, "value" => $amount],
                        "invoice_amount" => ["key" => $d->id, "value" => $d->active_amount],
                        "invoice_key" => ["key" => $d->id, "value" => $d->invoice_key],
                        "invoice_type" => ["key" => $d->id, "value" => $invoice_type],
                        "backcash_amount_sum" => ["key" => $d->id, "value" => $month_backcash_sum],
                        "backcash_amount" => [
                                "key" => $d->id . '_add',
                                "value" => '',
                                "name" => $d->id . '_text',
                                "type" => "add",
                                "max" => round($d->active_amount - $month_backcash_sum,2),
                                "tips" => "回款金额必须小于或等于剩余的发票金额！",
                        ],
                ];
            }

        }
        sort($table);

        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月份", "key" => "month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "amount", "type" => 1],
                ["name" => "核销月执行额下的发票总金额", "key" => "invoice_amount", "type" => 1],
                ["name" => "发票编号", "key" => "invoice_key", "type" => 1],
                ["name" => "发票类型", "key" => "invoice_type", "type" => 1],
                ["name" => "已回款总额", "key" => "backcash_amount_sum", "type" => 1],
                ["name" => "回款金额（核销发票金额）", "key" => "backcash_amount", "type" => 1],
        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                                                                    'table' =>$table,
                                                                    ]
        ];
        return json_encode($returnJsonData);
    }

    /**
     * 编辑回款时  查询业务或合作方 数据的发票信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function editInvoiceDataApi( $request) {

        $backcash_id=$request->get('id');//发票ID 判断是否是编辑回款金额

        if(empty($backcash_id)){
            return $this->ajaxError('未找到数据!');
        }

        $data = $this->backcashInvoiceRepository
                ->with(['backcash','business.partner','delivery'])
                ->applyWhere([
                              ['backcash_id', '=', $backcash_id],
                              ['isshow', '=', 1],
                            ])
                ->all();
        //计算核销月发票下的回款总额
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');
        $month_backcash_sum_arr=$this->sumBackcashMonthAmount($partner_id,$business_key);

        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($data as $d){
            $key=$d->business_key.$d->partner_id.$d->invoice_delivery_id;
            $business_key=$d->business_key;
            $team=$d->team;
            $month=$d->delivery->month;
            $amount=$d->delivery->amount;
            $invoice_type=$d->invoice_type;
            $partner  =   $d->business->partner->company_name;
            $table[]=[
                    "business_id"=>["key"=>$business_key,"value"=>$business_key],
                    "partner_id" =>["key"=>$business_key,"value"=>$partner],
                    "team"=>["key"=>$team,"value"=>$teamDept[$team]],
                    "month"=>["key"=>$team.$month,"value"=>$month],
                    "amount"=>["key"=>$team.$month.$amount,"value"=>$amount],

                    "invoice_key"=>["key"=>$team.$month,"value"=>$d->invoice_key],
                    "invoice_type"=>["key"=>$team.$month,"value"=>$invoice_type],
                    "invoice_amount"=>["key"=>$team.$month,"value"=>$d->invoice_amount],
                    "invoice_team_amount"=>["key"=>$team.$month,"value"=>$d->invoice_month_amount],
                    "backcash_amount_sum"=>["key"=>$team.$month.$month,"value"=>static::numFormat($month_backcash_sum_arr[$key])],
                    "backcash_amount"=>[
                            "key"=>$d->id,
                            "value"=>$d->active_amount,
                            "name"=>$d->id . '_up',
                            "type"=>"edit",
                            "max"   =>round($d->invoice_month_amount-$month_backcash_sum_arr[$key]+$d->active_amount,2),
                            "tips"   =>"输入的发票金额必须小于或等于当月剩余执行金额！",
                    ],
            ];

        }

        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月份", "key" => "month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "amount", "type" => 1],

                ["name" => "发票编号", "key" => "invoice_key", "type" => 1],
                ["name" => "发票类型", "key" => "invoice_type", "type" => 1],
                ["name" => "发票总额", "key" => "invoice_amount", "type" => 1],
                ["name" => "小组发票核销", "key" => "invoice_team_amount", "type" => 1],
                ["name" => "已回款总额", "key" => "backcash_amount_sum", "type" => 1],
                ["name" => "回款金额（核销发票金额）", "key" => "backcash_amount", "type" => 1],
        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$table,
        ]
        ];
        return $returnJsonData;
    }

    /**
     *  获得列表数据 含搜索条件
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $request
     * @param $type
     * @return \Illuminate\Database\Eloquent\Collection|mixed|string
     */
    public function getListRes($request , $type){

        //页面查询-------------------------------------------------------------
        $btime              =$request->get('btime');//回款时间段
        $etime              =$request->get('etime');
        $bmonth             =$request->get('bmonth');//执行月份时间段
        $emonth             =$request->get('emonth');
        $partner_id         =$request->get('partner_id');
        $business_key       =$request->get('business_key');

        $where=[];
        //录入回款时间
        if(!empty($btime) && !empty($etime)){
            $where[] =function ($query) use ($btime,$etime) {
                $query->whereHas('backcash',
                        function ($query) use ($btime,$etime) {
                            $query->whereBetween('backtime', [$btime, $etime]);
                        });
            };
        }

        //执行月份
        if(!empty($bmonth) && !empty($emonth)){
            $where[] = function ($query) use ($bmonth,$emonth) {
                $query->whereBetween('month', [$bmonth, $emonth]);
            };
        }
        //合作方
        if(!empty($partner_id) ){
            if(!is_array($partner_id)){
                $partner_id=[$partner_id];
            }
            $where[] = function ($query) use ($partner_id) {
                $query->whereIn('partner_id', $partner_id);
            };
        }
        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->whereRaw(session('company_sql'));
            });
        };
        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->whereRaw(session('team_sql'));
            });
        };

        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }

        if($type==1){
            return $this->backcashInvoiceRepository
                    ->with(['backcash','partner','delivery','business','business_team'])
                    ->applyWhere($where)
                    ->applyOrder('updated_at','desc')
                    ->paginate(20)
                    ->toarray();
        }else if($type==2){
            return $this->backcashInvoiceRepository
                    ->with(['backcash','partner','delivery','business','business_team'])
                    ->applyWhere($where)
                    ->applyOrder('updated_at','desc')
                    ->all();
        }
        return '';
    }

    /**
     *发票列表页面 获取生成表格的方法
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function listDataApi(Request $request){

        $listdata=$this->getListRes($request,1);

        $amount_array               =[];
        $count_amount               =0.00;
        $count_active_amount        =0.00;
        $count_invoice_month_amount =0.00;
        $count_month_amount         =0.00;

        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($listdata['data'] as $k=>$v){
            //回款基本信息
            $backcash       =   $v['backcash']['backcash_key'];
            $backtype   =   $v['backcash']['backtype'];
            $backtime         =   $v['backcash']['backtime'];
            $bank        =   $v['backcash']['bank'];
            $amount        =   $v['backcash']['amount'];
            $remark         =   $v['backcash']['remark'];
            $opt=[];
            if($v['backcash']['isshow']==0){
                $opt=[
                        ["type"=>"","title"=>'<font color="#CCCCCC">已删除'.$v['backcash']['deleted_at'].'</font>'],
                ];
            }else{
                $opt=[
                        ["type"=>"edit","title"=>'<i hidid="" class="icon i-edit" title="编辑"></i>',
                                "url"=>route('admin.backcash.edit',$v['backcash']['id'])],
                        ["type"=>"delete","title"=>'<i hidid="" class="icon i-del" title="删除"></i>',
                                "url"=>"","id"=>$v['backcash_id']],
                ];
                //被删除的数据不列入合计
                $amount_array[$backcash]=$amount;//发票的金额需要去重
                $count_active_amount=$count_active_amount+$v['active_amount'];
                $count_invoice_month_amount=$count_invoice_month_amount+$v['invoice_month_amount'];
                $count_month_amount=$count_month_amount+$v['delivery']['amount'];
            }
            $table[]=[
                    "backcash_key"=>["key"=>$backcash,"value"=>$backcash],
                    "partner_id" =>["key"=>$backcash,"value"=>$v['partner']['company_name']],
                    "backtime"=>["key"=>$backcash,"value"=>$backtime],
                    "bank"=>["key"=>$backcash,"value"=>$bank],
                    "backtype"=>["key"=>$backcash,"value"=>$backtype],
                    "amount"=>["key"=>$backcash,"value"=>$amount],
                    "active_amount"=>["key"=>$v['id'],"value"=>$v['active_amount']],
                    "invoice_key"=>["key"=>$v['id'],"value"=>$v['invoice_key']],
                   // "invoice_amount"=>["key"=>$v['id'],"value"=>$v['invoice_amount']],
                    "invoice_type"=>["key"=>$v['id'],"value"=>$v['invoice_type']],
                    "invoice_month_amount"=>["key"=>$v['id'],"value"=>$v['invoice_month_amount']],
                    "month"=>["key"=>$v['id'],"value"=>$v['delivery']['month']],
                    "month_amount"=>["key"=>$v['id'],"value"=>$v['delivery']['amount']],
                    "business_key"=>["key"=>$v['id'],"value"=>$v['business_key']],
                    "team"=>["key"=>$v['id'],"value"=>$teamDept[$v['team']]],
                    "remark"=>["key"=>$backcash,"value"=>$remark],
                    "operate"=>["key"=>$backcash,"value"=>$opt],
            ];
        }
        if(!empty($amount_array)){
            $count_amount=array_sum($amount_array);
        }
        $table[]=[
                "backcash_key"=>["key"=>'',"value"=>'本页合计:'],
                "partner_id" =>["key"=>'',"value"=>''],
                "backtime"=>["key"=>'',"value"=>''],
                "bank"=>["key"=>'',"value"=>''],
                "backtype"=>["key"=>'',"value"=>''],
                "amount"=>["key"=>'',"value"=>static::numFormat($count_amount,2)],
                "active_amount"=>["key"=>'',"value"=>static::numFormat($count_active_amount,2)],
                "invoice_key"=>["key"=>'',"value"=>''],
                "invoice_type"=>["key"=>'',"value"=>''],
                "invoice_month_amount"=>["key"=>'',"value"=>static::numFormat($count_invoice_month_amount,2)],
                "month"=>["key"=>'',"value"=>''],
                "month_amount"=>["key"=>'',"value"=>static::numFormat($count_month_amount,2)],
                "business_key"=>["key"=>'',"value"=>''],
                "team"=>["key"=>'',"value"=>''],
                "remark"=>["key"=>'',"value"=>''],
                "operate"=>["key"=>'',"value"=>''],
        ];
        //表格 标题
        $title = [
                ["name" => "回款单号", "key" => "backcash_key", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "回款时间", "key" => "backtime", "type" => 1],
                ["name" => "回款银行", "key" => "bank", "type" => 1],
                ["name" => "回款方式", "key" => "backtype", "type" => 1],
                ["name" => "回款总金额", "key" => "amount", "type" => 1],
                ["name" => "核销发票下的回款金额", "key" => "active_amount", "type" => 0],
                ["name" => "发票编号", "key" => "invoice_key", "type" => 0],
                //["name" => "发票金额", "key" => "invoice_amount", "type" => 0],
                ["name" => "发票类型", "key" => "invoice_type", "type" => 0],
                ["name" => "核销月执行额下的发票总金额", "key" => "invoice_month_amount", "type" => 0],
                ["name" => "执行月份", "key" => "month", "type" => 0],
                ["name" => "月执行金额", "key" => "month_amount", "type" => 0],
                ["name" => "业务编号", "key" => "business_key", "type" => 0],
                ["name" => "执行小组", "key" => "team", "type" => 0],
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

        $res=$this->getListRes($request,2);

        //表头
        $headers = [
                "回款单号",
                "合作方",
                "回款时间",
                "回款银行",
                "回款方式",
                "回款总金额",
                "核销发票下的回款金额",
                "发票编号",
                "发票类型",
                "核销月执行额下的发票总金额",
                "执行月份",
                "月执行金额",
                "业务编号",
                "执行小组",
                "备注",
                "状态",
        ];
        //格式化数据
        $rows=[];
        $res->map(function ($data) use (&$rows,$teamDept){
            $status='正常';
            if($data->backcash->isshow==0){
                $status='已删除';
            }
            $rows[]=[
                    $data->backcash->backcash_key,
                    $data->partner->company_name,
                    $data->backcash->backtime,
                    $data->backcash->bank,
                    $data->backcash->backtype,
                    $data->backcash->amount,
                    $data->active_amount,
                    $data->invoice_key,
                    $data->invoice_type,
                    $data->invoice_month_amount,
                    $data->delivery->month,
                    $data->delivery->amount,
                    $data->business_key,
                    $teamDept[$data->team],
                    $data->backcash->remark,
                    $status,
            ];
        });
        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }

    /**
     *验证 一张发票只能对应到一个合作方
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $invoice_key
     * @param $partner_id
     * @return bool
     */
    public function checkInvoicePartner($invoice_key,$partner_id){
        $where=' invoice_key="'.$invoice_key.'" and partner_id ='.$partner_id;
        $count=$this->invoiceDeliveyModel->whereRaw($where)->count();
        if($count>0){
            return true;
        }
        return false;
    }

    /**
     *根据合作方和业务key获得下面所所属执行月的已录入发票金额
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $partner_id
     * @param $business_key
     * @return array
     */
    public function sumBackcashMonthAmount($partner_id,$business_key=''){
        $where[]=['isshow','=',1];
        //合作方
        if(!empty($partner_id) ){
            if(!is_array($partner_id)){
                $partner_id=[$partner_id];
            }
            $where[] = function ($query) use ($partner_id) {
                $query->whereIn('partner_id', $partner_id);
            };
        }
        $where[]=function ($query)  {
            $query->whereHas('business', function ($subQuery) {
                $subQuery->whereRaw(session('company_sql'));
            });
        };
        $where[]=function ($query)  {
            $query->whereHas('business_team', function ($subQuery) {
                $subQuery->whereRaw(session('team_sql'));
            });
        };

        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }
        $data = $this->backcashInvoiceRepository
                ->with(['business','business_team'])
                ->applyWhere($where)
                ->all();
        //计算小组执行月的发票总额
        $month_backcash_sum_arr=[];
        foreach($data as $d){
            $key=$d->business_key.$d->partner_id.$d->invoice_delivery_id;
            if(!isset($month_backcash_sum_arr[$key])){
                $month_backcash_sum_arr[$key]=$d->active_amount;
            }else{
                $month_backcash_sum_arr[$key]=$month_backcash_sum_arr[$key]+$d->active_amount;
            }
        }
        return $month_backcash_sum_arr;
    }

}
