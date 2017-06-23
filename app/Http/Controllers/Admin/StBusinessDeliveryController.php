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
 * @author  YangweiWei <yangwei@stnts.com>
 */
class StBusinessDeliveryController extends Controller {
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
                    $business_type_str = $business_type_str .',"'. $v . '"';
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
        return $teamWhere.' and  ' . session('team_sql') ;
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
     * @author YangWei<yangwei@stnts.com>
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
     * @author YangWei<yangwei@stnts.com>
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


    /**
     *业务执行金额数据统计列表
     * @author YangWei<yangwei@stnts.com>
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function busineseDelivery(Request $request){
        $viewData=$this->seachFormData();//加载页面查询表单

        $datas=$this->getBusinessDeliveryDatas($request);
        $viewData['listdata']   = $datas['rows'];
        $viewData['total']      = $datas['total'];

        return view('admin.statistic.business-delivery',$viewData);
    }
    /**
     *业务执行金额数据导出
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function busineseDeliveryExcel(Request $request, ExcelMaker $excelMaker){
        //表头
        $headers = [
                '业务编号',
                '执行小组',
                '执行月份',
                '合作方',
                '合作方类型',
                '客户名称',
                '业务线',
                '业务类型',
                '产品名称',
                '产品类型',
                '合同编号',
                '执行开始时间',
                '执行结束时间',
                '约定结款日期',
                '实际金额拆分',
                '执行金额',
                '支出总和',
                '分成金额总和',
                '费用金额总和',
                '渠道费用金额总和',
                '发票总和',
                '回款总和',
                '坏账金额',
                '利润',
                '执行应收',
                '合同状态',
                '确认函状态',
                '回款状态',
                '发票状态',
        ];

        $data=$this->getBusinessDeliveryDatas($request);
        $excel = $excelMaker->makeExcel($headers, $data['rows']);
        $excel->download('xls');
    }




    /*******以下是数据API**************************************************************************************************/


