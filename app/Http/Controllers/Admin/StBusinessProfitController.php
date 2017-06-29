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
class StBusinessProfitController extends Controller {
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
        $viewData['team']=$this->dictionaryRepository->madeDeptTeamArray();

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

        if(!empty($businessWhere)){
            $businessWhere .= ' and  ' . session('company_sql') ;
        }else{
            $businessWhere .= session('company_sql');
        }

        $returnWhere=function ($query) use ($businessWhere) {
            $query->whereHas('business', function ($query) use ($businessWhere) {
                $query->whereRaw('('.$businessWhere.')');
            });
        };

        return $returnWhere;
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

        if(!empty($teamWhere)){
            $teamWhere .= ' and  ' . session('team_sql') ;
        }else{
            $teamWhere .=  session('team_sql') ;
        }

        $returnWhere='';
        if(!empty($teamWhere)){
            $returnWhere=function ($query) use ($teamWhere) {
                $query->whereHas('businessTeam', function ($query) use ($teamWhere) {
                    $query->whereRaw('('.$teamWhere.')');
                });
            };
        }
        return $returnWhere;
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
        $returnWhere='';
        if(!empty($deliveryWhere)){
            $returnWhere=function ($query) use ($deliveryWhere) {
                $query->whereRaw('('.$deliveryWhere.')');
            };
        }
        return $returnWhere;
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
        $returnWhere='';
        if(!empty($Where)){
            $returnWhere=function ($query) use ($Where) {
                $query->whereHas('expenses_deliveys.expenses', function ($query) use ($Where) {
                    $query->whereRaw('('.$Where.')');;});
            };
        }
        return $returnWhere;
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



    public function businessProfit(Request $request){
        $viewData=$this->seachFormData();//加载页面查询表单
        return view('admin.statistic.business-profit',$viewData);
    }

    public function businessProfitExcel(Request $request, ExcelMaker $excelMaker){
        //表头
        $headers = [
                '业务编号',
                '合作方',
                '产品名称',
                '产品类型',
                '回款状态',
                '执行小组',
                '执行月份',
                '月执行金额（元）',
                '支出金额（元）',
                '支出单号',
                '支出类型',
                '下游客户',
                '下游合作方',
                '下游合作形式',
                '合同编号',
                '执行小组月利润',
        ];

        $data=$this->getBusinessProfitDatasForExcel($request);
        $excel = $excelMaker->makeExcel($headers, $data);
        $excel->download('xls');
    }

