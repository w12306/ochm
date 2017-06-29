<?php

namespace App\Http\Controllers\Admin;


use App\Models\BackcashModel;
use App\Models\InvoiceModel;
use App\Repositories\BackcashInvoiceRepository;
use App\Repositories\InvoiceDeliveryRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use Symfony\Component\Finder\SplFileInfo;
use File;
use Illuminate\Support\Str;
use Validator;
use DB;
use App\Models\Badcash;
use App\Repositories\EarnestcashRepository;
use App\Repositories\EarnestcashMortgageRepository;
use App\Repositories\EarnestcashRefundRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\BusinessTeamRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\DictionaryRepository;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Services\ExcelMaker;
use App\Services\Admin\ActionLog;
use Admin;

/**
 * 保证金管理
 *
 * @author  AaronLiu <liukan0926@foxmail.com>
 * @package App\Http\Controllers\Admin
 */
class EarnestcashController extends Controller
{
    use CommonResponse;

    /**
     * @var EarnestcashRepository
     */
    protected $earnestcashRepository;

    /**
     * @var PartnersRepository
     */
    protected $partnersRepository;

    /**
     * @var BusinessTeamRepository
     */
    protected $businessTeamRepository;

    /**
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @var DictionaryRepository
     */
    protected $dictionaryRepository;

    /**
     * @var EarnestcashMortgageRepository
     */
    protected $earnestcashMortgageRepository;

    /**
     * @var EarnestcashRefundRepository
     */
    protected $earnestcashRefundRepository;

    protected $backcashInvoiceRepository;

    protected $invoiceDeliveryRepository;

    protected $invoiceModel;

    protected $backcashModel;

    protected $actionLog;

    /**
     * @param EarnestcashRepository  $earnestcashRepository
     * @param PartnersRepository     $partnersRepository
     * @param DeliveryRepository     $deliveryRepository
     * @param DictionaryRepository   $dictionaryRepository
     * @param BusinessTeamRepository $businessTeamRepository
     */
    public function __construct(EarnestcashRepository $earnestcashRepository,
                                PartnersRepository $partnersRepository,
                                DeliveryRepository $deliveryRepository,
                                DictionaryRepository $dictionaryRepository,
                                EarnestcashMortgageRepository $earnestcashMortgageRepository,
                                EarnestcashRefundRepository $earnestcashRefundRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                InvoiceDeliveryRepository $invoiceDeliveryRepository,
                                InvoiceModel $invoiceModel,
                                BackcashModel $backcashModel,
                                ActionLog $actionLog)
    {
        $this->earnestcashRepository         = $earnestcashRepository;
        $this->partnersRepository            = $partnersRepository;
        $this->deliveryRepository            = $deliveryRepository;
        $this->earnestcashMortgageRepository = $earnestcashMortgageRepository;
        $this->earnestcashRefundRepository   = $earnestcashRefundRepository;
        $this->dictionaryRepository          = $dictionaryRepository;
        $this->businessTeamRepository        = $businessTeamRepository;
        $this->backcashInvoiceRepository     = $backcashInvoiceRepository;
        $this->invoiceDeliveryRepository     = $invoiceDeliveryRepository;
        $this->invoiceModel                  = $invoiceModel;
        $this->backcashModel                 = $backcashModel;
        $this->actionLog                     = $actionLog;
    }

