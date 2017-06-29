<?php
namespace App\Http\Controllers\Admin;

use App\Repositories\BackcashInvoiceRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use File;
use Validator;
use DB;
use App\Repositories\InvoiceDeliveryRepository;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;

use App\Repositories\BusinessRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\DictionaryRepository;
use App\Models\InvoiceModel;
use App\Models\InvoiceDeliveyModel;
use App\Http\Requests\Admin\FormRequest;
use App\Services\Admin\ActionLog;
use App\Services\ExcelMaker;
use Admin;

/**
 * 发票管理
 *
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class InvoiceController extends Controller {
    use CommonResponse;

    protected $loginUser;

    protected $invoiceDeliveryRepository;

    protected $businessRepository;

    protected $deliveryRepository;

    protected $partnersRepository;

    protected $dictionaryRepository;

    protected $invoiceModel;

    protected $invoiceDeliveyModel;

    private   $backcashInvoiceRepository;

    private    $actionLog;
    /**
     *
     */
    public function __construct(AdminGuard $loginUser,
                                InvoiceDeliveryRepository $invoiceDeliveryRepository,
                                BusinessRepository $businessRepository,
                                DeliveryRepository $deliveryRepository,
                                PartnersRepository $partnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                InvoiceModel $invoiceModel,
                                InvoiceDeliveyModel $invoiceDeliveyModel,
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                ActionLog $actionLog) {
        $this->loginUser = $loginUser->user();
        $this->invoiceDeliveryRepository = $invoiceDeliveryRepository;
        $this->businessRepository = $businessRepository;
        $this->deliveryRepository = $deliveryRepository;
        $this->partnersRepository   =$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->invoiceModel         =$invoiceModel;
        $this->invoiceDeliveyModel  =$invoiceDeliveyModel;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
        $this->actionLog            =$actionLog;
    }

    /**
     * 发票查询列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request) {
        //加载合作方数据
        $viewData['partnerData']=$this->partnersRepository->getPartnersList();
        return view('admin.invoice.list', $viewData);
    }


    /**
     *录入发票信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function createInvoice($partner_id="",$remark="",$business_key="") {
        $partner_id =trim($partner_id);
        $remark     =base64_decode(trim($remark));
        $viewData['partner_id']=$partner_id;
        $viewData['remark']=$remark;
        $viewData['invoice_id']=0;
        $viewData['business_key']=$business_key;

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
        return view('admin.invoice.edit', $viewData);
    }


    /**
     *  编辑录入发票
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function editInvoice($invoice_id=0){
        $viewData['invoice_id']=$invoice_id;
        $validator = Validator::make(
                [
                        'invoice_id' => $invoice_id,
                ],
                [
                        'invoice_id' => 'integer',
                ]
        );
        if ($validator->fails())
        {
            echo '<script>alert("参数不符合规则!");history.back(-1);</script>';
        }
        //编辑的情况下 获得这个发票所属的合作方
        $invoiceDelivery=$this->invoiceDeliveyModel->where('invoice_id','=',$invoice_id)->first();
        $viewData['partner_id'] =$invoiceDelivery->partner_id;

        //编辑的情况下 加载发票信息
        $viewData['invoiceData']=$this->invoiceModel->where('id','=',$invoice_id)->first();


        //加载合作方数据
        $viewData['partnerData']=$this->partnersRepository->getPartnersList();

        return view('admin.invoice.edit', $viewData);
    }

    /**
     * 保存录入发票信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function storeInvoice(FormRequest $request){
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');


        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        $where=[];
        if(!empty($partner_id)){
            $where[]=function ($query) use ($partner_id) {
                $query->whereHas('business', function ($subQuery) use ($partner_id)
                {
                    $subQuery->where('partner_id','=',$partner_id);
                });
            };
        }else{
            $partner_id="0";
        }

        if(!empty($business_key)){
            $where[]=function ($query) use ($business_key) {
                $query->whereHas('business', function ($subQuery) use ($business_key)
                {
                    $subQuery->where('business_key','=',$business_key);
                });
            };
        }

        $where[]=function ($query) {
            $query->whereHas('business', function ($subQuery)
            {
                $subQuery->whereRaw(session('company_sql'));
            });
        };
        $where[]=function ($query) {
            $query->whereHas('businessTeam', function ($subQuery)
            {
                $subQuery->whereRaw(session('team_sql'));
            });
        };

        $rebackPrm="/".$partner_id.'/i/'.$business_key;


        $invoice_key    =trim($request->get('invoice_key'));
        $invoice_type   =$request->get('invoice_type');
        $invoice_time   =$request->get('invoice_time');
        $amount         =$request->get('amount');
        $remark         =$request->get('remark');
        $invoiceData['invoice_key']=$invoice_key;
        $invoiceData['invoice_type']=$invoice_type;
        $invoiceData['invoice_time']=$invoice_time;
        $invoiceData['amount']      =$amount;
        $invoiceData['remark']      =$remark;

        //查询出所有执行月的数据
        $deliveryData=$this->deliveryRepository->with(['business','businessTeam'])
                ->applyWhere($where)->all();

        $invoice_id=$request->get('invoice_id');//编辑的时候会有iD传回
        if(!empty($invoice_id)){
            if($this->updateInvoice($invoiceData,$request,$deliveryData)){
                return $this->ajaxSuccess('修改发票成功！',['url'=>route('admin.invoice.list')]);
                exit;
            }
        }

        DB::beginTransaction();//事务开始

        //保存发票主体数据
        $invoice=$this->invoiceModel->create($invoiceData);
        $invoice_id=$invoice->id;

        //保存发票金额拆分数据
        $saveData['invoice_id']     =   $invoice_id;
        foreach($deliveryData as $d){
            $saveData['delivey_id']     =   $d->id;
            $saveData['team_id']     =   $d->team_id;
            $active_amount              =   $request->get($d->id.'_text');
            if($active_amount==""){continue;}
            $saveData['active_amount']           =   $active_amount;
            //冗余数据
            $saveData['invoice_key']    =   $invoice_key;
            $saveData['partner_id']     =   $d->business->partner_id;
            $saveData['team']           =   $d->team;
            $saveData['team_month']     =   $d->month;
            $saveData['team_amount']    =   $d->amount;
            $saveData['business_key']   =   $d->business->business_key;

            $this->invoiceDeliveryRepository->create($saveData);
        }
        //记录日志
        $this->actionLog->log('invoice.archive',
                Admin::user()->id,
                [  'id'    =>$invoice_id,
                   'company_id' => $partner_id,
                   'amount'  =>$amount   ,
                ]
        );
        DB::commit();
        return $this->ajaxSuccess('录入发票成功！',['url'=>route('admin.invoice.add').$rebackPrm]);
    }

    /**
     *更新发票信息  只对填写的发票金额/发票基础信息修改
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updateInvoice($invoiceSaveData,$request,$deliveryData){
        $invoice_id=$request->get('invoice_id');//编辑的时候会有iD传回
        //$partner_id=$request->get('partner_id');
        $times=$request->get('times');
        //查询编辑前的发票数据
        //$old_invoice=$this->invoiceModel->where('id','=',$invoice_id)->first();

        DB::beginTransaction();//事务开始

        $partner_id='';
        //1.更新发票主体数据
        $this->invoiceModel->whereRaw(' id='.$invoice_id)->update($invoiceSaveData);

        //2.检查更新已经存在的发票金额
        $invoiceDeliveryData =$this->invoiceDeliveryRepository
                ->applyWhere([
                        ['invoice_id', '=', $invoice_id]
                ])->all();
        foreach($invoiceDeliveryData as $d){
            $partner_id=$d->partner_id;
            $saveInvoiceDelivery['active_amount']=$request->get($d->id.'_up');
            $this->invoiceDeliveyModel->whereRaw(' id='.$d->id )->first()->update($saveInvoiceDelivery);
        }

        if(!empty($times)) {
            //3.检查是否有新增发票拆分金额数据
            $saveData['invoice_id'] = $invoice_id;
            foreach ($deliveryData as $d) {
                $saveData['delivey_id'] = $d->id;
                $saveData['team_id'] = $d->team_id;
                $active_amount = $request->get($d->id . '_text');

                if ($active_amount == "") {
                    continue;
                }
                $saveData['active_amount'] = $active_amount;
                //冗余数据
                $saveData['invoice_key'] = $invoiceSaveData['invoice_key'];
                $saveData['partner_id'] = $d->business->partner_id;
                $saveData['team'] = $d->team;
                $saveData['team_month'] = $d->month;
                $saveData['team_amount'] = $d->amount;
                $saveData['business_key'] = $d->business->business_key;
                $this->invoiceDeliveryRepository->create($saveData);
            }
        }

        //记录日志
        $this->actionLog->log('invoice.edit',
                Admin::user()->id,
                [  'id'    =>$invoice_id,
                        'company_id' => $partner_id,
                        'amount'  =>$invoiceSaveData['amount']    ,
                ]
        );
        DB::commit();
        return true;
    }

    /**
     *删除发票明细
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteInvoiceDelivey($id ){
        //删除前检查 下属有没有关联的回款单
        if($this->backcashInvoiceRepository->findByInvoicedetailId($id)>0){
            DB::rollBack();
            throw new BusinessException('检测到该发票明细单有未删除的回款单据，请先删除回款单据！');
        }
        $returnData=[];
        DB::beginTransaction();//事务开始

        $invoiceDelivery=$this->invoiceDeliveryRepository->find($id);

        //删除明细
        $arr['isshow']=0;
        $invoiceDelivery->update($arr);

        DB::commit();
        return $returnData;
    }

    public function ajaxdeleteInvoiceDelivey(Request $request){
        $id=$request->input('id');
        $returnData=$this->deleteInvoiceDelivey($id);
        return $this->ajaxSuccess('删除成功!',$returnData);
    }

    /**
     *删除发票
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteInvoice(Request $request ){
        $invoice_id=$request->get('id');

        DB::beginTransaction();//事务开始
        //删除发票主体
        $arr['isshow']=0;
        $arr['deleted_at']=date('Y-m-d H:i:s',time());
        $this->invoiceModel->where('id','=',$invoice_id)->update($arr);


        //删除前检查 下属有没有关联的回款单
        if($this->backcashInvoiceRepository->findBackcashByInvoiceId($invoice_id)>0){
            return $this->ajaxError('检测到该发票单有未删除的回款单据，请先删除回款单据！');
        }

        //删除发票详细关联
        $this->invoiceDeliveyModel->where('invoice_id','=',$invoice_id)->get()->map( function ($data){
            $this->deleteInvoiceDelivey($data['id']);return true;
        });

        //记录日志
        $this->actionLog->log('invoice.delete',
                Admin::user()->id,
                [  'id'    =>$invoice_id,
                        'company_id' => 0,
                        'amount'  =>0   ,
                ]
        );
        DB::commit();
        return $this->ajaxSuccess('删除发票成功！',['url'=>route('admin.invoice.list')]);
    }

    /**
     * 添加发票时  查询业务或合作方 数据的发票信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function seachInvoice(Request $request) {
        $business_key = trim($request->get('business_key'));//业务编号
        $partner_id = $request->get('partner_id');//合作方id
        $invoice_id=$request->get('id');//发票ID 判断是否是编辑发票金额

        $times=$request->get('times');//判断编辑页面是否重新查询合作方的执行月条件0=否 1=是

        if(empty($business_key) && empty($partner_id)){
            return $this->ajaxError('未找到数据!');
        }

        if(!empty($invoice_id) && empty($times)){
            $returnJsonData=$this->editInvoiceDataApi($request);
            return json_encode($returnJsonData);
        }
        //查询业务表
        $where=' 1=1 ';
        if (!empty($business_key)) {
            $business_key_in=static::serialInStr($business_key);
            $where .= ' and business_key in (' . $business_key_in . ')';
        }
        if (!empty($partner_id)) {
            $where .= ' and partner_id=' . $partner_id ;
        }else{
            $where .= ' and  '.session('company_sql');
        }


        $data = $this->deliveryRepository->with([
                'business',
                'businessTeam',
                'invoice_deliveys',
                'business.partner',
        ])
        ->applyWhere([
            function ($query) use ($where) {
                $query->whereHas('business', function ($subQuery) use ($where) {
                    $subQuery->whereRaw($where);
                });
            },
            function ($query)  {
                $query->whereHas('businessTeam', function ($subQuery) {
                        $subQuery->whereRaw(session('team_sql'));
                });
            },

            ['isshow', '=', 1]
        ])->all();

        $mont_invoice_arr=$this->sumInvoiceMonthAmount($partner_id,$business_key);
        $table=[];
        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($data as $d){
            $business_key=$d->business->business_key;
            $team=$d->team;
            $month=$d->month;
            $amount=$d->amount;
            $partner=$d->business->partner->company_name;
            $key=$d->business_key.$d->id;
            if(!isset($mont_invoice_arr[$key])){
                $mont_invoice_arr[$key]=0;
            }

            //加载已经和业务月执行额关联的数据  用于显示
            $isloadadd=true;
            foreach($d->invoice_deliveys as $invoice){
                if($invoice->isshow==0){continue;}
                $invoice_amount=[];
                if($times=='1' && $invoice_id==$invoice->invoice_id){
                    $invoice_amount=[
                            "key"=>$invoice->id,
                            "value"=>$invoice->active_amount,
                            "name"=>$invoice->id."_up",
                            "type"=>"edit",
                            "max" => floatval($amount - $mont_invoice_arr[$key]+$invoice->active_amount),
                            "tips" => "输入的发票金额必须小于或等于当月剩余执行金额！",
                    ];
                    $isloadadd=false;
                }else{
                    $invoice_amount=[
                            "key"=>$invoice->id,
                            "value"=>$invoice->active_amount,
                            "name"=>"",
                            "type"=>"show",
                    ];
                    $isloadadd=true;
                }
                $table[]=[
                        "business_id"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key,"value"=>$partner],
                        "team"=>["key"=>$business_key.$team,"value"=>$teamDept[$team]],
                        "month"=>["key"=>$business_key.$team.$month,"value"=>$month],
                        "amount"=>["key"=>$business_key.$team.$month.$month,"value"=>$amount],
                        "invoice_sum"=>["key"=>$d->id,"value"=>$mont_invoice_arr[$key]],
                        "invoice_amount"=>$invoice_amount,
                ];

            }
            //加载创建
            if($isloadadd) {
                $table[] = [
                        "business_id" => ["key" => $business_key, "value" => $business_key],
                        "partner_id" => ["key" => $business_key, "value" => $partner],
                        "team" => ["key" => $business_key.$team, "value" => $teamDept[$team]],
                        "month" => ["key" => $business_key.$team.$month, "value" => $month],
                        "amount" => ["key" => $business_key.$team.$month.$month, "value" => $amount],
                        "invoice_sum" => ["key" => $d->id, "value" => $mont_invoice_arr[$key]],
                        "invoice_amount" => [
                                "key" => "new_" . $d->id,
                                "value" => '',
                                "name" =>$d->id."_text",
                                "type" => "add",
                                "max" => ($amount - $mont_invoice_arr[$key])*100/100,
                                "tips" => "输入的发票金额必须小于或等于当月剩余执行金额！",
                        ],
                ];
            }
        }


        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_id", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月份", "key" => "month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "amount", "type" => 1],
                ["name" => "已开发票总额", "key" => "invoice_sum", "type" => 1],
                ["name" => "发票金额（核销月执行额）", "key" => "invoice_amount", "type" => 1],

        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                                                                    'table' =>$table,
                                                                    ]
        ];
        return json_encode($returnJsonData);
    }

    /**
     * 添加发票时  查询业务或合作方 数据的发票信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function editInvoiceDataApi( $request) {
        $invoice_id=$request->get('id');//发票ID 判断是否是编辑发票金额
        if(empty($invoice_id)){
            return $this->ajaxError('未找到数据!');
        }
        $data =$this->invoiceDeliveryRepository->with(['invoice','business.partner','delivery'])
                                                ->applyWhere([
                                                        ['isshow', '=', 1],
                                                        ['invoice_id', '=', $invoice_id]
                                                ])->all();

        //计算小组执行月的发票总额
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');
        $mont_invoice_arr=$this->sumInvoiceMonthAmount($partner_id,$business_key);

        //查询小组所属的部门
        $table=[];
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        foreach($data as $d){
            $key=$d->business_key.$d->delivey_id;
            $business_key=$d->business_key;
            $f_partner_id=$d->partner_id;
            $team=$teamDept[$d->team];
            $month=$d->delivery->month;
            $amount=$d->delivery->amount;
            $partner=$d->business->partner->company_name;
            $table[]=[
                        "business_id"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key.$f_partner_id,"value"=>$partner],
                        "team"=>["key"=>$business_key.$team,"value"=>$team],
                        "month"=>["key"=>$business_key.$team.$month,"value"=>$month],
                        "amount"=>["key"=>$business_key.$team.$month.$amount,"value"=>$amount],
                        "invoice_sum"=>["key"=>$business_key.$team.$month.$month,"value"=>$mont_invoice_arr[$key]],
                        "invoice_amount"=>[
                                "key"=>$d->id,
                                "value"=>$d->active_amount,
                                "name"=>$d->id.'_up',
                                "type"=>'edit',
                                "max"   =>$amount-$mont_invoice_arr[$key]+$d->active_amount,
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
                ["name" => "已开发票总额", "key" => "invoice_sum", "type" => 1],
                ["name" => "发票金额（核销月执行额）", "key" => "invoice_amount", "type" => 1],

        ];

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$table,
        ]
        ];
        return $returnJsonData;
    }

    /**
     * 返回 列表页数据 （带搜索条件）
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $request
     */
    public function getListRes($request,$type){
        $btime              =$request->get('btime');//开票时间段
        $etime              =$request->get('etime');
        $bmonth             =$request->get('bmonth');//执行月份时间段
        $emonth             =$request->get('emonth');
        $partner_id         =$request->get('partner_id');
        $invoice_type       =$request->get('invoice_type');//发票类型
        $business_key       =$request->get('business_key');

        //页面查询-------------------------------------------------------------
        $where=[];
        //录入发票时间
        if(!empty($btime) && !empty($etime)){
            $where[] =function ($query) use ($btime,$etime) {
                $query->whereHas('invoice',
                        function ($subquery) use ($btime,$etime) {
                            $subquery->whereBetween('invoice_time', [$btime, $etime]);
                        });
            };
        }
        //发票类型
        if(!empty($invoice_type)){
            $where[] =function ($query) use ($invoice_type) {
                $query->whereHas('invoice',
                        function ($subquery) use ($invoice_type) {
                            $subquery->where('invoice_type', 'like' ,'%'.$invoice_type.'%');
                        });
            };
        }
        //执行月份
        if(!empty($bmonth) && !empty($emonth)){
            $where[] = function ($query) use ($bmonth,$emonth) {
                $query->whereBetween('team_month', [$bmonth, $emonth]);
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
        }else{
            $where[] = function ($query) {
                $query->whereHas('business',function ($subquery)  {
                    $subquery->whereRaw(session('company_sql'));
                });
            };
        }
        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }

        //小组范围权限
        $where[] = function ($query) {
            $query->whereHas('business_team',function ($subquery)  {
                $subquery->whereRaw(session('team_sql'));
            });
        };

        if($type==1){
            return $this->invoiceDeliveryRepository->with([
                    'invoice',
                    'business',
                    'business.partner',
                    'delivery',
                    'business_team'
                    ])
                    ->applyWhere($where)
                    ->applyOrder('updated_at','desc')
                    ->paginate(20)
                    ->toArray();
        }else if($type==2){
            return $this->invoiceDeliveryRepository->with([
                    'invoice',
                    'business',
                    'business.partner',
                    'delivery',
                    'business_team'
                    ])
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


        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        //-------------------------------------------------------------------------------------

        $listdata=$this->getListRes($request,1);

        $amount_array               =[];
        $count_amount               =0.00;
        $count_active_amount        =0.00;
        $count_team_amount          =0.00;

        $table=[];
        foreach($listdata['data'] as $k=>$v){
            //发票基本信息
            $invoice_id     =   $v['invoice']['id'];
            $invoice_key    =   $invoice_id.$v['invoice']['invoice_key'];
            $invoice_type   =   $v['invoice']['invoice_type'];
            $invoice_time   =   $v['invoice']['invoice_time'];
            $amount         =   $v['invoice']['amount'];
            $remark         =   $v['invoice']['remark'];
            $partner        =   $v['business']['partner']['company_name'];
            $opt=[];
            if($v['invoice']['isshow']==0){
                $opt=[
                        ["type"=>"","title"=>'<font color="#CCCCCC">已删除'.$v['invoice']['deleted_at'].'</font>'],
                ];
            }else{
                $opt=[
                        ["type"=>"","title"=>'<i hidid="" class="icon i-edit" title="编辑"></i>',
                                "url"=>route('admin.invoice.edit',$v['invoice_id'])],
                        ["type"=>"delete","title"=>'<i hidid="" class="icon i-del" title="删除"></i>',"url"=>"","id"=>$v['invoice_id']],
                ];
                //被删除的数据不列入合计
                $amount_array[$invoice_key]=$amount;//发票的金额需要去重
                $count_active_amount=$count_active_amount+$v['active_amount'];
                $count_team_amount=$count_team_amount+$v['delivery']['amount'];
            }
            $table[]=[
                    "invoice_id"=>["key"=>$invoice_id,"value"=>$invoice_id],
                    "invoice_key"=>["key"=>$invoice_key,"value"=>$v['invoice']['invoice_key']],
                    "partner_id" =>["key"=>$invoice_key,"value"=>$partner],
                    "invoice_type"=>["key"=>$invoice_key,"value"=>$invoice_type],
                    "invoice_time"=>["key"=>$invoice_key,"value"=>$invoice_time],
                    "amount"=>["key"=>$invoice_key,"value"=>$amount],
                    "active_amount"=>["key"=>$v['id'],"value"=>$v['active_amount']],
                    "team_month"=>["key"=>$v['id'],"value"=>$v['delivery']['month']],
                    "team_amount"=>["key"=>$v['id'],"value"=>$v['delivery']['amount']],
                    "business_key"=>["key"=>$v['id'],"value"=>$v['business_key']],
                    "team"=>["key"=>$v['id'],"value"=>$teamDept[$v['team']]],
                    "remark"=>["key"=>$invoice_key,"value"=>$remark],
                    "operate"=>["key"=>$invoice_key,"value"=>$opt   ],
            ];

        }
        if(!empty($amount_array)){
            $count_amount=array_sum($amount_array);
        }
        $table[]=[
                "invoice_id"=>["key"=>'',"value"=>'当前页合计：'],
                "invoice_key"=>["key"=>'',"value"=>''],
                "partner_id" =>["key"=>'',"value"=>''],
                "invoice_type"=>["key"=>'',"value"=>''],
                "invoice_time"=>["key"=>'',"value"=>''],
                "amount"=>["key"=>'',"value"=>static::numFormat($count_amount,2)],
                "active_amount"=>["key"=>'',"value"=>static::numFormat($count_active_amount,2)],
                "team_month"=>["key"=>'',"value"=>''],
                "team_amount"=>["key"=>'',"value"=>static::numFormat($count_team_amount,2)],
                "business_key"=>["key"=>'',"value"=>''],
                "team"=>["key"=>'',"value"=>''],
                "remark"=>["key"=>'',"value"=>''],
                "operate"=>["key"=>'',"value"=>''   ],
        ];
        //表格 标题
        $title = [
                ["name" => "编号", "key" => "invoice_id", "type" => 1],
                ["name" => "发票编号", "key" => "invoice_key", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "发票类型", "key" => "invoice_type", "type" => 1],
                ["name" => "开票时间", "key" => "invoice_time", "type" => 1],
                ["name" => "发票金额", "key" => "amount", "type" => 1],
                ["name" => "核销月执行额下的发票金额", "key" => "active_amount", "type" => 1],
                ["name" => "执行月份", "key" => "team_month", "type" => 1],
                ["name" => "月执行金额", "key" => "team_amount", "type" => 1],
                ["name" => "业务编号", "key" => "business_key", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
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
                '编号',
                '发票编号',
                '合作方',
                '发票类型',
                '开票时间',
                '发票金额',
                '核销月执行额下的发票金额',
                '执行月份',
                '月执行金额',
                '业务编号',
                '执行小组',
                '备注',
                '状态',
        ];
        //格式化数据
        $rows=[];
        $res->map(function ($data) use (&$rows,$teamDept){
            $status='正常';
            if($data->invoice->isshow==0){
                $status='已删除';
            }
            $rows[]=[
                    $data->invoice->id,
                    $data->invoice->invoice_key,
                    $data->business->partner->company_name,
                    $data->invoice->invoice_type,
                    $data->invoice->invoice_time,
                    $data->invoice->amount,
                    $data->active_amount,
                    $data->delivery->month,
                    $data->delivery->amount,
                    $data->business_key,
                    $teamDept[$data->team],
                    $data->invoice->remark,
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
    public function sumInvoiceMonthAmount($partner_id,$business_key=''){
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
        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
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

        $data = $this->invoiceDeliveryRepository->with(['business','business_team'])
                ->applyWhere($where)
                ->all();
        //计算小组执行月的发票总额
        $mont_invoice_arr=[];
        foreach($data as $d){
            $key=$d->business_key.$d->delivey_id;
            if(!isset($mont_invoice_arr[$key])){
                $mont_invoice_arr[$key]=$d->active_amount;
            }else{
                $mont_invoice_arr[$key]=$mont_invoice_arr[$key]+$d->active_amount;
            }
        }
        return $mont_invoice_arr;
    }



}