	/**
	 *组织 返回excel导出的数据格式
	 * @author YangWei<yangwei@foxmail.com>
	 *
	 * @param $request
	 * @return array
	 */
	public function getBusinessProfitDatasForExcel($request){

		$expensesTypeList=$this->dictionaryRepository->returnList('expenses_type');
		$listdata=$this->getBusinessProfitDatas($request)->toArray();
		//查询小组所属的部门
		$teamDept=$this->dictionaryRepository->getDeptTeam();
		$table=[];
		foreach($listdata as $k=>$v){
			$contract_key="";
			if(!empty($v['business']['contracts'])){
				foreach($v['business']['contracts'] as $k=>$cv){
					if(empty($contract_key)){
						$contract_key=$cv['ckey'];
					}else{
						$contract_key=$contract_key.','.$cv['ckey'];
					}
				}
			}
			if(empty($v['expenses_deliveys'])){
				$str='--';
				$table[]=[
						"business_key"=>$v['business_key'],
						"partner_id" =>$v['business']['partner']['company_name'],
						"product"=>$v['business']['product']['name'],
						"product_type"=>$v['business']['product']['type'],
						"backcash_status"=>($v['business_team']['backcash_status']==1)?"已结清":"未结清",
						"team"=>$teamDept[$v['team']],
						"team_month"=>$v['month'],
						"team_amount"=>$v['amount'],
						"expenses_amount"=>$str,
						"expenses_key"=>$str,
						"expenses_type"=>$str,
						"under_company"=>$str,
						"under_partner"=>$str,
						"cooperation_type"=>$str,
						"contract_key"=>$contract_key,
						"profit"=>$str,
				];
			}else{
				//计算月利润  =月执行金额-所有的支出总金额-所有坏账总和
				$month_expenses_amount=0.00;//月支出总和
				foreach($v['expenses_deliveys'] as $ek=>$ev){
					$month_expenses_amount=$month_expenses_amount+$ev['active_amount'];
				}
				//计算坏账总额
				$month_badcash_amount=0.00;//月坏账总和
				foreach($v['badcash'] as $ek=>$ev){
					$month_badcash_amount=$month_badcash_amount+$ev['amount'];
				}
				$profit=$v['amount']-$month_expenses_amount-$month_badcash_amount;

				foreach($v['expenses_deliveys'] as $ek=>$ev){
					$under_company="";
					if(isset($ev['expenses']['under_partner']['undercompany'])) {
						foreach ($ev['expenses']['under_partner']['undercompany'] as $ck => $cv) {
							$under_company .= $cv['company_name'] . ',';
						}
					}
					$table[]=[
							"business_key"=>$v['business_key'],
							"partner_id" =>$v['business']['partner']['company_name'],
							"product"=>$v['business']['product']['name'],
							"product_type"=>$v['business']['product']['type'],
							"backcash_status"=>($v['business_team']['backcash_status']==1)?"已结清":"未结清",
							"team"=>$teamDept[$v['team']],
							"team_month"=>$v['month'],
							"team_amount"=>$v['amount'],
							"expenses_amount"=>$ev['active_amount'],
							"expenses_key"=>$ev['expenses']['expenses_key'],
							"expenses_type"=>$expensesTypeList[$ev['expenses']['expenses_type']],
							"under_company"=>$under_company,
							"under_partner"=>$ev['expenses']['under_partner']['company_name'],
							"cooperation_type"=>$ev['expenses']['cooperation_type'],
							"contract_key"=>$contract_key,
							"profit"=>static::numFormat($profit),
					];
				}
			}
		}
		return $table;
	}

