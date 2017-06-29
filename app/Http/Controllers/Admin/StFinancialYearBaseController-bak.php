<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\BusinessModel;
use App\Models\Contract;
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
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class StFinancialYearBaseController extends Controller {
    use CommonResponse;

    protected $deliveryRepository;

    protected $companyRepository;

    protected $partnersRepository;

    protected $dictionaryRepository;

    protected $businessTeamRepository;

    protected $expensesDeliveyRepository;

    protected $underPartnersRepository;

    protected $backcashInvoiceRepository;
    /**
     *
     */
    public function __construct(DeliveryRepository $deliveryRepository,
                                CompanyRepository $companyRepository,
                                PartnersRepository $partnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                ExpensesDeliveyRepository $expensesDeliveyRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                BackcashInvoiceRepository $backcashInvoiceRepository) {
        $this->deliveryRepository=$deliveryRepository;
        $this->companyRepository=$companyRepository;
        $this->partnersRepository=$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->businessTeamRepository=$businessTeamRepository;
        $this->expensesDeliveyRepository=$expensesDeliveyRepository;
        $this->underPartnersRepository=$underPartnersRepository;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
    }

    /**
     *组织 统计页面 顶部查询表单基础数据
     * @author YangWei<yangwei@foxmail.com>
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
        $viewData['team']=$this->dictionaryRepository->returnTypeArray('team');

        return $viewData;
    }

    /**
     * 查询页面 提交查询条件
     *
     * @author YangWei<yangwei@foxmail.com>
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
        return $businessWhere;
    }

    public function businessTeamWhere($request){
        /******小组表查询条件***************************************************************/
        $teamWhere='';
        if ($request->has('team')) {
            $team=$request->get('team');
            $team_str='';
            foreach($team as $k=>$v){
                if(empty($team_str)){
                    $team_str='"'.$v.'"';
                }else{
                    $team_str.=$team_str.',"'.$v.'"';;
                }
            }
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
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $request
     * @return string
     */
    public function deliveryWhere($request){
        /******月执行额数据查询条件***************************************************************/
        $deliveryWhere='';
        $bmonth=$request->get('bmonth');
        $emonth=$request->get('emonth');
        if(!empty($bmonth) && !empty($emonth)){
            if(!empty($deliveryWhere)){$deliveryWhere.=' or ';}
            $deliveryWhere.=' month between "'.$bmonth.'" and "'.$emonth.'"';
        }

        if(empty($deliveryWhere)){$deliveryWhere=' 1=1 ';}
        return $deliveryWhere;
    }

    /**
     *下游合作方查询条件
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $request
     * @return string
     */
    public function underPartnerWhere($request){
        /******月执行额数据查询条件***************************************************************/
        $Where='';
        //下游合作方
        if ($request->has('underpartner_id')) {
            $underpartner_id = $request->get('underpartner_id');
            if (!empty($where)) {
                $where .= ' or ';
            }
            $where .= '  under_partner_id in (' . $underpartner_id . ')';
        }

        if(empty($Where)){$Where=' 1=1 ';}
        return $Where;
    }

    /**
     *合同表数据查询条件
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $request
     * @return string
     */
    public function contractWhere($request){
        /******合同表数据查询条件***************************************************************/
        $contractWhere='';
        $contract_type=$request->get('contract_type');
        if(!empty($contract_type)){
            if(!empty($contractWhere)){$contractWhere.=' or ';}
            $contractWhere.=' `status` in ( '.implode(',',$contract_type).')';
        }
        if(empty($contractWhere)){$contractWhere=' 1=1 ';}
        return $contractWhere;
    }




    public function financialYearBase(Request $request){
        $viewData=$this->seachFormData();//加载页面查询表单




        return view('admin.statistic.financial-year-base',$viewData);
    }




    /*******以下是数据API**************************************************************************************************/

    public function financialYearBaseApi(Request $request){
        $data=$this->deliveryRepository->with([
                'business',
                'business.partner',
                'business.contract',
                'invoice_deliveys',
                'invoice_deliveys.invoice',
                'invoice_deliveys.backcash_invoices',
                'invoice_deliveys.backcash_invoices.backcash',
        ])
                ->applyOrder('business_key')
        ->all()->toArray();

        $title=[
                ["name"=> '业务编号' ,"key" => "business_key", "type" => 1],
                ["name"=> '合作方' ,"key" => "partner_id", "type" => 1],
                ["name"=> '合同编号' ,"key" => "contract_key", "type" => 1],
                ["name"=> '执行小组' ,"key" => "team", "type" => 1],
                ["name"=> '执行月份' ,"key" => "team_month", "type" => 1],
                ["name"=> '月执行金额（元）' ,"key" => "team_amount", "type" => 1],
                ["name"=> '发票金额（元）' ,"key" => "invoice_amount", "type" => 1],
                ["name"=> '发票编号' ,"key" => "invoice_key", "type" => 1],
                ["name"=> '发票类型' ,"key" => "invoice_type", "type" => 1],
                ["name"=> '开票日期' ,"key" => "invoice_time", "type" => 1],
                ["name"=> '回款金额' ,"key" => "backcash_active_amount", "type" => 1],
                ["name"=> '回款日期' ,"key" => "backcash_time", "type" => 1],
                ["name"=> '回款银行' ,"key" => "backcash_bank", "type" => 1],
                ["name"=> '回款方式' ,"key" => "backcash_type", "type" => 1],

        ];
        $table=[];
        foreach($data as $index=> $d){
            $values['business_key']=$d['business_key'];
            $values['partner_id']=$d['business_key'];
            $values['contract_key']=$d['business_key'];
            $values['team']=$d['business_key'];
            $values['team_month']=$d['business_key'];
            $values['team_amount']=$d['business_key'];
            $values['invoice_amount']=$d['business_key'];
            $values['invoice_key']=$d['business_key'];
            $values['invoice_type']=$d['business_key'];
            $values['invoice_time']=$d['business_key'];
            $values['backcash_active_amount']=$d['business_key'];
            $values['backcash_time']=$d['business_key'];
            $values['backcash_bank']=$d['business_key'];
            $values['backcash_type']=$d['business_key'];

           /* $business_key=$d['business_key'];
            $partner_id=$d['id'].$d['business']['partner']['company_name'];
            $contract_key=$d['business']['contract']['ckey'];
            $team=$d['team'];
            $team_month=$d['month'];
            $team_amount=$d['amount'];

            $invoice_id="";
            $invoice_amount="";
            $invoice_key="";
            $invoice_type="";
            $invoice_time="";

            $backcash_id="";
            $backcash_active_amount="";
            $backcash_time="";
            $backcash_bank="";
            $backcash_type="";*/

            if(!empty($d['invoice_deliveys'])){
                foreach($d['invoice_deliveys'] as $i){
                    $invoice_id=$i['id'];
                    $invoice_amount=$i['active_amount'];
                    $invoice_key=$i['invoice_key'];
                    $invoice_type=$i['invoice']['invoice_type'];
                    $invoice_time=$i['invoice']['invoice_time'];
                    if(!empty($i['backcash_invoices'])){
                        foreach($i['backcash_invoices'] as $b){
                            $backcash_id=$b['id'];
                            $backcash_active_amount=$b['active_amount'];
                            $backcash_time=$b['backcash']['backtime'];
                            $backcash_bank=$b['backcash']['bank'];
                            $backcash_type=$b['backcash']['backtype'];
                            $table[]=[
                                    "business_key"=>["key"=>$business_key,"value"=>$business_key],
                                    "partner_id" =>["key"=>$business_key,"value"=>$partner_id],
                                    "contract_key"=>["key"=>$business_key,"value"=>$contract_key],
                                    "team"=>["key"=>$d['id'].$team,"value"=>$team],
                                    "team_month"=>["key"=>$d['id'].$team,"value"=>$team_month],
                                    "team_amount"=>["key"=>$d['id'].$team,"value"=>$team_amount],
                                    "invoice_amount"=>["key"=>$invoice_id,"value"=>$invoice_amount],
                                    "invoice_key"=>["key"=>$invoice_id,"value"=>$invoice_key],
                                    "invoice_type"=>["key"=>$invoice_id,"value"=>$invoice_type],
                                    "invoice_time"=>["key"=>$invoice_id,"value"=>$invoice_time],
                                    "backcash_active_amount"=>["key"=>"","value"=>$backcash_active_amount],
                                    "backcash_time"=>["key"=>$backcash_id,"value"=>$backcash_time],
                                    "backcash_bank"=>["key"=>$backcash_id,"value"=>$backcash_bank],
                                    "backcash_type"=>["key"=>$backcash_id,"value"=>$backcash_type],
                            ];
                        }
                    }else{

                        $table[]=[
                                "business_key"=>["key"=>$business_key,"value"=>$business_key],
                                "partner_id" =>["key"=>$business_key,"value"=>$partner_id],
                                "contract_key"=>["key"=>$business_key,"value"=>$contract_key],
                                "team"=>["key"=>$d['id'].$team,"value"=>$team],
                                "team_month"=>["key"=>$d['id'].$team,"value"=>$team_month],
                                "team_amount"=>["key"=>$d['id'].$team,"value"=>$team_amount],
                                "invoice_amount"=>["key"=>$invoice_id,"value"=>$invoice_amount],
                                "invoice_key"=>["key"=>$invoice_id,"value"=>$invoice_key],
                                "invoice_type"=>["key"=>$invoice_id,"value"=>$invoice_type],
                                "invoice_time"=>["key"=>$invoice_id,"value"=>$invoice_time],
                                "backcash_active_amount"=>["key"=>'i_'.$i['id'],"value"=>""],
                                "backcash_time"=>["key"=>'i_'.$i['id'],"value"=>""],
                                "backcash_bank"=>["key"=>'i_'.$i['id'],"value"=>""],
                                "backcash_type"=>["key"=>'i_'.$i['id'],"value"=>""],
                        ];
                    }
                }

            }else{
                //dd($d->toarray());
                $table[]=[
                        "business_key"=>["key"=>$business_key,"value"=>$business_key],
                        "partner_id" =>["key"=>$business_key,"value"=>$partner_id],
                        "contract_key"=>["key"=>$business_key,"value"=>$contract_key],
                        "team"=>["key"=>$d['id'].$team,"value"=>$team],
                        "team_month"=>["key"=>$d['id'].$team,"value"=>$team_month],
                        "team_amount"=>["key"=>$d['id'].$team,"value"=>$team_amount],
                        "invoice_amount"=>["key"=>$d['id'],"value"=>""],
                        "invoice_key"=>["key"=>$d['id'],"value"=>""],
                        "invoice_type"=>["key"=>$d['id'],"value"=>""],
                        "invoice_time"=>["key"=>$d['id'],"value"=>""],
                        "backcash_active_amount"=>["key"=>$d['id'],"value"=>""],
                        "backcash_time"=>["key"=>$d['id'],"value"=>""],
                        "backcash_bank"=>["key"=>$d['id'],"value"=>""],
                        "backcash_type"=>["key"=>$d['id'],"value"=>""],
                ];
                continue;
            }


        }
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$table,

        ]
        ];
        return json_encode($returnJsonData);
    }


    function madeRows($values,$keys,&$table){
        foreach($table as $k=>$v){
            $table[]=[
                    $v['key']=>[
                            'key'=>$keys[$v['key']],
                            'value'=>$values[$v['key']]
                    ]
            ];
        }
        /*$table[]=[
                "business_key"=>["key"=>$business_key,"value"=>$business_key],
                "partner_id" =>["key"=>$business_key,"value"=>$partner_id],
                "contract_key"=>["key"=>$business_key,"value"=>$contract_key],
                "team"=>["key"=>$d['id'].$team,"value"=>$team],
                "team_month"=>["key"=>$d['id'].$team,"value"=>$team_month],
                "team_amount"=>["key"=>$d['id'].$team,"value"=>$team_amount],
                "invoice_amount"=>["key"=>$d['id'],"value"=>""],
                "invoice_key"=>["key"=>$d['id'],"value"=>""],
                "invoice_type"=>["key"=>$d['id'],"value"=>""],
                "invoice_time"=>["key"=>$d['id'],"value"=>""],
                "backcash_active_amount"=>["key"=>$d['id'],"value"=>""],
                "backcash_time"=>["key"=>$d['id'],"value"=>""],
                "backcash_bank"=>["key"=>$d['id'],"value"=>""],
                "backcash_type"=>["key"=>$d['id'],"value"=>""],
        ];*/

    }


    /**
     *计算某个执行月对应的 关联数据总和
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function sumAmount($datas=""){
        $active_amount = 0.00;
        if(!empty($datas)) {
            foreach ($datas as $k => $v) {
                if(!isset($v['amount'])) {
                    $active_amount = $active_amount + $v['active_amount'];
                }else{
                    $active_amount = $active_amount + $v['amount'];//处理坏账总和数据
                }
            }
        }
        return $active_amount;
    }


}
