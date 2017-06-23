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
class StBusinessLegalController extends Controller {
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

        if(!empty($businessWhere)){
            $businessWhere .= ' and  ' . session('company_sql') ;
        }else{
            $businessWhere .=  session('company_sql') ;
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

        $returnWhere="";
        if(!empty($teamWhere)){
            $returnWhere=function ($query) use ($teamWhere) {
                $query->whereRaw('('.$teamWhere.')');
            };
        }
        return $returnWhere;
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
        $returnWhere="";
        if(!empty($contractWhere)){
            $returnWhere=function ($query) use ($contractWhere) {
                $query->whereHas('business.contract', function ($query) use ($contractWhere) {
                    $query->whereRaw('('.$contractWhere.')');
                });
            };
        }
        return $returnWhere;
    }


    public function businessLegal(Request $request){
        $viewData=$this->seachFormData();//加载页面查询表单
        $viewData['contract_status']=Contract::$statusList;
        $viewData['confirm_status']=BusinessModel::$confirm_status;
        $datas=$this->getbusinessLegalDatas($request);
        $viewData['listdata']   = $datas['rows'];

        return view('admin.statistic.business-legal',$viewData);
    }

    public function businessLegalExcel(Request $request, ExcelMaker $excelMaker){
        //表头
        $headers = [
                '业务编号',
                '执行小组',
                '业务线',
                '合作方',
                '客户名称',
                '产品名称',
                '合同编号',
                '实际金额拆分',
                '回款总额',
                '合同状态',
                '确认函状态',
        ];
        $data=$this->getbusinessLegalDatas($request);
        $excel = $excelMaker->makeExcel($headers, $data['rows']);
        $excel->download('xls');
    }





    public function getbusinessLegalDatas($request){
        $where=[];

        $businessWhere=$this->businessWhere($request);
        if(!empty($businessWhere)){
            $where[]=$businessWhere;
        }

        $businessTeamWhere=$this->businessTeamWhere($request);
        if(!empty($businessTeamWhere)){
            $where[]=$businessTeamWhere;
        }

        $contractWhere=$this->contractWhere($request);
        if(!empty($contractWhere)){
            $where[]=$contractWhere;
        }
        $where[]=['isshow','=',1];

        $datas=$this->businessTeamRepository->with([
                'business',
                'business.partner',
                'business.company',
                'business.product',
                'business.contract',
                'backcash_invoices',
        ])->applyWhere($where)->all();

        //查询小组所属的部门
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $rows=$datas->map(function ($data) use($teamDept) {
            //计算小组业务中所有执行月的执行额
            $delivery_amount=0.00;
            foreach($data['deliverys'] as $k=>$v){
                $delivery_amount=$delivery_amount+$v->amount;
            }
            //计算回款总和
            $backcash_active_amount=$this->sumAmount($data['backcash_invoices']);
            $contract_key="无";
            $contract_status="无";
            if(isset($data['business']['contract']['ckey'])){
                $contract_key=$data['business']['contract']['ckey'];
            }
            if(isset($data['business']['contract']['status_text'])){
                $contract_status=$data['business']['contract']['status_text'];
            }
            return [
                    'business_key'=>$data['business']['business_key'],
                    'team'=>$teamDept[$data['team']],
                    'business_line'=>$data['business']['business_line'],
                    'partner' => $data['business']['partner']['company_name'],
                    'company'=> $data['business']['company']['company_name'],
                    'product' => $data['business']['product']['name'],
                    'contract_key'=>$contract_key,
                    'team_amount'=>$data['active_amount'],
                    'backcash_amount' =>static::numFormat($backcash_active_amount),//回款总和
                    'contract_status'=>$contract_status,//合同状态
                    'confirm_status'=>$data['business']['confirmstatus_text'],//确认函状态
            ];
        });
        return ['rows'=>$rows];
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