    /**
     *财务月利润统计 表格
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function businessProfitApi(Request $request){

        //表格 标题
        $title = [
                ["name" => "业务编号", "key" => "business_key", "type" => 1],
                ["name" => "合作方", "key" => "partner_id", "type" => 1],
                ["name" => "产品名称", "key" => "product", "type" => 1],
                ["name" => "产品类型", "key" => "product_type", "type" => 1],
                ["name" => "回款状态", "key" => "backcash_status", "type" => 1],
                ["name" => "执行小组", "key" => "team", "type" => 1],
                ["name" => "执行月份", "key" => "team_month", "type" => 1],
                ["name" => "月执行金额（元）", "key" => "team_amount", "type" => 1],
                ["name" => "支出金额（元）", "key" => "expenses_amount", "type" => 1],
                ["name" => "支出单号", "key" => "expenses_key", "type" => 1],
                ["name" => "支出类型", "key" => "expenses_type", "type" => 1],
                ["name" => "下游客户", "key" => "under_company", "type" => 1],
                ["name" => "下游合作方", "key" => "under_partner", "type" => 1],
                ["name" => "下游合作形式", "key" => "cooperation_type", "type" => 1],
                ["name" => "合同编号", "key" => "contract_key", "type" => 1],
                ["name" => "执行小组月利润", "key" => "profit", "type" => 1],
				["name" => "月利润率", "key" => "profit_point", "type" => 1],

        ];

        $expensesTypeList=$this->dictionaryRepository->returnList('expenses_type');

        $listdata=$this->getBusinessProfitDatas($request)->toArray();

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();

        $table=[];
        $team_amount_toal=0.00;//小组月执行金额合计
        $expenses_amount_toal=0.00;//支出金额合计
		//$badcash_amount_toal=0.00;//坏账金额合计
        $profit_amount_toal=0.00;//月利润金额合计
        foreach($listdata as $k=>$v){
            $team_amount_toal=$team_amount_toal+$v['amount'];
            $contract_key="";
            if(!empty($v['business']['contracts'])){
                foreach($v['business']['contracts'] as $k=>$cv){
                    if($cv['type']!=1) {
                        if (empty($contract_key)) {
                            $contract_key = $cv['ckey'];
                        } else {
                            $contract_key = $contract_key . ',' . $cv['ckey'];
                        }
                    }
                }
            }
            if(empty($v['expenses_deliveys'])){
                $str='--';
                $table[]=[
                        "business_key"=>["key"=>$v['id'],"value"=>$v['business_key']],
                        "partner_id" =>["key"=>$v['id'],"value"=>$v['business']['partner']['company_name']],
                        "product"=>["key"=>$v['id'],"value"=>$v['business']['product']['name']],
                        "product_type"=>["key"=>$v['id'],"value"=>$v['business']['product']['type']],
                        "backcash_status"=>["key"=>$v['id'],"value"=>
                                ($v['business_team']['backcash_status']==1)?"已结清":"未结清"],
                        "team"=>["key"=>$v['id'],"value"=>$teamDept[$v['team']]],
                        "team_month"=>["key"=>$v['id'],"value"=>$v['month']],
                        "team_amount"=>["key"=>$v['id'],"value"=>$v['amount']],
                        "expenses_amount"=>["key"=>$v['id'],"value"=>$str],
                        "expenses_key"=>["key"=>$v['id'],"value"=>$str],
                        "expenses_type"=>["key"=>$v['id'],"value"=>$str],
                        "under_company"=>["key"=>$v['id'],"value"=>$str],
                        "under_partner"=>["key"=>$v['id'],"value"=>$str],
                        "cooperation_type"=>["key"=>$v['id'],"value"=>$str],
                        "contract_key"=>["key"=>$v['id'],"value"=>$contract_key],
                        "profit"=>["key"=>$v['id'],"value"=>$v['amount']],
						"profit_point"=>["key"=>$v['id'],"value"=>'100%'],
                ];
            }else{
                //计算月利润  =月执行金额-所有的支出总金额
                $month_expenses_amount=0.00;//月支出总和
                foreach($v['expenses_deliveys'] as $ek=>$ev){
                    $month_expenses_amount=$month_expenses_amount+$ev['active_amount'];
                }
				//计算坏账总额
				/*$month_badcash_amount=0.00;//月坏账总和
				foreach($v['badcash'] as $ek=>$ev){
					$month_badcash_amount=$month_badcash_amount+$ev['amount'];
				}*/

                if(empty($v['amount']) || $v['amount']='0.00'){
                    $profit=0;
                    $profit_point=0;//月利润率
                }else{
                    $profit=$v['amount']-$month_expenses_amount;
                    $profit_point=(round(($profit/$v['amount']),4)*100)."%";//月利润率
                }

                $profit_amount_toal=$profit_amount_toal+$profit;

