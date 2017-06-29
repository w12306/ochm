<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Controller;
use App\Models\BusinessModel;
use App\Models\Contract;
use App\Models\DeliveryModel;
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

    protected $deliveryModel;
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
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                DeliveryModel $deliveryModel) {
        $this->deliveryRepository=$deliveryRepository;
        $this->companyRepository=$companyRepository;
        $this->partnersRepository=$partnersRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->businessTeamRepository=$businessTeamRepository;
        $this->expensesDeliveyRepository=$expensesDeliveyRepository;
        $this->underPartnersRepository=$underPartnersRepository;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
        $this->deliveryModel=$deliveryModel;
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
        return $viewData;
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
        $deliveryWhere="";
        $returnWhere=[];
        $returnWhere[]=['isshow','=',1];
        $year=$request->get('year');
        if(!empty($year)){
            $deliveryWhere.=' month like "'.$year.'%"';
        }
        //执行月相关条件
        $bmonth=$request->get('bmonth');
        $emonth=$request->get('emonth');
        $monthWhere="";
        if(!empty($bmonth) && !empty($emonth)){
            $monthWhere=' and  month between "'.$bmonth.'" and "'.$emonth.'"';
        }
        $deliveryWhere.=$monthWhere;
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
        //业务相关条件
        $company_id=$request->get('company_id');
        $partner_id=$request->get('partner_id');
        $businessWhere="";
        if(!empty($company_id) ){
            $businessWhere=' company_id in ('.$company_id.')';
        }
        if(!empty($partner_id)){
            if(empty($businessWhere)){
                $businessWhere.=' partner_id in ('.$partner_id.')';
            }else{
                $businessWhere.=' or partner_id in ('.$partner_id.')';
            }
        }
        //构建查询
        if(!empty($deliveryWhere)){
            $returnWhere[]=function ($query) use ($deliveryWhere,$monthWhere,$invoiceWhere,$backcashWhere,
                    $businessWhere)
            {
                //执行月条件
                $query->whereRaw($deliveryWhere);
                //发票条件
                if(!empty($invoiceWhere) && !empty($monthWhere)) {
                    $query->orWhereHas('invoice_deliveys.invoice', function ($query) use ($invoiceWhere) {
                        $query->whereRaw($invoiceWhere);
                    });
                }else if(!empty($invoiceWhere) && empty($monthWhere)){
                    $query->whereHas('invoice_deliveys.invoice', function ($query) use ($invoiceWhere) {
                        $query->whereRaw($invoiceWhere);
                    });
                }
                //回款条件
                if(!empty($backcashWhere) && (!empty($monthWhere) || !empty($invoiceWhere) )) {
                    $query->orWhereHas('invoice_deliveys.backcash_invoices.backcash',
                            function ($query) use ($backcashWhere) {
                                $query->whereRaw($backcashWhere);
                    });
                }else if(!empty($backcashWhere) && empty($monthWhere) && empty($invoiceWhere) ){
                    $query->whereHas('invoice_deliveys.backcash_invoices.backcash',
                            function ($query) use ($backcashWhere) {
                                 $query->whereRaw($backcashWhere);
                    });
                }
                //业务条件
                if(!empty($businessWhere) &&  (!empty($monthWhere) || !empty($invoiceWhere) || !empty($backcashWhere)))
                {
                    $query->orWhereHas('business', function ($query) use ($businessWhere) {
                        $query->whereRaw($businessWhere);
                    });
                }else if(!empty($businessWhere) && empty($monthWhere) && empty($invoiceWhere) && empty($backcashWhere))
                {
                    $query->whereHas('business', function ($query) use ($businessWhere) {
                        $query->whereRaw($businessWhere);
                    });
                }
            };
        }
        return $returnWhere;
    }

    /**
     * 财务年度基础统计 页面
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param Request $request
     * @param string $year
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function financialYearBase(Request $request,$year=""){
        $viewData=$this->seachFormData();//加载页面查询表单
        //查询执行月中有哪些年份
        if(empty($year)){
            $year=date("Y",time());
        }
        $viewData['year']=$year;
        $viewData['years']=$this->deliveryRepository->getDeliveryYears();
        return view('admin.statistic.financial-year-base',$viewData);
    }

    public function getDatas($request){
        $where=$this->deliveryWhere($request);

        //增加数据权限限制
        $where[]=function ($query) {
            $query->whereHas('business', function ($query) {
                $query->whereRaw(session('company_sql'));
            });
        };
        $where[]=function ($query) {
            $query->whereHas('businessTeam', function ($query) {
                $query->whereRaw(session('team_sql'));
            });
        };

        $data=$this->deliveryRepository->with([
                'business',
                'businessTeam',
                'business.partner',
                'business.contract',
                'invoice_deliveys',
                'invoice_deliveys.invoice',
                'invoice_deliveys.backcash_invoices',
                'invoice_deliveys.backcash_invoices.backcash',
        ])
        ->applyWhere($where)
        ->applyOrder('business_key','desc')->all();
        return $data;
    }

    /*******以下是数据API**************************************************************************************************/

    public function financialYearBaseApi(Request $request){
        $data=$this->getDatas($request);
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

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $data->map(function ($d) use (&$table,$teamDept) {
            $values['business_key']=$d->business_key;
            $values['partner_id']=$d->business->partner->company_name;
            $values['contract_key']=empty($d->business->contract->ckey)?"":$d->business->contract->ckey;
            $values['team']=$teamDept[$d->team];
            $values['team_month']=$d->month;
            $values['team_amount']=$d->amount;
            $values['invoice_amount']="";
            $values['invoice_key']="";
            $values['invoice_type']="";
            $values['invoice_time']="";
            $values['backcash_active_amount']="";
            $values['backcash_time']="";
            $values['backcash_bank']="";
            $values['backcash_type']="";

            $keys['business_key']=$d->business_key;
            $keys['partner_id']=$d->business_key;
            $keys['contract_key']=$d->business_key;
            $keys['team']=$d->id;
            $keys['team_month']=$d->id;
            $keys['team_amount']=$d->id;
            $keys['invoice_amount']=$d->id;
            $keys['invoice_key']=$d->id;
            $keys['invoice_type']=$d->id;
            $keys['invoice_time']=$d->id;
            $keys['backcash_active_amount']=$d->id;
            $keys['backcash_time']=$d->id;
            $keys['backcash_bank']=$d->id;
            $keys['backcash_type']=$d->id;

            if(!$d->invoice_deliveys->isEmpty()){
                foreach($d['invoice_deliveys'] as $i){
                    $values['invoice_amount']=$i->active_amount;
                    $values['invoice_key']=$i->invoice_key;
                    $values['invoice_type']=$i->invoice->invoice_type;
                    $values['invoice_time']=$i->invoice->invoice_time;

                    $keys['invoice_amount']=$i->id;
                    $keys['invoice_key']=$i->id;
                    $keys['invoice_type']=$i->id;
                    $keys['invoice_time']=$i->id;

                    if(!$i->backcash_invoices->isEmpty()){
                        foreach($i['backcash_invoices'] as $b){
                            $values['backcash_active_amount']=$b->active_amount;
                            $values['backcash_time']=$b->backcash->backtime;
                            $values['backcash_bank']=$b->backcash->bank;
                            $values['backcash_type']=$b->backcash->backtype;

                            $keys['backcash_active_amount']=$b->id;
                            $keys['backcash_time']=$b->id;
                            $keys['backcash_bank']=$b->id;
                            $keys['backcash_type']=$b->id;
                            $table[]=$this->madeRows($values,$keys);
                        }
                    }else{
                        $table[]=$this->madeRows($values,$keys);
                    }
                }
            }else{
                $table[]=$this->madeRows($values,$keys);
            }
        });
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,'table' =>$table,] ];
        return json_encode($returnJsonData);
    }

    /**
     *构建一行返回的数据格式
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $values数据值
     * @param $keys 数据表格的key
     * @return array
     */
    function madeRows($values,$keys){
        $table=[
                "business_key"=>["key"=>$keys['business_key'],"value"=>$values['business_key']],
                "partner_id" =>["key"=>$keys['partner_id'],"value"=>$values['partner_id']],
                "contract_key"=>["key"=>$keys['contract_key'],"value"=>$values['contract_key']],
                "team"=>["key"=>$keys['team'],"value"=>$values['team']],
                "team_month"=>["key"=>$keys['team_month'],"value"=>$values['team_month']],
                "team_amount"=>["key"=>$keys['team_amount'],"value"=>$values['team_amount']],
                "invoice_amount"=>["key"=>$keys['invoice_amount'],"value"=>$values['invoice_amount']],
                "invoice_key"=>["key"=>$keys['invoice_key'],"value"=>$values['invoice_key']],
                "invoice_type"=>["key"=>$keys['invoice_type'],"value"=>$values['invoice_type']],
                "invoice_time"=>["key"=>$keys['invoice_time'],"value"=>$values['invoice_time']],
                "backcash_active_amount"=>["key"=>$keys['backcash_active_amount'],"value"=>$values['backcash_active_amount']],
                "backcash_time"=>["key"=>$keys['backcash_time'],"value"=>$values['backcash_time']],
                "backcash_bank"=>["key"=>$keys['backcash_bank'],"value"=>$values['backcash_bank']],
                "backcash_type"=>["key"=>$keys['backcash_type'],"value"=>$values['backcash_type']],
        ];
        return $table;
    }

    public function financialYearBaseExcel(Request $request, ExcelMaker $excelMaker){
        $headers=[
                '业务编号',
                '合作方',
                '合作方类型',
                '产品名称',
                '产品类型',
                '业务线',
                '合同编号',
                '合同状态',
                '确认函状态',
                '执行开始时间',
                '执行结束时间',
                '约定回款时间',
                '执行小组',
                '执行总金额',
                '执行月份',
                '月执行金额',
                '发票金额',
                '发票编号',
                '发票类型',
                '开票日期',
                '回款金额',
                '回款日期',
                '回款银行',
                '回款方式',
                ];
        //产品类型、合同状态、确认函状态
        $data=$this->getDatas($request);

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();

        $returnData=[];
        $data->map(function($d) use(&$returnData,$teamDept){
                $value=[
                        'business_key'=>$d->business_key,
                        'partner_name'=>$d->business->partner->company_name,
                        'partner_type'=>$d->business->partner->companytype_text,
                        'product_name'=>$d->business->product->name,
                        'product_type'=>$d->business->product->type,
                        'business_line'=>$d->business->business_line,
                        'ckey'=>empty($d->business->contract->ckey)?"":$d->business->contract->ckey,
                        'c_status'=>empty($d->business->contract->status)?"":$d->business->contract->status_text,
                        'confirmstatus'=>$d->business->confirm_status_text,
                        'btime'=>$d->business->btime,
                        'etime'=>$d->business->etime,
                        'paytime'=>$d->business->paytime,
                        'team'=>$teamDept[$d->team],
                        'team_active_amount'=>$d->businessTeam->active_amount,
                        'month'=>$d->month,
                        'month_amount'=>$d->amount,
                        'invoice_amount'=>0.00,
                        'invoice_key'=>'',
                        'invoice_type'=>'',
                        'invoice_time'=>'',
                        'backcash_active_amount'=>0.00,
                        'backcash_time'=>'',
                        'backcash_bank'=>'',
                        'backcash_type'=>''
                        ];
            if(!$d->invoice_deliveys->isEmpty()){
                foreach($d['invoice_deliveys'] as $i){
                    $value['invoice_amount']=$i->active_amount;
                    $value['invoice_key']=$i->invoice_key;
                    $value['invoice_type']=$i->invoice->invoice_type;
                    $value['invoice_time']=$i->invoice->invoice_time;
                    if(!$i->backcash_invoices->isEmpty()){
                        foreach($i['backcash_invoices'] as $b){
                            $value['backcash_active_amount']=$b->active_amount;
                            $value['backcash_time']=$b->backcash->backtime;
                            $value['backcash_bank']=$b->backcash->bank;
                            $value['backcash_type']=$b->backcash->backtype;
                            $returnData[]=$value;
                        }
                    }else{
                        $returnData[]=$value;
                    }
                }
            }else{
                $returnData[]=$value;
            }
        });
        $excel = $excelMaker->makeExcel($headers, $returnData);
        $excel->download('xls');
    }



}