    /**
     * 保证金列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $renderData = [];

        //获得通用数据 ----------------------------------------------------------

        //获得所有合作方信息
        $renderData['partners'] = $this->partnersRepository->getAllPartnersArray();

        return view('admin.earnestcash.list', $renderData);
    }


    public function seachWhere($request){
        $where = [];
        //已选择的合作方
        if ($request->has('partner_id_csv')) {
            $queryPartnerIdCSV = $request->get('partner_id_csv');
            $queryPartnerIds   = array_filter(explode(',', $queryPartnerIdCSV));
            $where[]           = function ($query) use ($queryPartnerIds) {
                /**
                 * @var $query QueryBuilder|EloquentBuilder
                 */
                $query->whereHas('partner', function ($query) use ($queryPartnerIds) {
                    /**
                     * @var $query QueryBuilder|EloquentBuilder
                     */
                    $query->whereIn('id', $queryPartnerIds);
                });
            };
        }
        //票据编号
        if ($request->has('bill_num')) {
            $queryBillNum = $request->get('bill_num');
            $where[]      = ['bill_num', '=', $queryBillNum];
        }
        //收款时间
        $where[] = static::getWhereConditionForTimeSpan(
                'reception_time',
                'reception_time',
                $request->get('btime'),
                $request->get('etime')
        );
        return $where;
    }

    /**
     * 获得保证金动态表格数据接口
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getListTable(Request $request)
    {
        $perPage = 20;

        $returnData = [
            'title' => [
                ['name' => '保证金单号', 'key' => 'earnestcash_key', 'type' => 1],
                ['name' => '合作方', 'key' => 'partner_company_name', 'type' => 1],
                ['name' => '保证金金额', 'key' => 'amount', 'type' => 1],
                ['name' => '剩余保证金金额', 'key' => 'last_amount', 'type' => 1],
                ['name' => '收款时间', 'key' => 'reception_time', 'type' => 1],
                ['name' => '收款银行', 'key' => 'bank', 'type' => 1],
                ['name' => '票据类型', 'key' => 'bill_type', 'type' => 1],
                ['name' => '票据编号', 'key' => 'bill_num', 'type' => 1],
                ['name' => '抵款金额', 'key' => 'mortgage_amount', 'type' => 1],
                ['name' => '抵款时间', 'key' => 'mortgage_time', 'type' => 1],
                ['name' => '退款金额', 'key' => 'refund_amount', 'type' => 1],
                ['name' => '退款时间', 'key' => 'refund_time', 'type' => 1],
                ['name' => '备注', 'key' => 'remark', 'type' => 1],
                ['name' => '操作', 'key' => 'operate', 'type' => 1],
            ],
            'table' => [],
            'page'  => [
                'total'   => 0,
                'perpage' => 20,
                'curpage' => 1,
            ],
        ];
        //查询条件 --------------------------------------------------------------
        $where=$this->seachWhere($request);

        //查询数据 --------------------------------------------------------------

        $earnestcashEntries = $this->earnestcashRepository
            ->with([
                'partner',
                'earnestcashMortgage',
                'earnestcashRefund',
            ])
            ->applyWhere($where)
            ->applyOrder('updated_at', 'desc')
            ->paginate($perPage);

        //数据格式化
        $tableEntries = collect();

        $sum_amount=0.00;
        $amount_array=[];
        $sum_last_amount=0.00;
        $last_amount_array=[];
        $sum_mortgage_amount=0.00;
        $sum_refund_amount  =0.00;
        foreach ($earnestcashEntries as $earnestcash) {
            //首先添加一条没有任何抵款、退款的基础数据
            $opt=[];
            $sumAmount=$this->getTrueAmount($earnestcash->id);//该保证金已经录入 的抵款和退款的总额

            if($earnestcash->isshow==1){
                if($sumAmount<$earnestcash->amount) {
                    $opt = [
                            ['type' => '', 'title' => '<i hidid="" class="icon i-edit" title="编辑"></i>',
                                    'url' => route('admin.earnestcash.edit', ['id' => $earnestcash->id])],
                            ['type' => '', 'title' => '<i hidid="" class="icon i-deposit" title="抵款"></i>', 'id' => $earnestcash->id,
                                    "url" => route('admin.earnestcash.mortgage', $earnestcash->id)],
                            ['type' => 'refund', 'title' => '<i hidid="" class="icon i-refund" title="退款"></i>', 'id' => $earnestcash->id,
                                    'company' => $earnestcash->partner->company_name],
                            ['type' => 'delete', 'title' => '<i hidid="" class="icon i-del" title="删除"></i>', 'id' => $earnestcash->id],
                    ];
                }else{
                    $opt = [
                            ['type' => '', 'title' => '<i hidid="" class="icon i-edit" title="编辑"></i>',
                                    'url' => route('admin.earnestcash.edit', ['id' => $earnestcash->id])],
                            ['type' => 'delete', 'title' => '<i hidid="" class="icon i-del" title="删除"></i>', 'id' => $earnestcash->id],
                    ];
                }
            }else{
                $opt=[
                        ['type' => '', 'title' => '<font color="#CCCCCC">删除时间'.$earnestcash->deleted_at.'</font>'],

                ];
            }
            if($earnestcash->earnestcashMortgage->isEmpty() && $earnestcash->earnestcashRefund->isEmpty()) {

                $tableEntries->push([
                        'earnestcash_key' => ['key' => $earnestcash->id, 'value' => $earnestcash->earnestcash_key],
                        'partner_company_name' => ['key' => $earnestcash->id, 'value' => $earnestcash->partner->company_name],
                        'amount' => ['key' => $earnestcash->id, 'value' => $earnestcash->amount],
                        'last_amount' => ['key' => $earnestcash->id, 'value' => $earnestcash->last_amount],
                        'reception_time' => ['key' => $earnestcash->id, 'value' => $earnestcash->reception_time],
                        'bank' => ['key' => $earnestcash->id, 'value' => $earnestcash->bank],
                        'bill_type' => ['key' => $earnestcash->id, 'value' => $earnestcash->bill_type],
                        'bill_num' => ['key' => $earnestcash->id, 'value' => $earnestcash->bill_num],
                        'mortgage_amount' => ['key' => $earnestcash->id, 'value' => '--'],
                        'mortgage_time' => ['key' => $earnestcash->id, 'value' => '--'],
                        'refund_amount' => ['key' => $earnestcash->id, 'value' => '--'],
                        'refund_time' => ['key' => $earnestcash->id, 'value' => '--'],
                        'remark' => ['key' => $earnestcash->id, 'value' => str_limit($earnestcash->remark, 20)],
                        'operate' => ['key' => $earnestcash->id, 'value' => $opt],
                ]);
                $amount_array[$earnestcash->id]=$earnestcash->amount;
                $last_amount_array[$earnestcash->id]=$earnestcash->last_amount;
            }
            //每条抵款产生一条记录
            if(!$earnestcash->earnestcashMortgage->isEmpty()) {
                foreach ($earnestcash->earnestcashMortgage as $mortgage) {

                    $tableEntries->push([
                            'earnestcash_key' => ['key' => $earnestcash->id, 'value' => $earnestcash->earnestcash_key],
                            'partner_company_name' => ['key' => $earnestcash->id, 'value' => $earnestcash->partner->company_name],
                            'amount' => ['key' => $earnestcash->id, 'value' => $earnestcash->amount],
                            'last_amount' => ['key' => $earnestcash->id, 'value' => $earnestcash->last_amount],
                            'reception_time' => ['key' => $earnestcash->id, 'value' => $earnestcash->reception_time],
                            'bank' => ['key' => $earnestcash->id, 'value' => $earnestcash->bank],
                            'bill_type' => ['key' => $earnestcash->id, 'value' => $earnestcash->bill_type],
                            'bill_num' => ['key' => $earnestcash->id, 'value' => $earnestcash->bill_num],
                            'mortgage_amount' => ['key' => $earnestcash->id . '-m-' . $mortgage->id, 'value' => $mortgage->amount],
                            'mortgage_time' => ['key' => $earnestcash->id . '-m-' . $mortgage->id, 'value' => $mortgage->date],
                            'refund_amount' => ['key' => $earnestcash->id . '-m-' . $mortgage->id, 'value' => '--'],
                            'refund_time' => ['key' => $earnestcash->id . '-m-' . $mortgage->id, 'value' => '--'],
                            'remark' => ['key' => $earnestcash->id, 'value' => str_limit($earnestcash->remark, 20)],
                            'operate' => ['key' => $earnestcash->id, 'value' => $opt],
                    ]);
                    $amount_array[$earnestcash->id]=$earnestcash->amount;
                    $last_amount_array[$earnestcash->id]=$earnestcash->last_amount;
                    $sum_mortgage_amount=$sum_mortgage_amount+$mortgage->amount;
                }
            }

            //每条退款产生一条记录
            if(!$earnestcash->earnestcashRefund->isEmpty()) {
                foreach ($earnestcash->earnestcashRefund as $refund) {
                    $tableEntries->push([
                            'earnestcash_key' => ['key' => $earnestcash->id, 'value' => $earnestcash->earnestcash_key],
                            'partner_company_name' => ['key' => $earnestcash->id, 'value' => $earnestcash->partner->company_name],
                            'amount' => ['key' => $earnestcash->id, 'value' => $earnestcash->amount],
                            'last_amount' => ['key' => $earnestcash->id, 'value' => $earnestcash->last_amount],
                            'reception_time' => ['key' => $earnestcash->id, 'value' => $earnestcash->reception_time],
                            'bank' => ['key' => $earnestcash->id, 'value' => $earnestcash->bank],
                            'bill_type' => ['key' => $earnestcash->id, 'value' => $earnestcash->bill_type],
                            'bill_num' => ['key' => $earnestcash->id, 'value' => $earnestcash->bill_num],
                            'mortgage_amount' => ['key' => $earnestcash->id . '-r-' . $refund->id, 'value' => '--'],
                            'mortgage_time' => ['key' => $earnestcash->id . '-r-' . $refund->id, 'value' => '--'],
                            'refund_amount' => ['key' => $earnestcash->id . '-r-' . $refund->id, 'value' => $refund->amount],
                            'refund_time' => ['key' => $earnestcash->id . '-r-' . $refund->id, 'value' => $refund->date],
                            'remark' => ['key' => $earnestcash->id, 'value' => str_limit($earnestcash->remark, 20)],
                            'operate' => ['key' => $earnestcash->id, 'value' => $opt],
                    ]);
                    $amount_array[$earnestcash->id]=$earnestcash->amount;
                    $last_amount_array[$earnestcash->id]=$earnestcash->last_amount;
                    $sum_refund_amount=$sum_refund_amount+$refund->amount;
                }
            }
        }
        if(!empty($amount_array)){
            $sum_amount=array_sum($amount_array);
        }
        if(!empty($last_amount_array)){
            $sum_last_amount=array_sum($last_amount_array);
        }
        $tableEntries->push([
                'earnestcash_key' => ['key' => '', 'value' => '当前页合并:'],
                'partner_company_name' => ['key' => '', 'value' => ''],
                'amount' => ['key' => '', 'value' => static::numFormat($sum_amount)],
                'last_amount' => ['key' => '', 'value' => static::numFormat($sum_last_amount)],
                'reception_time' => ['key' => '', 'value' => ''],
                'bank' => ['key' => '', 'value' => ''],
                'bill_type' => ['key' => '', 'value' => ''],
                'bill_num' => ['key' => '', 'value' => ''],
                'mortgage_amount' => ['key' => '' , 'value' => static::numFormat($sum_mortgage_amount)],
                'mortgage_time' => ['key' => '', 'value' => '--'],
                'refund_amount' => ['key' => '', 'value' => static::numFormat($sum_refund_amount)],
                'refund_time' => ['key' => '' , 'value' =>''],
                'remark' => ['key' => '', 'value' => ''],
                'operate' => ['key' => '', 'value' => ''],
        ]);
        $returnData['table'] = $tableEntries->toArray();

        //更新分页信息
        $returnData['page'] = [
            'total'   => $earnestcashEntries->total(),
            'perpage' => $perPage,
            'curpage' => $earnestcashEntries->currentPage(),
        ];

        return $this->ajaxSuccess('', $returnData);
    }

    /**
     * 列表导出Excel
     *
     * @param Request    $request
     * @param ExcelMaker $excelMaker
     */
    public function getExportExcel(Request $request, ExcelMaker $excelMaker)
    {
        $where=$this->seachWhere($request);
        $where[]=['isshow','=',1];//只导出没有删除的数据
        //获取数据
        $earnestcashEntries = $this->earnestcashRepository
                ->with([
                        'partner',
                        'earnestcashMortgage',
                        'earnestcashRefund',
                ])
                ->applyWhere($where)
                ->applyOrder('updated_at', 'desc')
                ->all();

        //表头
        $headers = [
                '保证金单号',
                '合作方',
                '保证金金额',
                '剩余保证金金额',
                '收款时间',
                '收款银行',
                '票据类型',
                '票据编号',
                '抵款金额',
                '抵款时间',
                '退款金额',
                '退款时间',
                '备注',
        ];

        //格式化数据
        $rows=[];
        $earnestcashEntries->map(function ($earnestcash) use (&$rows){

            if($earnestcash->earnestcashMortgage->isEmpty() && $earnestcash->earnestcashRefund->isEmpty()) {
                $rows[]=[
                        $earnestcash->earnestcash_key,
                        $earnestcash->partner->company_name,
                        $earnestcash->amount,
                        $earnestcash->last_amount,
                        $earnestcash->reception_time,
                        $earnestcash->bank,
                        $earnestcash->bill_type,
                        $earnestcash->bill_num,
                        '--',
                        '--',
                        '--',
                        '--',
                        $earnestcash->remark
                ];
            }
            //每条抵款产生一条记录
            if(!$earnestcash->earnestcashMortgage->isEmpty()) {
                foreach ($earnestcash->earnestcashMortgage as $mortgage) {
                    $rows[]=[
                            $earnestcash->earnestcash_key,
                            $earnestcash->partner->company_name,
                            $earnestcash->amount,
                            $earnestcash->last_amount,
                            $earnestcash->reception_time,
                            $earnestcash->bank,
                            $earnestcash->bill_type,
                            $earnestcash->bill_num,
                            $mortgage->amount,
                            $mortgage->date,
                            '--',
                            '--',
                            $earnestcash->remark
                    ];
                }
            }
            //每条退款产生一条记录
            if(!$earnestcash->earnestcashRefund->isEmpty()) {
                foreach ($earnestcash->earnestcashRefund as $refund) {
                    $rows[] = [
                            $earnestcash->earnestcash_key,
                            $earnestcash->partner->company_name,
                            $earnestcash->amount,
                            $earnestcash->last_amount,
                            $earnestcash->reception_time,
                            $earnestcash->bank,
                            $earnestcash->bill_type,
                            $earnestcash->bill_num,
                            '--',
                            '--',
                            $refund->amount,
                            $refund->date,
                            $earnestcash->remark
                    ];
                }
            }
            return ;
        });
        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }


    public function mortgageApiWhere(Request $request){
        $where=[];
        $partner_id=$request->get('partner_id');
        $business_key=$request->get('business_key');
        //合作方
        if(empty($partner_id) ){
            return $this->ajaxError('参数缺失');
        }
        if(!is_array($partner_id)){
            $partner_id=[$partner_id];
        }
        $where[] = function ($query) use ($partner_id) {
            $query->whereIn('partner_id', $partner_id);
        };

        //业务编号
        if(!empty($business_key) ){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=', $business_key);
            };
        }
        $where[] = function ($query) use ($business_key) {
            $query->where('isshow','=', 1);
        };
        return $where;
    }
    public function mortgageApiBackCashData($where){
        return $this->backcashInvoiceRepository->with(['partner','delivery'])
                ->applyWhere($where)
                ->applyOrder('updated_at','desc')->all();
    }

    public function mortgageApiInvoiceData($where){
        return $this->invoiceDeliveryRepository->with(['partner','delivery'])
                ->applyWhere($where)
                ->applyOrder('updated_at','desc')->all();
    }

    /**抵款
     * 保证金转 回款筛选接口
     * 需要查询相关数据的发票数据 再查询相关数据的回款数据  然后根据业务合作方执行月小组为主键合并数据显示
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getAddMortgageApi(Request $request){
        $where=$this->mortgageApiWhere($request);
        $teamDept=$this->dictionaryRepository->getDeptTeam();

        //获得剩余保证金金额
        $earnestcash_id=$request->input('earnestcash_id');
        $earnestcash=$this->earnestcashRepository->find($earnestcash_id);
        $last_amount=$earnestcash->last_amount;
        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_key", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月", "key" => "month", "type" => 1],
                ["name" => "月执行金额", "key" => "month_amount", "type" => 1],
                ["name" => "已开发票总额", "key" => "all_invoice_amount", "type" => 1],
                ["name" => "已回款总额", "key" => "all_backcash_amount", "type" => 1],
                ["name" => "发票金额", "key" => "invoice_amount", "type" => 1],
                ["name" => "回款金额", "key" => "backcash_amount", "type" => 1],
        ];
        $table=[];
        //--------------查询条件下所有回款的数据------------------
        $backcashdata = $this->mortgageApiBackCashData($where);

        foreach($backcashdata as $v){
            $business_key   =$v->business_key;
            $team           =$teamDept[$v->delivery->team];
            $month          =$v->delivery->month;
            $month_amount   =$v->delivery->amount;
            $partner_id     =$v->partner_id;
            $key=$business_key.$partner_id.$v->delivery_id;
            $all_backcash_amount=0.00;

            $textname= $this->madeTextKey($business_key, $partner_id,$v->delivery_id);
            if(isset($table[$key])){
                $all_backcash_amount=str_replace(',','',$table[$key]['all_backcash_amount']['value'])+$v->active_amount;
            }else{
                $all_backcash_amount=$v->active_amount;
            }
            //判断输入的发票金额验证 剩余保证金和剩余发票金额 取最小的一个
            $max_amount=$last_amount;
            if($last_amount>$month_amount){
                $max_amount=$month_amount;
            }
            $table[$key]=[
                    "business_key"=>["key"=>$business_key,"value"=>$business_key],
                    "partner_id" =>["key"=>$business_key.$partner_id,"value"=>$v->partner->company_name],
                    "team"=>["key"=>$business_key.$partner_id.$team,"value"=>$team],
                    "month"=>["key"=>$key,"value"=>$month],
                    "month_amount"=>["key"=>$key.$month_amount, "value"=>$month_amount],
                    "all_invoice_amount"=>["key"=>$key,"value"=>0.00],
                    "all_backcash_amount"=>["key"=>$key,"value"=>static::numFormat($all_backcash_amount)],
                    "invoice_amount"=>
                            ["key"=>$v->id,
                                    "value"=>"",
                                    "name"=>$textname."_add",
                                    "type"=>"edit",
                                    "max" =>round($max_amount,2),
                                    "tips" =>"输入的金额必须小于或等于当月剩余发票金额！",
                            ],
                    "backcash_amount"=>["key"=>$v->id,"value"=>''],
            ];
        }

        //------------查询条件下所有发票的数据-----------------------------
        $invoicedata = $this->mortgageApiInvoiceData($where);
        foreach($invoicedata as $v){
            $business_key   =$v->business_key;
            $team           =$teamDept[$v->delivery->team];
            $month          =$v->delivery->month;
            $month_amount   =$v->delivery->amount;
            $partner_id     =$v->partner_id;
            $key=$business_key.$partner_id.$v->delivey_id;
            $all_invoice_amount=0.00;
            $all_backcash_amount=0.00;

            $textname= $this->madeTextKey($business_key, $partner_id,$v->delivery_id);
            if(isset($table[$key])){
                $all_invoice_amount=str_replace(',','',$table[$key]['all_invoice_amount']['value'])+$v->active_amount;
                $all_backcash_amount=$table[$key]['all_backcash_amount']['value'];
            }else{
                $all_invoice_amount=$v->active_amount;
            }
            //判断输入的发票金额验证 剩余保证金和剩余发票金额 取最小的一个
            $max_amount=$last_amount;
            if($last_amount>$month_amount){
                $max_amount=$month_amount-$all_invoice_amount;
            }
            $table[$key]=[
                    "business_key"=>["key"=>$business_key,"value"=>$business_key],
                    "partner_id" =>["key"=>$business_key.$partner_id,"value"=>$v->partner->company_name],
                    "team"=>["key"=>$business_key.$partner_id.$team,"value"=>$team],
                    "month"=>["key"=>$key,"value"=>$month],
                    "month_amount"=>["key"=>$key.$month_amount,"value"=>$month_amount],
                    "all_invoice_amount"=>["key"=>$key,"value"=>static::numFormat($all_invoice_amount)],
                    "all_backcash_amount"=>["key"=>$key,"value"=>$all_backcash_amount],
                    "invoice_amount"=>
                            ["key"=>$v->id,
                                    "value"=>"",
                                    "name"=>$textname."_add",
                                    "type"=>"edit",
                                    "max" =>round($max_amount,2),
                                    "tips" =>"输入的金额必须小于或等于当月剩余发票金额！",
                            ],
                    "backcash_amount"=>["key"=>$v->id,"value"=>''],
            ];
        }
        $returnTable=[];
        sort($table);
        foreach($table as $k=>$v){
            $returnTable[]=$v;
        }
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$returnTable,
        ]
        ];
        return json_encode($returnJsonData);
    }

    /**
     *保存 保证金转 发票/回款数据
     * 操作内容：
     * 1.录入保证金这边的抵款信息
     * 2.录入新增发票信息
     * 3.录入新增回款信息
     * 4.记录日志
     * 注：这里录入的发票和回款可能会对应到多个业务/合作方 存在拆分
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function storeMortgage(Request $request){
        $earnestcash_id=$request->input('earnestcash_id');//保证金ID
        $earnestcash_key=$request->input('earnestcash_key');//保证金单号
        $mortgageArr['earnestcash_id']  =$earnestcash_id;
        $mortgageArr['amount']          =$request->input('total_amount');
        $mortgageArr['date']            =date('Y-m-d H:i:s',time());

        $invoice_amount = $request->input('total_amount');
        DB::beginTransaction();//事务开始
        $earnestcash=$this->earnestcashRepository->find($earnestcash_id);

        //判断是否够扣款
        $this->checkAmount($earnestcash->last_amount,$invoice_amount);
        $last_amount=$earnestcash->last_amount - $invoice_amount;
        //更新剩余的保证金
        $this->earnestcashRepository->update($earnestcash->id, [
        'last_amount' => $last_amount,
        ]);

        //1.录入保证金这边的抵款信息
        $this->earnestcashMortgageRepository->create($mortgageArr);

        //构建添加页面中有多少个输入框
        $where=$this->mortgageApiWhere($request);
        $backcashdata = $this->mortgageApiBackCashData($where);
        $textArr=[];
        foreach($backcashdata as $v){
            $key= $this->madeTextKey($v->business_key, $v->partner_id,$v->delivery_id);
            $textArr[$key]['business_key']=$v->business_key;
            $textArr[$key]['partner_id']=$v->partner_id;
            $textArr[$key]['team_id']=$v->team_id;
            $textArr[$key]['delivery_id']=$v->delivery_id;
            $textArr[$key]['team']=$v->team;
            $textArr[$key]['team_month']=$v->month;
            $textArr[$key]['team_amount']=$v->month_amount;
        }
        $invoicedata = $this->mortgageApiInvoiceData($where);
        foreach($invoicedata as $v){
            $key= $this->madeTextKey($v->business_key, $v->partner_id,$v->delivery_id);
            $textArr[$key]['business_key']=$v->business_key;
            $textArr[$key]['partner_id']=$v->partner_id;
            $textArr[$key]['team_id']=$v->team_id;
            $textArr[$key]['delivery_id']=$v->delivey_id;
            $textArr[$key]['team']=$v->team;
            $textArr[$key]['team_month']=$v->team_month;
            $textArr[$key]['team_amount']=$v->team_amount;
        }

        //2.录入新增发票信息
        $invoice_key = $request->input('invoice_key');
        $invoice_type=$request->input('invoice_type');
        $invoiceData['invoice_key'] = $invoice_key;
        $invoiceData['invoice_type'] = $invoice_type;
        $invoiceData['invoice_time'] = $request->input('invoice_time');
        $invoiceData['amount'] = $invoice_amount;
        $invoiceData['remark'] = "保证金（单号".$earnestcash_key."）转回款，开发票".$invoice_amount."元";
        $invoice = $this->invoiceModel->create($invoiceData);//录入发票主表信息
        $invoice_id = $invoice->id;

        //3.录入新增回款信息
        $backData['amount']         =$invoice_amount;//发票和回款的金额是一样的
        $backData['backtime']       =$request->input('backcash_time');
        $backData['bank']           =$request->input('backcash_bank');
        $backData['backtype']       =$request->input('backtype');
        $backData['remark']         ="保证金（单号".$earnestcash_key."）转回款，回款金额".$invoice_amount."元";
        $backcash=$this->backcashModel->create($backData);
        $backcash_id=$backcash->id;

        //更新回款单号
        $backUpData['backcash_key']    =static::makeNumber($backcash_id,'HK');
        $this->backcashModel->where('id','=',$backcash_id)->update($backUpData);

        foreach($textArr as $k=>$v) {
            $active_amount=$request->input($k.'_add');
            if($active_amount==""){
                continue;
            }
            //----录入发票明细信息
            $saveData['invoice_id'] = $invoice_id;
            $saveData['delivey_id'] = $v['delivery_id'];
            $saveData['team_id']    = $v['team_id'];
            $saveData['active_amount'] = $active_amount;
            //冗余数据
            $saveData['invoice_key'] = $invoice_key;
            $saveData['partner_id']  =  $v['partner_id'];
            $saveData['team'] =  $v['team'];
            $saveData['team_month']     =  $v['team_month'];
            $saveData['team_amount']    =  $v['team_amount'];
            $saveData['business_key']   =  $v['business_key'];
            $invoiceDelivery=$this->invoiceDeliveryRepository->create($saveData);
            $invoice_delivery_id=$invoiceDelivery->id;
            //----录入回款明细
            $saveData['backcash_id']        =   $backcash_id;
            $saveData['invoice_delivery_id']=   $invoice_delivery_id;
            $saveData['invoice_id']         =   $invoice_id;
            $saveData['invoice_key']    =   $invoice_key;
            $saveData['invoice_amount'] =   $invoice_amount;
            $saveData['invoice_type']   =   $invoice_type;
            $saveData['invoice_month_amount']   =$active_amount;
            $saveData['delivery_id']    =   $v['delivery_id'];
            $saveData['team_id']        =   $v['team_id'];
            $saveData['month']          =   $v['team_month'];
            $saveData['month_amount']   =   $v['team_amount'];
            $saveData['business_key']   =   $v['business_key'];;
            $saveData['team']           =   $v['team'];
            $saveData['active_amount']  =   $active_amount;
            $saveData['partner_id']     =   $v['partner_id'];

            $this->backcashInvoiceRepository->create($saveData);
        }
        //记录日志
        try{
            $this->actionLog->log('earnestcash.deduct',
                    Admin::user()->id,
                    [  'id'         =>$earnestcash_id,
                       'amount'     =>$mortgageArr['amount'] ,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return $this->ajaxSuccess("保存成功!",['url'=>route('admin.earnestcash.list')]);
    }

    /**
     *添加保证金
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return mixed
     */
    public function getAdd(Request $request)
    {
        $renderData = [];

        //获得所有合作方信息
        $partners               = $this->partnersRepository->getAllPartnersArray();
        $renderData['partners'] = collect($partners);

        //获得所有银行
        $banks               = $this->dictionaryRepository
            ->applyWhere([
                ['type', '=', 'bank'],
            ])
            ->all();
        $renderData['banks'] = $banks;

        //获得所有票据类型
        $billTypes               = $this->dictionaryRepository
            ->applyWhere([
                ['type', '=', 'invoice_type'],
            ])
            ->all();
        $renderData['billTypes'] = $billTypes;

        return view('admin.earnestcash.edit', $renderData);
    }

    /**
     *保存保证金
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postStore(Request $request)
    {
        $inputs = $request->except(['earnestcash_key']);
        $inputs['last_amount']=$request->input('amount');

        DB::beginTransaction();

        $earnestcash = $this->earnestcashRepository->create($inputs);
        $earnestcash->updateKey();

        //记录日志
        try{
            $this->actionLog->log('earnestcash.create',
                    Admin::user()->id,
                    [  'id'         =>$earnestcash->id,
                       'amount'     =>$inputs['last_amount'],
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }

        DB::commit();

        return $this->ajaxSuccess('添加成功!', [
            'url' => route('admin.earnestcash.list')
        ]);
    }

    /**
     *修改保证金
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $earnestcashId
     * @return mixed
     */
    public function getEdit($earnestcashId)
    {
        $renderData = [];

        $earnestcash               = $this->earnestcashRepository->find($earnestcashId);
        $renderData['earnestcash'] = $earnestcash;

        //获得所有合作方信息
        $partners               = $this->partnersRepository->getAllPartnersArray();
        $renderData['partners'] = collect($partners);

        //获得所有银行
        $banks               = $this->dictionaryRepository
            ->applyWhere([
                ['type', '=', 'bank'],
            ])
            ->all();
        $renderData['banks'] = $banks;

        //获得所有票据类型
        $billTypes               = $this->dictionaryRepository
            ->applyWhere([
                ['type', '=', 'invoice_type'],
            ])
            ->all();
        $renderData['billTypes'] = $billTypes;

        return view('admin.earnestcash.edit', $renderData);
    }

    /**
     * 更新保证金
     *
     * @param Request $request
     * @param         $earnestcashId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postUpdate(Request $request, $earnestcashId)
    {
        $earnestcash = $this->earnestcashRepository->find($earnestcashId);

        $inputs = $request->only([
            'partner_id',
            'reception_time',
            'bill_type',
            'bill_num',
            'bank',
            'amount',
            'remark',
        ]);

        DB::beginTransaction();
        //检测本次更新有没有更新金额  如果更新了金额 需要验证金额是否小于已经增加的抵款+退款金额之和
        if($inputs['amount']!=$earnestcash->amount){
            $sumAmount=$this->getTrueAmount($earnestcash->id);
            $this->checkAmount($inputs['amount'],$sumAmount,'保证金金额小于已经录入的退款和抵款，无法修改！');
            $inputs['last_amount']=$inputs['amount']-$sumAmount;
        }
        $this->earnestcashRepository->update($earnestcash->id, $inputs);
        //记录日志
        try{
            $this->actionLog->log('earnestcash.edit',
                    Admin::user()->id,
                    [  'id'         =>$earnestcash->id,
                       'amount'     =>$inputs['amount'],
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }
        DB::commit();
        return $this->ajaxSuccess('更新成功！', [
            'url' => route('admin.earnestcash.list')
        ]);
    }

    /**
     *保证金转回款 添加页面
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function  addMortgage($id){
        $data=$this->earnestcashRepository->find($id);
        $partner=$this->partnersRepository->getPartnerById($data->partner_id);
        $data->partner=$partner->company_name;
        $viewData['mortgage']=$data;

        //加载回款方式
        $viewData['backcash_typeData']=$this->dictionaryRepository->returnTypeArray('backcash_type');
        //加载银行信息
        $viewData['bankData']=$this->dictionaryRepository->returnTypeArray('bank');

        return view('admin.earnestcash.mortgage',$viewData);
    }


    /**
     * 保存退款记录
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    public function postStoreRefund(Request $request)
    {
        $earnestcashId = $request->input('id');//保证金主单id
        $id = $request->input('earnestcash_id');//退款数据id
        $queryAmount        = $request->input('amount');
        $currentDate = Carbon::now()->toDateString();

        DB::beginTransaction();

        $earnestcash = $this->earnestcashRepository->find($earnestcashId);
        if(!empty($id)){//编辑
            $this->earnestcashRefundRepository->update($id,[
                    'amount'         => $queryAmount,
            ]);
            $sumAmount=$this->getTrueAmount($earnestcashId);
            //判断是否够扣款
            $this->checkAmount($earnestcash->amount,$sumAmount);
            $last_amount=$earnestcash->amount - $sumAmount;
        }else{//新增
            //判断是否够扣款
            $this->checkAmount($earnestcash->last_amount,$queryAmount);
            $this->earnestcashRefundRepository->create([
                    'earnestcash_id' => $earnestcashId,
                    'amount'         => $queryAmount,
                    'date'           => $currentDate,
            ]);
            $last_amount=$earnestcash->last_amount - $queryAmount;
        }
        $this->earnestcashRepository->update($earnestcash->id, [
            'last_amount' => $last_amount,
        ]);
        //记录日志
        try{
            $this->actionLog->log('earnestcash.refund',
                    Admin::user()->id,
                    [  'id'         =>$earnestcash->id,
                       'amount'     =>$queryAmount,
                    ]
            );
        }catch (Exception $e){
            DB::rollBack();
        }

        DB::commit();

        return $this->ajaxSuccess('操作成功!');
    }

    /**
     *检测输入的金额是否超出保证金
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $allAmount
     * @param $amount
     * @throws BusinessException
     */
    public function checkAmount($allAmount,$amount,$msg='数额超过了保证金余额，无法生效'){
        if ($allAmount - $amount < 0) {
            DB::rollBack();
            throw new BusinessException($msg);
        }
    }

    /**
     *查询 抵款和退款总额是否超过保证金
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $id
     * @return float|mixed
     */
    public function getTrueAmount($id){
        $sumMortgage=$this->earnestcashMortgageRepository->sumAmountByPid($id);
        $sumRefund=$this->earnestcashRefundRepository->sumAmountByPid($id);
        if(empty($sumMortgage)){$sumMortgage=0.00;}
        if(empty($sumRefund)){$sumRefund=0.00;}
        return $sumMortgage+$sumRefund;
    }

    /**
     * 删除保证金
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postDelete(Request $request)
    {
        $queryId = $request->input('id');

        $this->earnestcashRepository->deleteEarnestcash($queryId);
        $this->actionLog->log('earnestcash.delete',
                    Admin::user()->id,
                    [  'id'         =>$queryId,
                       'amount'     =>0,
                    ]
        );


        return $this->ajaxSuccess('删除成功!');
    }

    /**
     * 保证金 抵款列表API
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getMortgageListTable($id=""){
        if(empty($id)){return json_encode(['status'=>'error','info'=>'参数缺失','data'=>'']);}
        $mortgage=$this->earnestcashRepository->with(['earnestcashMortgage'])
                ->applyWhere([
                        ['id','=',$id],
                ])
                ->find($id);

        $returnData=array();
        foreach($mortgage->earnestcashMortgage as $k=>$d){
            $returnData[]=[
                    'id'=>$d['id'],
                    'date'=>$d['date'],
                    'amount'=>$d['amount'],
            ];
        }
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     * 删除保证金-抵款
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postDeleteMortgage(Request $request)
    {
        $queryId = $request->input('id');

        DB::beginTransaction();//事务开始

        $mortgage=$this->earnestcashMortgageRepository->find($queryId);

        $earnestcash=$this->earnestcashRepository->find($mortgage->earnestcash_id);

        $this->earnestcashMortgageRepository->delete($queryId);
        //更新剩余的保证金金额
        $this->earnestcashRepository->update($earnestcash->id, [
                'last_amount' => $earnestcash->amount-$this->getTrueAmount($earnestcash->id),
        ]);

        DB::commit();

        return $this->ajaxSuccess('删除成功!');
    }

    /**
     *保证金 退款列表API
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function getRefundListTable($id=""){
        if(empty($id)){return json_encode(['status'=>'error','info'=>'参数缺失','data'=>'']);}
        $mortgage=$this->earnestcashRepository->with(['earnestcashRefund'])
                ->applyWhere([
                        ['id','=',$id],
                ])
                ->find($id);

        $returnData=array();
        foreach ($mortgage->earnestcashRefund  as $rd) {
            $returnData[]=[
                        'id'=>$rd['id'],
                        'date'=>$rd['date'],
                        'amount'=>$rd['amount'],
            ];
        }
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     * 删除保证金-退款
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postDeleteRefund(Request $request)
    {
        $queryId = $request->input('id');

        DB::beginTransaction();//事务开始

        $refund=$this->earnestcashRefundRepository->find($queryId);
        $earnestcash=$this->earnestcashRepository->find($refund->earnestcash_id);
        $this->earnestcashRefundRepository->delete($queryId);
        //更新剩余的保证金金额
        $this->earnestcashRepository->update($earnestcash->id, [
                'last_amount' => $earnestcash->amount-$this->getTrueAmount($earnestcash->id),
        ]);

        DB::commit();
        return $this->ajaxSuccess('删除成功!');
    }
    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $business_key
     * @param $partner_id
     * @param $delivey_id
     * @return string
     */
    public function madeTextKey($business_key,$partner_id,$delivey_id){
        $key=$business_key.$partner_id.$delivey_id;
        return $key;
    }

}