                foreach($v['expenses_deliveys'] as $ek=>$ev){
                    $under_company="";
                    if(isset($ev['expenses']['under_partner']['undercompany'])) {
                        foreach ($ev['expenses']['under_partner']['undercompany'] as $ck => $cv) {
                            $under_company .= $cv['company_name'] . ',';
                        }
                    }
                    $expenses_amount_toal=$expenses_amount_toal+$ev['active_amount'];
                    $table[]=[
                            "business_key"=>["key"=>$v['id'],"value"=>$v['business_key']],
                            "partner_id" =>["key"=>$v['id'],"value"=>$v['business']['partner']['company_name']],
                            "product"=>["key"=>$v['id'],"value"=>$v['business']['product']['name']],
                            "product_type"=>["key"=>$v['id'],"value"=>$v['business']['product']['type']],
                            "backcash_status"=>["key"=>$v['id'],"value"=>
                                    ($v['business_team']['backcash_status']==1)?"已结清":"未结清"],
                            "team"=>["key"=>$v['id'],"value"=>$teamDept[$v['team']]],
                            "team_month"=>["key"=>$v['id'],"value"=>$v['month']],
                            "team_amount"=>["key"=>$v['id'],"value"=>$v['amount']],
                            "expenses_amount"=>["key"=>$ev['id'],"value"=>$ev['active_amount']],
                            "expenses_key"=>["key"=>$ev['id'],"value"=>$ev['expenses']['expenses_key']],
                            "expenses_type"=>["key"=>$ev['id'],"value"=>
                                    $expensesTypeList[$ev['expenses']['expenses_type']]],
                            "under_company"=>["key"=>$ev['id'],"value"=>$under_company],
                            "under_partner"=>["key"=>$ev['id'],"value"=>$ev['expenses']['under_partner']['company_name']],
                            "cooperation_type"=>["key"=>$ev['id'],"value"=>$ev['expenses']['cooperation_type']],
                            "contract_key"=>["key"=>$ev['id'],"value"=>$contract_key],
                            "profit"=>["key"=>$v['id'],"value"=>static::numFormat($profit)],
							"profit_point"=>["key"=>$v['id'],"value"=>$profit_point],
                    ];
                }
            }
        }

        //合计
		$profit_point_toal=(round($profit_amount_toal/$team_amount_toal,4)*100).'%';//月利润率
        $table[]=[
                "business_key"=>["key"=>'hj',"value"=>'合计'],
                "partner_id" =>["key"=>'hj',"value"=>''],
                "product"=>["key"=>'hj',"value"=>''],
                "product_type"=>["key"=>'hj',"value"=>''],
                "backcash_status"=>["key"=>'hj',"value"=>''],
                "team"=>["key"=>'hj',"value"=>''],
                "team_month"=>["key"=>'hj',"value"=>''],
                "team_amount"=>["key"=>'hj',"value"=>static::numFormat($team_amount_toal)],
                "expenses_amount"=>["key"=>'hj',"value"=>static::numFormat($expenses_amount_toal)],
                "expenses_key"=>["key"=>'hj',"value"=>''],
                "expenses_type"=>["key"=>'hj',"value"=>''],
                "under_company"=>["key"=>'hj',"value"=>''],
                "under_partner"=>["key"=>'hj',"value"=>''],
                "cooperation_type"=>["key"=>'hj',"value"=>''],
                "contract_key"=>["key"=>'hj',"value"=>''],
                "profit"=>["key"=>'hj',"value"=>static::numFormat($profit_amount_toal)],
				"profit_point"=>["key"=>'hj',"value"=>$profit_point_toal],
        ];

        //分页数据
        //$page=['total'=>$total, 'perpage'=>$per_page,'curpage'=>$current_page];
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,'table' =>$table,]
        ];
        return json_encode($returnJsonData);
    }

    /*------以下是数据计算--------------------------------------------------------------------------------------------------------------*/

    public function getBusinessProfitDatas($request){
        $where=[];
        $businessWhere=$this->businessWhere($request);
        if(!empty($businessWhere)){
            $where[]=$businessWhere;
        }
        $businessTeamWhere=$this->businessTeamWhere($request);
        if(!empty($businessTeamWhere)){
            $where[]=$businessTeamWhere;
        }
        $deliveryWhere=$this->deliveryWhere($request);
        if(!empty($deliveryWhere)){
            $where[]=$deliveryWhere;
        }
        $underPartnerWhere=$this->underPartnerWhere($request);
        if(!empty($underPartnerWhere)){
            $where[]=$underPartnerWhere;
        }
        $where[]=['isshow','=',1];
        return $this->deliveryRepository->with([
                'expenses_deliveys',
                'expenses_deliveys.expenses',
                'expenses_deliveys.expenses.under_partner',
                'expenses_deliveys.expenses.under_partner.undercompany',
				'badcash',
                'business',
                'business.contracts',
                'business.partner',
                'business.product',
                'businessTeam',
        ])->applyWhere($where)->all();//paginate(20);
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
