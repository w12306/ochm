<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\BusinessModel;
use App\Models\Contract;
use App\Models\DeliveryModel;
use App\Repositories\BusinessRepository;
use Illuminate\Http\Request;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use File;
use Validator;
use DB;
use Admin;
use App\Repositories\DeliveryRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\DictionaryRepository;
use App\Repositories\BusinessTeamRepository;
use App\Repositories\ExpensesDeliveyRepository;
use App\Repositories\UnderPartnersRepository;
use App\Repositories\BackcashInvoiceRepository;
use App\Services\ExcelMaker;
/**
 * 统计数据处理
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class StFinancialBaseController extends Controller {
    use CommonResponse;

    protected $deliveryRepository;

    protected $companyRepository;

    protected $partnersRepository;

    protected $dictionaryRepository;

    protected $businessTeamRepository;

    protected $expensesDeliveyRepository;

    protected $underPartnersRepository;

    protected $backcashInvoiceRepository;

    protected $businessRepository;

    protected $deliveryModel;

    static $fields=[
            'company_type'=>'代理/直客',
            'partner'=>'合作方',
            'company'=>'客户名称',
            'product_type'=>'产品类型',
            'product_name'=>'产品名称',
            'business_line'=>'业务线',
            'business_type'=>'业务类型',
            'btime'=>'执行开始时间',
            'etime'=>'执行结束时间',
            'paytime'=>'约定回款日期',
            'contract_key'=>'合同编号',
            'contract_status'=>'合同状态',
            'confirm_status'=>'确认函状态',
            'zq_days'=>'账期（天）',
            'team_amount_total'=>'执行总金额（小组执行金额总和）',
            'team_month'=>'执行月',
            'team_month_amount'=>'月执行金额',
            'team_month_invoice'=>'月发票总额',
            'team_month_backcash'=>'月回款总额',
            'team_month_expenses'=>'月费用总额',
            'team_month_badcash'=>'月坏账金额',
            'badcash_time'=>'月坏账录入时间',
            'yszk_amount'=>'应收账款余额',
            'invoice_amount'=>'录入的发票金额',
            'invoice_type'=>'发票类型',
            'invoice_key'=>'发票编号',
            'invoice_time'=>'开票日期',
            'backcash_amount'=>'录入的回款金额',
            'backcash_bank'=>'回款银行',
            'backcash_type'=>'回款方式',
            'backcash_time'=>'回款时间',
            'backcash_days'=>'执行完毕后回款天数',
    ];
    /**
     *
     */
    public function __construct(
                                BusinessRepository $businessRepository,
                                DeliveryRepository $deliveryRepository,
                                CompanyRepository $companyRepository,
                                PartnersRepository $partnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                ExpensesDeliveyRepository $expensesDeliveyRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                DeliveryModel $deliveryModel)
    {
        $this->deliveryRepository=$deliveryRepository;
        $this->companyRepository=$companyRepository;
        $this->partnersRepository=$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->businessTeamRepository=$businessTeamRepository;
        $this->expensesDeliveyRepository=$expensesDeliveyRepository;
        $this->underPartnersRepository=$underPartnersRepository;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
        $this->businessRepository=$businessRepository;
        $this->deliveryModel=$deliveryModel;
    }

    /**
     *组织 统计页面 顶部查询表单基础数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function seachFormData(){
        $viewData=[];
        //上游客户
        $viewData['companyData']=$this->companyRepository->getAllCompanyArray();

        //组织上游合作方
        $viewData['partnersData']=$this->partnersRepository->getAllPartversArray();

        //组织下游游合作方
        $viewData['underpartnersData']=$this->underPartnersRepository->getAllPartversArray();

        //组织业务类型 查询
        $viewData['business_type']=$this->dictionaryRepository->returnTypeArray('business_type');

        //组织业务线 查询
        $viewData['business_line']=$this->dictionaryRepository->returnTypeArray('business_line');

        //组织执行小组 查询
        $viewData['team']=$this->dictionaryRepository->madeDeptTeamArray();

        //合同状态
        $viewData['contract_status']=Contract::$statusList;
        //业务确认状态
        $viewData['confirm_status']=BusinessModel::$confirm_status;

        return $viewData;
    }

    /**
     * 查询页面 提交查询条件
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $request
     * @return string
     */
    public function businessWhere($request) {
        /*******业务表数据查询条件*************************************************************************/
        $businessWhere = "";
        //业务客户
        if ($request->has('company_id')) {
            $company_id = $request->get('company_id');
            $businessWhere .= ' company_id in (' . $company_id . ')';
        }
        //业务合作方
        if ($request->has('partner_id')) {
            $partner_id = $request->get('partner_id');
            if (!empty($businessWhere)) {
                $businessWhere .= ' or ';
            }
            $businessWhere .= '  partner_id in (' . $partner_id . ')';
        }
        //业务类型
        if ($request->has('business_type')) {
            $business_type = $request->get('business_type');
            $business_type_str = '';
            foreach ($business_type as $k => $v) {
                if (empty($business_type_str)) {
                    $business_type_str = '"' . $v . '"';
                } else {
                    $business_type_str .= $business_type_str . ',"' . $v . '"';
                }
            }
            if (!empty($businessWhere)) {
                $businessWhere .= ' or ';
            }
            $businessWhere .= ' business_type in (' . $business_type_str . ')';
        }
        //业务线
        if ($request->has('business_line')) {
            $business_line = $request->get('business_line');
            $business_line_str = '';
            foreach ($business_line as $k => $v) {
                if (empty($business_line_str)) {
                    $business_line_str = ' business_line like "%' . $v . '%" ';
                } else {
                    $business_line_str .= ' or business_line like "%' . $v . '%" ';
                }
            }
            if (!empty($businessWhere)) {
                $businessWhere .= ' or ';
            }
            $businessWhere .= '   ( ' . $business_line_str . ' )';
        }
        //业务编号
        if ($request->has('business_key')) {
            $business_key = $request->get('business_key');
            if (!empty($businessWhere)) {
                $businessWhere .= ' or ';
            }
            $businessWhere .= '  business_key="' . $business_key . '"';
        }
        //业务合同类型
        if ($request->has('business_contract_type')) {
            $business_contract_type = $request->get('business_contract_type');
            if (!empty($businessWhere)) {
                $businessWhere .= ' or ';
            }
            $businessWhere .= '  contract_type="' . $business_contract_type . '"';
        }

        //业务确认函状态
        if ($request->has('confirm_status')) {
            $confirm_status = $request->get('confirm_status');
            if (!empty($businessWhere)) {
                $businessWhere .= ' or ';
            }
            $businessWhere .= '  confirm_status in (' . implode(',',$confirm_status) . ')';
        }

        if(empty($businessWhere)){$businessWhere=' 1=1 ';}
        return $businessWhere.' and  ' . session('company_sql') ;
    }

    public function businessTeamWhere($request){
        /******小组表查询条件***************************************************************/
        $teamWhere='';
        if ($request->has('team')) {
            $team_str=implode(',',$request->get('team'));
            if(!empty($teamWhere)){$teamWhere.=' or ';}
            $teamWhere.=' team in ( '.$team_str.' )';
        }

        //发票状态
        $invoice_status=$request->get('invoice_status');
        if(!empty($invoice_status)){
            $invoice_status=implode(',',$invoice_status);
            if(!empty($teamWhere)){$teamWhere.=' or ';}
            $teamWhere.='  `invoice_status` in ('.$invoice_status.') ';
        }

        //回款状态
        $backcash_status=$request->get('backcash_status');
        if(!empty($backcash_status)){
            $backcash_status=implode(',',$backcash_status);
            if(!empty($teamWhere)){$teamWhere.=' or ';}
            $teamWhere.='  `backcash_status` in ('.$backcash_status.') ';
        }


        if(empty($teamWhere)){$teamWhere=' 1=1 ';}
        return $teamWhere;
    }

    /**
     *月执行额数据查询条件
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $request
     * @return string
     */
    public function deliveryWhere($request){
        /******月执行额数据查询条件***************************************************************/
        $returnWhere=[];
        $returnWhere[]=['isshow','=',1];
        //业务相关条件
        $company_id=$request->get('company_id');//客户方
        $partner_id=$request->get('partner_id');//合作方
        $business_line=$request->get('business_line');//业务线
        $business_type=$request->get('business_type');//业务类型
        $confirm_status=$request->get('confirm_status');//确认函状态
        $business_key=$request->get('business_key');//业务编号
        $businessWhere="";
        if(!empty($company_id) ){
            $businessWhere.=' company_id in ('.$company_id.')';
        }
        if(!empty($partner_id)){
            if(empty($businessWhere)){
                $businessWhere.=' partner_id in ('.$partner_id.')';
            }else{
                $businessWhere.=' or partner_id in ('.$partner_id.')';
            }
        }
        if(!empty($business_line)){
            $business_line_str="";
            foreach($business_line as $k=>$v){
                if(empty($business_line_str)){
                    $business_line_str=' business_line like "%'.$v.'%" ';
                }else{
                    $business_line_str.=' or business_line like "%'.$v.'%" ';
                }
            }
            if(empty($businessWhere)){
                $businessWhere.=' '.$business_line_str;
            }else{
                $businessWhere.=' or '.$business_line_str;
            }
        }
        if(!empty($business_type)){
            $business_type_str="";
            foreach($business_type as $k=>$v){
                if(empty($business_type_str)){
                    $business_type_str=' business_type like "%'.$v.'%" ';
                }else{
                    $business_type_str.=' or business_type like "%'.$v.'%" ';
                }
            }
            if(empty($businessWhere)){
                $businessWhere.=' '.$business_type_str;
            }else{
                $businessWhere.=' or '.$business_type_str;
            }
        }
        if(!empty($confirm_status)){
            if(empty($businessWhere)){
                $businessWhere.=' audit_status in ('.implode(',',$confirm_status).')';
            }else{
                $businessWhere.=' or audit_status in ('.implode(',',$confirm_status).')';
            }
        }
        if(!empty($business_key)){
            if(empty($businessWhere)){
                $businessWhere.=' business_key ="'.$business_key.'"';
            }else{
                $businessWhere.=' or business_key ="'.$business_key.'"';
            }
        }
        //小组相关条件
        $team=$request->get('team');//小组
        $backcash_status=$request->get('backcash_status');//回款状态
        $teamWhere="";
        if(!empty($team)){
            $teamWhere=' team in ('.$this->imArrTpStr($team).')';
        }
        if(!empty($backcash_status)){
            if(empty($teamWhere)){
                $teamWhere.=' backcash_status in ('.implode(',',$backcash_status).')';
            }else{
                $teamWhere.=' or backcash_status in ('.implode(',',$backcash_status).')';
            }
        }
        //合同条件
        $contract_status=$request->get('contract_status');//合同状态
        $contractWhere="";
        if(!empty($contract_status)){
            $contractWhere=' status in ('.implode(',',$contract_status).')';
        }

        //执行月相关条件
        $bmonth=$request->get('bmonth');
        $emonth=$request->get('emonth');
        $deliveryWhere="";
        if(!empty($bmonth) && !empty($emonth)) {
            $deliveryWhere=[$bmonth,$emonth];
        }
        //发票相关条件
        $btime=$request->get('btime');
        $etime=$request->get('etime');
        $invoiceWhere="";
        if(!empty($btime) && !empty($etime)){
            $invoiceWhere='   invoice_time between "'.$btime.'" and "'.$etime.'"';
        }
        //回款相关条件
        $refund_btime=$request->get('refund_btime');
        $refund_etime=$request->get('refund_etime');
        $backcashWhere="";
        if(!empty($refund_btime) && !empty($refund_etime)){
            $backcashWhere='   backtime between "'.$refund_btime.'" and "'.$refund_etime.'"';
        }
        //构建查询
        if(!empty($businessWhere) || !empty($teamWhere) || !empty($contractWhere) || !empty($deliveryWhere ||
                        !empty($invoiceWhere) || !empty($backcashWhere)) ){
            $returnWhere[]=function ($query) use ($businessWhere,$teamWhere,$contractWhere,$deliveryWhere,
                                                $invoiceWhere,$backcashWhere)
            {
                //业务条件
                if(!empty($businessWhere) ) {
                    $query->whereHas('business', function ($query) use ($businessWhere) {
                        $query->whereRaw("(".$businessWhere.")");
                    });
                }
                //小组条件
                if(!empty($teamWhere) && !empty($businessWhere)) {
                    $query->orWhereHas('businessTeam', function ($query) use ($teamWhere) {
                        $query->whereRaw("(".$teamWhere.")");
                    });
                }else if(!empty($teamWhere) && empty($businessWhere)){
                    $query->whereHas('businessTeam', function ($query) use ($teamWhere) {
                        $query->whereRaw("(".$teamWhere.")");
                    });
                }
                //合同条件
                if(!empty($contractWhere) && (!empty($businessWhere) || !empty($teamWhere) )) {
                    $query->orWhereHas('business.contract', function ($query) use ($contractWhere) {
                        $query->whereRaw("(".$contractWhere.")");
                    });
                }else if(!empty($contractWhere) && empty($businessWhere) && empty($teamWhere)){
                    $query->whereHas('business.contract', function ($query) use ($contractWhere) {
                        $query->whereRaw("(".$contractWhere.")");
                    });
                }
                //执行月条件
                if(!empty($deliveryWhere) && (!empty($businessWhere) || !empty($teamWhere) || !empty($contractWhere) ))
                {
                    $query->orWhereBetween('month', $deliveryWhere);
                }else if(!empty($deliveryWhere) && empty($businessWhere) && empty($teamWhere) && empty($contractWhere))
                {
                    $query->whereBetween('month', $deliveryWhere);
                }
                //发票条件
                if(!empty($invoiceWhere) && (!empty($businessWhere) || !empty($teamWhere) || !empty($contractWhere)
                                || !empty($deliveryWhere))) {
                    $query->orWhereHas('invoice_deliveys.invoice', function ($query) use ($invoiceWhere) {
                        $query->whereRaw("(".$invoiceWhere.")");
                    });
                }else if(!empty($invoiceWhere) && empty($businessWhere) && empty($teamWhere) && empty($contractWhere)
                                && empty($deliveryWhere)){
                    $query->whereHas('invoice_deliveys.invoice', function ($query) use ($invoiceWhere) {
                        $query->whereRaw("(".$invoiceWhere.")");
                    });
                }
                //回款条件
                if(!empty($backcashWhere) && (!empty($businessWhere) || !empty($teamWhere) || !empty($contractWhere)
                                || !empty($deliveryWhere) || !empty($invoiceWhere) )) {
                    $query->orWhereHas('invoice_deliveys.backcash_invoices.backcash',
                            function ($query) use ($backcashWhere) {
                                $query->whereRaw("(".$backcashWhere.")");
                    });
                }else if(!empty($backcashWhere) && empty($businessWhere) && empty($teamWhere) && empty($contractWhere)
                        && empty($deliveryWhere) && empty($invoiceWhere) ){
                    $query->whereHas('invoice_deliveys.backcash_invoices.backcash',
                            function ($query) use ($backcashWhere) {
                                $query->whereRaw("(".$backcashWhere.")");
                    });
                }


            };
        }
        return $returnWhere;
    }

    /**
     * 将表单提交的字符串数组转换成sql 中 IN 条件的字符串
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $arr
     * @return string
     */
    public function imArrTpStr($arr){
        if(empty($arr)){return "";}
        $returnStr="";
        foreach($arr as $v){
            if(empty($returnStr)){
                $returnStr='"'.$v.'"';
            }else{
                $returnStr=$returnStr.',"'.$v.'"';
            }
        }
        return $returnStr;
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $request
     * @param int $type  1=表单查询  2=导出数据
     * @return array
     */
    public function getDatas($request,$type=1){
        $viewData=$this->seachFormData();//加载页面查询表单
        $fields=static::$fields;
        $viewData['formfields']=static::$fields;//用于表单中显示字段数据

        //得到已经在表单中勾选的 字段
        $tableFields=[];
        if($request->has('fields')){
            $formFields=$request->get('fields');
            foreach($fields as $k=>$v){
                if(in_array($k,$formFields)){
                    $tableFields[$k]=$v;
                }
            }
        }
        $viewData['tablefields']=$tableFields;//用于列表中显示字段

        //查询表单
        $deliveryWhere=$this->deliveryWhere($request);
        //增加数据权限限制
        $deliveryWhere[]=function ($query) {
            $query->whereHas('business', function ($query) {
                $query->whereRaw(session('company_sql'));
            });
        };
        $deliveryWhere[]=function ($query) {
            $query->whereHas('businessTeam', function ($query) {
                $query->whereRaw(session('team_sql'));
            });
        };
        if(($type==1 && $request->has('submit') && count($tableFields)>2) || ($type==2)) {
            $data = $this->deliveryRepository->with([
                    'business',
                    'businessTeam',
                    'business.partner',
                    'business.company',
                    'business.contract',
                    'expenses_deliveys',//支出
                    'badcash',//坏账
                    'invoice_deliveys',
                    'invoice_deliveys.invoice',
                    'invoice_deliveys.backcash_invoices',
                    'invoice_deliveys.backcash_invoices.backcash',
            ])
                    ->applyWhere(
                            $deliveryWhere
                    )
                    ->applyOrder('business_key')->all();

            $totalArr=[];
            $returnData=[];//用于在页面显示的数据
            //查询小组所属的部门
            $teamDept=$this->dictionaryRepository->getDeptTeam();
            //格式化数据
            $data->map(function ($data) use (&$totalArr, &$returnData,$teamDept) {
                //处理合同相关数据
                $ckey = "";
                $contract_status = "";
                if (isset($data->business->contract->ckey)) {
                    $ckey = $data->business->contract->ckey;
                    $contract_status = $this->businessRepository
                            ->checkContractStatus($data->business->contract_type, $data->business->contract);
                }

                //汇总统计
                /*******小组执行总额汇总****************************************************/
                /*if (isset($totalArr['teamTotal'][$data->business_id . $data->team])) {
                    $totalArr['teamTotal'][$data->business_id . $data->team] = $data->amount +
                            $totalArr['teamTotal'][$data->business_id . $data->team];
                } else {
                    $totalArr['teamTotal'][$data->business_id . $data->team] = $data->amount;
                }*/
                /*******费用支出总额汇总****************************************************/
                if (!$data->expenses_deliveys->isEmpty()) {
                    foreach ($data->expenses_deliveys as $expenses) {
                        if (isset($totalArr['expensesTotal'][$data->id])) {
                            $totalArr['expensesTotal'][$data->id] = $expenses->active_amount +
                                    $totalArr['expensesTotal'][$data->id];
                        } else {
                            $totalArr['expensesTotal'][$data->id] = $expenses->active_amount;
                        }
                    }
                }
                /**********坏账总额汇总****************************************************/
                $badcash_time = '';//月坏账录入时间
                if (!$data->badcash->isEmpty()) {
                    foreach ($data->badcash as $badcash) {
                        $badcash_time = $badcash->created_at;
                        if (isset($totalArr['badcashTotal'][$data->id])) {
                            $totalArr['badcashTotal'][$data->id] = $badcash->amount +
                                    $totalArr['badcashTotal'][$data->id];
                        } else {
                            $totalArr['badcashTotal'][$data->id] = $badcash->amount;
                        }
                    }
                }
                /**************end************************************************************/
                $mainArr = [
                    //业务相关数据
                        'business_id' => $data->business_id,
                        'delivery_id' => $data->id,
                        'business_key' => $data->business_key,
                        'company_type' => $data->business->partner->companytype_text,
                        'partner' => $data->business->partner->company_name,
                        'company' => $data->business->company->company_name,
                        'product_type' => $data->business->product->type,
                        'product_name' => $data->business->product->name,
                        'business_line' => $data->business->business_line,
                        'business_type' => $data->business->business_type,
                        'btime' => $data->business->btime,
                        'etime' => $data->business->etime,
                        'paytime' => $data->business->paytime,
                        'contract_key' => $ckey,
                        'contract_status' => $contract_status,
                        'confirm_status' => $data->business->confirmstatus_text,
                        'zq_days' => $this->diffBetweenTwoDays($data->business->etime, $data->business->paytime),
                    //执行小组相关数据
                        'team' => $teamDept[$data->team],
                        'team_amount_total' => $data->businessTeam->active_amount,
                    //执行月相关数据
                        'team_month' => $data->month,
                        'team_month_amount' => $data->amount,
                        'team_month_invoice' => 0.00,
                        'team_month_backcash' => 0.00,
                        'team_month_expenses' => 0.00,
                        'team_month_badcash' => 0.00,
                        'badcash_time' => $badcash_time,
                        'yszk_amount' => 0.00,
                    //发票明细
                        'invoice_amount' => 0.00,
                        'invoice_type' => "",
                        'invoice_key' => "",
                        'invoice_time' => "",
                    //回款明细
                        'backcash_amount' => 0.00,
                        'backcash_bank' => "",
                        'backcash_type' => "",
                        'backcash_time' => "",
                        'backcash_days' => "",
                ];
                //发票明细相关
                //$invoiceTotal=0.00;
                if (!$data->invoice_deliveys->isEmpty()) {
                    foreach ($data->invoice_deliveys as $invoice) {
                        $mainArr['invoice_amount'] = $invoice->active_amount;
                        $mainArr['invoice_type'] = $invoice->invoice->invoice_type;
                        $mainArr['invoice_key'] = $invoice->invoice->invoice_key;
                        $mainArr['invoice_time'] = $invoice->invoice->invoice_time;

                        //月发票总额汇总
                        if (isset($totalArr['invoiceTotal'][$data->id])) {
                            $totalArr['invoiceTotal'][$data->id] = $invoice->active_amount +
                                    $totalArr['invoiceTotal'][$data->id];
                        } else {
                            $totalArr['invoiceTotal'][$data->id] = $invoice->active_amount;
                        }

                        if (!$invoice->backcash_invoices->isEmpty()) {
                            foreach ($invoice->backcash_invoices as $backcash) {
                                $mainArr['backcash_amount'] = $backcash->active_amount;
                                $mainArr['backcash_bank'] = $backcash->backcash->bank;
                                $mainArr['backcash_type'] = $backcash->backcash->backtype;
                                $mainArr['backcash_time'] = $backcash->backcash->backtime;
                                $mainArr['backcash_days'] = $this->diffBetweenTwoDays($data->business->etime,
                                        $backcash->backcash->backtime);

                                //月回款总额汇总
                                if (isset($totalArr['backcashTotal'][$data->id])) {
                                    $totalArr['backcashTotal'][$data->id] = $backcash->active_amount +
                                            $totalArr['backcashTotal'][$data->id];
                                } else {
                                    $totalArr['backcashTotal'][$data->id] = $backcash->active_amount;
                                }

                                $returnData[] = $mainArr;
                            }
                        } else {
                            $returnData[] = $mainArr;
                        }
                    }//end foreanch
                } else {
                    $returnData[] = $mainArr;
                }
            });
            $viewData['list']=$returnData;
            $viewData['total']=$totalArr;
        }
        return $viewData;
    }

    /**
     * 财务统计基础 统计列表
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function financialBase(Request $request){
        $viewData=$this->getDatas($request,1);
        return view('admin.statistic.financial-base',$viewData);
    }

    public function financialBaseExcel(Request $request, ExcelMaker $excelMaker){
        $viewData=$this->getDatas($request,2);
        $fields=static::$fields;
        //增加两个固定字段
        $headers['business_key']="业务编号";
        $headers['team']="执行小组";
        foreach($fields as $k=>$v){
            $headers[$k]=$v;
        }

        $data=[];
        if(!empty($viewData['list'])){
            $total=$viewData['total'];
            //组织数据
            foreach($viewData['list'] as $v){
                $team_month_invoice=0.00;
                $team_month_backcash=0.00;
                $team_month_expenses=0.00;
                $team_month_badcash=0.00;
                $yszk_amount=0.00;

                if(isset($total['invoiceTotal'][$v['delivery_id']])){
                    $team_amount_total=number_format($total['invoiceTotal'][$v['delivery_id']],2);
                }
                if(isset($total['backcashTotal'][$v['delivery_id']])){
                    $team_month_backcash=number_format($total['backcashTotal'][$v['delivery_id']],2);
                }
                if(isset($total['expensesTotal'][$v['delivery_id']])){
                    $team_month_expenses=number_format($total['expensesTotal'][$v['delivery_id']],2);
                }
                if(isset($total['badcashTotal'][$v['delivery_id']])){
                    $team_month_badcash=number_format($total['badcashTotal'][$v['delivery_id']],2);
                }

                $yszk_amount=$v['team_month_amount']-$team_month_expenses-$team_month_backcash-$team_month_badcash;

                $data[] = [
                        //业务相关数据
                        'business_key' => $v['business_key'],
                        'team' => $v['team'],
                        'company_type' => $v['company_type'],
                        'partner' => $v['partner'],
                        'company' => $v['company'],
                        'product_type' => $v['product_type'],
                        'product_name' => $v['product_name'],
                        'business_line' => $v['business_line'],
                        'business_type' => $v['business_type'],
                        'btime' => $v['btime'],
                        'etime' => $v['etime'],
                        'paytime' => $v['paytime'],
                        'contract_key' => $v['contract_key'],
                        'contract_status' => $v['contract_status'],
                        'confirm_status' => $v['confirm_status'],
                        'zq_days' => $v['zq_days'],
                        //执行小组相关数据
                        'team_amount_total' => $v['team_amount_total'],
                        //执行月相关数据
                        'team_month' => $v['team_month'],
                        'team_month_amount' => $v['team_month_amount'],
                        'team_month_invoice' => $team_month_invoice,
                        'team_month_backcash' => $team_month_backcash,
                        'team_month_expenses' => $team_month_expenses,
                        'team_month_badcash' => $team_month_badcash,
                        'badcash_time' => $v['badcash_time'],
                        'yszk_amount' =>$yszk_amount,
                        //发票明细
                        'invoice_amount' => $v['invoice_amount'],
                        'invoice_type' => $v['invoice_type'],
                        'invoice_key' => $v['invoice_key'],
                        'invoice_time' => $v['invoice_time'],
                        //回款明细
                        'backcash_amount' => $v['backcash_amount'],
                        'backcash_bank' => $v['backcash_bank'],
                        'backcash_type' => $v['backcash_type'],
                        'backcash_time' => $v['backcash_time'],
                        'backcash_days' => $v['backcash_days'],
                ];
            }
        }
        $excel = $excelMaker->makeExcel($headers, $data);
        $excel->download('xls');
    }

    /**
     * 求两个日期之间相差的天数
     * (针对1970年1月1日之后)
     * @param string $day1
     * @param string $day2
     * @return number
     */
    public function diffBetweenTwoDays ($day1, $day2)
    {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);
        if ($second1 < $second2) {
            $tmp = $second2;
            $second2 = $second1;
            $second1 = $tmp;
        }
        return ($second1 - $second2) / 86400;
    }


}