    /**
     *业务执行金额数据计算
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $request
     * @return array
     */
    public function getBusinessDeliveryDatas($request){
        $businessWhere=$this->businessWhere($request);
        $businessTeamWhere=$this->businessTeamWhere($request);
        $deliveryWhere=$this->deliveryWhere($request);

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $listdata = $this->deliveryRepository->with([
                        'business',
                        'business.partner',
                        'business.company',
                        'business.product',
                        'business.contract',
                        'expenses_deliveys',
                        'expenses_deliveys.expenses',
                        'businessTeam',
                        'invoice_deliveys',
                        'backcash_invoices',
                        'badcash',
                ]
        )->applyWhere([
                function ($query) use ($businessWhere) {
                    $query->whereHas('business', function ($query) use ($businessWhere) {
                        $query->whereRaw('('.$businessWhere.')');
                    });
                },
                function ($query) use ($businessTeamWhere) {
                    $query->whereHas('businessTeam', function ($query) use ($businessTeamWhere) {
                        $query->whereRaw('('.$businessTeamWhere.')');
                    });
                },
                function ($query) use ($deliveryWhere) {
                        $query->whereRaw('('.$deliveryWhere.')');
                },
                ['isshow','=',1]
        ])->all();


        $month_amount_total=0.00;//月执行额合计
        $expenses_amount_total=0.00;//支出总和合计
        $team_amount_set=[];//用户判断某个小组的实际执行金额是否已经显示过 如已经显示过则显示“--”
        $team_amount_total=0.00;//小组实际执行金额合计
        $invoice_amount_total=0.00;//发票总和
        $backcash_amount_total=0.00;//回款总和
        $badcash_amount_total=0.00;//坏账总和合计
        $profit_total=0.00;//月利润  合计
        $income_total=0.00;//月应收 合计

        //格式化数据
        $rows=$listdata->map(function ($data) use (&$month_amount_total,&$expenses_amount_total,
                &$team_amount_total,&$team_amount_set,&$invoice_amount_total,
                &$backcash_amount_total,&$badcash_amount_total,&$profit_total,
                &$income_total,$teamDept) {
            //小组实际执行额(需要格式化)
            $team_amount =0.00;
            $key=$data['business']['business_key'].$data['team'];
            if(isset($team_amount_set[$key])){
                $team_amount='--';
            }else{
                $team_amount=$data['businessTeam']['active_amount'];
                $team_amount_total=$team_amount_total+$team_amount;
                $team_amount_set[$key]=$team_amount;
            }

            //月执行额合计
            $month_amount_total=$month_amount_total+$data['amount'];

            //计算支出总和
            $expenses_active_amount=$this->sumAmount($data['expenses_deliveys']);
            $expenses_amount_total=$expenses_amount_total+$expenses_active_amount;
            //支出的三个类型统计
            $expenses_amount_1=0.00;
            $expenses_amount_2=0.00;
            $expenses_amount_3=0.00;
            foreach ($data['expenses_deliveys'] as $k => $v) {
                if($v['expenses']['expenses_type']==1) {
                    $expenses_amount_1 = $expenses_amount_1 + $v['active_amount'];//分成金额总和
                }else if($v['expenses']['expenses_type']==2){
                    $expenses_amount_2 = $expenses_amount_2 + $v['active_amount'];//费用金额总和
                }else if($v['expenses']['expenses_type']==3){
                    $expenses_amount_3 = $expenses_amount_3 + $v['active_amount'];//渠道费用金额总和
                }
            }

            //计算发票总和
            $invoice_active_amount=$this->sumAmount($data['invoice_deliveys']);
            $invoice_amount_total=$invoice_amount_total+$invoice_active_amount;

            //计算回款总和
            $backcash_active_amount=$this->sumAmount($data['backcash_invoices']);
            $backcash_amount_total=$backcash_amount_total+$backcash_active_amount;

            //计算坏账总和
            $badcash_active_amount=$this->sumAmount($data['badcash']);
            $badcash_amount_total=$badcash_amount_total+$badcash_active_amount;

            //月利润
            $profit=($data['amount']-$expenses_active_amount-$badcash_active_amount);
            $profit_total=$profit_total+$profit;
            //月实际应收
            $income=($profit-$backcash_active_amount);
            $income_total=$income_total+$income;

            //输出数据格式  利润=执行金额-支出总和-坏账金额
            return [
                    'business_key'  => $data['business']['business_key'],
                    'team' =>$teamDept[$data['team']],
                    'month' => $data['month'],
                    'partner' => $data['business']['partner']['company_name'],
                    'partner_type' => $data['business']['partner']['companytype_text'],
                    'company' => $data['business']['company']['company_name'],
                    'business_line' => $data['business']['business_line'],
                    'business_type' => $data['business']['business_type'],
                    'product' => $data['business']['product']['name'],
                    'product_type' => $data['business']['product']['type'],//产品类型
                    'ckey'=>$data['business']['contract']['ckey'],
                    'btime'=>$data['business']['btime'],//业务执行开始时间
                    'etime'=>$data['business']['etime'],
                    'paytime'=>$data['business']['paytime'],//业务约定结款时间
                    'team_amount' => $team_amount,//小组拆分实际执行额
                    'month_amount' => $data['amount'],//月执行金额
                    'expenses_amount' => static::numFormat($expenses_active_amount),//支出总和
                    'expenses_amount_1'=>static::numFormat($expenses_amount_1),//'分成金额总和',
                    'expenses_amount_2'=>static::numFormat($expenses_amount_2),//'费用金额总和',
                    'expenses_amount_3'=>static::numFormat($expenses_amount_3),//'渠道费用金额总和',
                    'invoice_amount' => static::numFormat($invoice_active_amount),//发票总和
                    'backcash_amount' => static::numFormat($backcash_active_amount),//回款总和
                    'badcash_amount' => static::numFormat($badcash_active_amount),//坏账总和
                    'profit' => static::numFormat($profit),//月利润
                    'income' => static::numFormat($income),//月执行应收
                    'contract_status'=>$data['business']['contract']['status_text'],
                    'confirm_status'=>$data['business']['confirmstatus_text'],
                    'backcash_status'=>$data['businessTeam']['backcashstatus_text'],
                    'invoice_status'=>$data['businessTeam']['invoicestatus_text'],
            ];
        });
        $total=['month_amount'=>static::numFormat($month_amount_total),
                'expenses_amount'=>static::numFormat($expenses_amount_total),
                'team_amount'=>static::numFormat($team_amount_total),
                'invoice_amount'=>static::numFormat($invoice_amount_total),
                'backcash_amount'=>static::numFormat($backcash_amount_total),
                'badcash_amount'=>static::numFormat($badcash_amount_total),
                'profit'=>static::numFormat($profit_total),
                'income'=>static::numFormat($income_total),
        ];
        return ['rows'=>$rows,'total'=>$total];
    }

    /**
     *计算某个执行月对应的 关联数据总和
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function sumAmount($datas=""){
        $active_amount = 0.00;
        if(!empty($datas)) {
            foreach ($datas as $k => $v) {
                if($v['isshow']==0){continue;}
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
