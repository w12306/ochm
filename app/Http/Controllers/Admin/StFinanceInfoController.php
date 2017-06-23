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
class StFinanceInfoController extends Controller {
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
                    $business_type_str = $business_type_str . ',"' . $v . '"';
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
        return $teamWhere.' and  ' . session('team_sql');
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


    public function financeInfo(Request $request){
        $viewData=$this->seachFormData();//加载页面查询表单

        $datas=$this->getFinanceInfoDatas($request);
        $viewData['listdata']   = $datas['rows'];
        $viewData['total']      = $datas['total'];

        return view('admin.statistic.finance-info',$viewData);
    }

    public function financeInfoExcel(Request $request, ExcelMaker $excelMaker){
        //表头
        $headers = [
                '业务编号',
                '执行小组',
                '业务线',
                '合作方',
                '客户名称',
                '产品名称',
                '产品类型',
                '实际金额拆分',
                '执行总额',
                '发票总额',
                '回款总额',
                '支出总额',
                '坏账总额',
                '回款状态',
                '未开发票金额',
                '执行应收',
        ];
        $data=$this->getFinanceInfoDatas($request);
        $excel = $excelMaker->makeExcel($headers, $data['rows']);
        $excel->download('xls');
    }




    public function getFinanceInfoDatas($request){
        $businessWhere=$this->businessWhere($request);
        $businessTeamWhere=$this->businessTeamWhere($request);

        $datas=$this->businessTeamRepository->with([
                'business',
                'business.partner',
                'business.company',
                'business.product',
                'deliverys',
                'invoice_deliveys',
                'backcash_invoices',
                'expenses_deliveys',
                'badcash',
        ])->applyWhere([
                function ($query) use ($businessWhere) {
                    $query->whereHas('business', function ($query) use ($businessWhere) {
                        $query->whereRaw('('.$businessWhere.')');
                    });
                },
				function ($query)  {
					$query->whereHas('deliverys', function ($query)  {
						$query->where('isshow','=',1);
					});
				},
                function ($query) use ($businessTeamWhere) {
                        $query->whereRaw('('.$businessTeamWhere.')');
                },
                ['isshow','=',1]
        ])->all();

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        //格式化数据
        $team_amount_total=0.00;//小组实际执行金额合计
        $delivery_amount_total=0.00;//执行额合计
        $invoice_amount_total=0.00;//发票总和
        $backcash_amount_total=0.00;//回款总和
        $expenses_amount_total=0.00;//支出总和合计
        $badcash_amount_total=0.00;//坏账总和合计
        $losinvoice_amount_total=0.00;//未开发票合计
        $income_total=0.00;//月应收 合计
        $rows=$datas->map(function ($data) use (&$team_amount_total,&$delivery_amount_total,&$invoice_amount_total,
                                            &$backcash_amount_total,&$expenses_amount_total,&$badcash_amount_total,
                                            &$losinvoice_amount_total,&$income_total,$teamDept) {

            //小组实际执行金额合计
            $team_amount_total=$team_amount_total+$data->active_amount;//合计
            //计算小组业务中所有执行月的执行额
            $delivery_amount=0.00;

            foreach($data->deliverys as $v){
				if($v->isshow==0){continue;}
                $delivery_amount=$delivery_amount+$v->amount;
            }
            $delivery_amount_total=$delivery_amount_total+$delivery_amount;//合计
            //计算发票总和
            $invoice_active_amount=$this->sumAmount($data->invoice_deliveys);
            $invoice_amount_total=$invoice_amount_total+$invoice_active_amount;//合计

            //计算回款总和
            $backcash_active_amount=$this->sumAmount($data->backcash_invoices);
            $backcash_amount_total=$backcash_amount_total+$backcash_active_amount;//合计

            //计算支出总和
            $expenses_active_amount=$this->sumAmount($data->expenses_deliveys);
            $expenses_amount_total=$expenses_amount_total+$expenses_active_amount;//合计

            //计算坏账总和
            $badcash_active_amount=$this->sumAmount($data->badcash);
            $badcash_amount_total=$badcash_amount_total+$badcash_active_amount;//合计

            //小组执行应收=执行总额-支出总额-坏账总额-回款总额
            $income=$delivery_amount-$expenses_active_amount-$badcash_active_amount-$backcash_active_amount;
            $income_total=$income_total+$income;

            //未开发票金额 =应开发票总额(实际金额-支出-坏账)-已开发票总额
            $losinvoice_amount=$data->active_amount-$expenses_active_amount-$badcash_active_amount-$invoice_active_amount;
            $losinvoice_amount_total=$losinvoice_amount_total+$losinvoice_amount;

            return [
                    'business_key'=>$data['business']['business_key'],
                    'team'=>$teamDept[$data['team']],
                    'business_line'=>$data['business']['business_line'],
                    'partner' => $data['business']['partner']['company_name'],
                    'company'=> $data['business']['company']['company_name'],
                    'product' => $data['business']['product']['name'],
                    'product_type' => $data['business']['product']['type'],
                    'team_amount'=>$data['active_amount'],
                    'delivery_amount'=>static::numFormat($delivery_amount),//小组月执行总额
                    'invoice_amount' => static::numFormat($invoice_active_amount),//发票总和
                    'backcash_amount' => static::numFormat($backcash_active_amount),//回款总和
                    'expenses_amount' => static::numFormat($expenses_active_amount),//支出总和
                    'badcash_amount' => static::numFormat($badcash_active_amount),//坏账总和
                    'backcash_status'=>$data['backcashstatus_text'],//回款状态
                    'losinvoice_amount'=>static::numFormat($losinvoice_amount),//未开发票金额
                    'income' => static::numFormat($income),//小组执行应收
            ];
        });

        $total=['team_amount'=>static::numFormat($team_amount_total),
                'delivery_amount'=>static::numFormat($delivery_amount_total),
                'invoice_amount'=>static::numFormat($invoice_amount_total),
                'backcash_amount'=>static::numFormat($backcash_amount_total),
                'expenses_amount'=>static::numFormat($expenses_amount_total),
                'badcash_amount'=>static::numFormat($badcash_amount_total),
                'losinvoice_amount'=>static::numFormat($losinvoice_amount_total),
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
            foreach ($datas as $v) {
                if($v['isshow']==0){continue;}
                if(!isset($v->amount)) {
                    $active_amount = $active_amount + $v->active_amount;
                }else{
                    $active_amount = $active_amount + $v->amount;//处理坏账总和数据
                }
            }
        }
        return $active_amount;
    }


}
