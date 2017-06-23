<?php
namespace App\Http\Controllers\Admin;

use App\Models\CompanyTeamModel;
use App\Models\PartnersModel;
use App\Models\UnderCompanyTeamModel;
use App\Models\UnderPartnersModel;
use App\Repositories\ExecutiveRepository;
use App\Repositories\PartnersRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\BusinessRepository;
use App\Models\BusinessModel;
use App\Repositories\CompanyRepository;
use App\Repositories\ProductRepository;
use App\Repositories\DictionaryRepository;
use App\Repositories\ContractRepository;
use App\Repositories\DeliveryRepository;
use App\Repositories\BackcashInvoiceRepository;
use App\Repositories\PaymentExpensesRepository;

use App\Repositories\UnderCompanyRepository;
use App\Repositories\UnderPartnersRepository;
use File;
use Validator;
use DB;

/**
 * 数据返回接口
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class JsonDataApiController extends Controller
{
    use CommonResponse;

    protected  $businessRepository;

    protected  $companyRepository;

    protected  $businessModel;

    protected  $productRepository;

    protected  $dictionaryRepository;

    protected  $partnersModel;

    protected  $contractRepository;

    protected  $partnersRepository;

    protected  $deliveryRepository;

    protected  $backcashInvoiceRepository;

    protected  $underCompanyRepository;

    protected  $underPartnersRepository;

    protected  $underPartnersModel;

    protected  $paymentExpensesRepository;

    protected  $companyTeamModel;

    protected  $underCompanyTeamModel;

	protected 	$executiveRepository;
    /**
     *
     */
    public function __construct(BusinessRepository $businessRepository,
                                CompanyRepository $companyRepository,
                                ProductRepository $productRepository,
                                DictionaryRepository $dictionaryRepository,
                                ContractRepository $contractRepository,
                                PartnersRepository $partnersRepository,
                                BusinessModel $businessModel,
                                PartnersModel $partnersModel,
                                DeliveryRepository $deliveryRepository,
                                BackcashInvoiceRepository $backcashInvoiceRepository,
                                UnderCompanyRepository $underCompanyRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                UnderPartnersModel $underPartnersModel,
                                PaymentExpensesRepository $paymentExpensesRepository,
                                CompanyTeamModel $companyTeamModel,
                                UnderCompanyTeamModel $underCompanyTeamModel,
								ExecutiveRepository $executiveRepository
                                )
    {
        $this->businessRepository   =$businessRepository;
        $this->companyRepository    =$companyRepository;
        $this->productRepository    =$productRepository;
        $this->dictionaryRepository =$dictionaryRepository;
        $this->contractRepository   =$contractRepository;
        $this->partnersRepository   =$partnersRepository;
        $this->deliveryRepository   =$deliveryRepository;
        $this->backcashInvoiceRepository=$backcashInvoiceRepository;
        $this->underPartnersRepository=$underPartnersRepository;
        $this->underCompanyRepository=$underCompanyRepository;
        $this->underPartnersModel=$underPartnersModel;
        $this->paymentExpensesRepository=$paymentExpensesRepository;

        $this->businessModel        =$businessModel;
        $this->partnersModel        =$partnersModel;
        $this->companyTeamModel     =$companyTeamModel;
        $this->underCompanyTeamModel=$underCompanyTeamModel;
		$this->executiveRepository	=$executiveRepository;
    }


    public function createBusinessJsonData($bid="",$executive_id=""){
        $company_id=0;
        $partner_id=0;
        $product_id=0;
        $business_type='';
        $business_line=[];
        $contract_type='';
        $contract_id='';
        $btime='';
        $etime ='';
        $paytime ='';
        $amount ='';
        $active_amount ='';
        $confirm_status ='';
        $team ='';
        $remark='';
        $business_key="";

        if(!empty($bid)){
            $businessData=$this->businessRepository->getBusinessById($bid);
            $business_key=$businessData->business_key;
            $company_id=$businessData->company_id;
            $partner_id=$businessData->partner_id;
            $product_id=$businessData->product_id;
            $business_type=$businessData->business_type;
            $contract_type=$businessData->contract_type;
            $btime=$businessData->btime;
            $etime =$businessData->etime;
            $paytime =$businessData->paytime;
            $amount =$businessData->amount;
            $active_amount =$businessData->active_amount;
            $confirm_status =$businessData->confirm_status;
            $remark=$businessData->remark;
            $contract_id=$businessData->contract_id;
			$executive_id=$businessData->executive_id;

            if($businessData->business_line!=""){
                $business_line=explode(',',$businessData->business_line);
            }
        }
        $returnData['id']=$bid;//编辑用的id

        $returnData['execution_id']=$this->executiveRepository->getExecutiveBusinessSelect($executive_id);
		if(!empty($executive_id)){
			$executive=$this->executiveRepository->find($executive_id);
			$company_id=$executive->company_id;
			$partner_id=$executive->partner_id;
			$product_id=$executive->product_id;
		}
        $returnData['company_id']=$this->companyRepository->getAllCompanyArray($company_id);//上游客户数据
        $returnData['partner_id']=$this->getPartnerByCompanyId($company_id,$partner_id,'fun');//合作方数据
        $returnData['product_id']=$this->productRepository->returnProductNameArray($product_id);//产品
        $returnData['business_type']=$this->dictionaryRepository->returnTypeArray('business_type',$business_type);//业务类型
        $returnData['business_line']=$this->dictionaryRepository->returnTypeArray('business_line',$business_line);//业务线

        $contract_type_arr=$this->businessModel->getContractType();

        ////合同类型
        $contract_type_arr_new[]=['key'=>0,'value'=>'--请选择--','s'=>0];
        foreach($contract_type_arr as $k=>$v){
            //if($v=="框架合同"){
                //continue;
            //}
            $s=0;
            if($contract_type==$k){
                $s=1;
            }
            $contract_type_arr_new[]=['key'=>$k,'value'=>$v,'s'=>$s];
        }
        $returnData['contract_type']=$contract_type_arr_new;

        $returnData['contract_id']=$this->getContractByType($contract_type,$contract_id,'list');//合同编号

        $returnData['btime']=$btime;//执行开始时间
        $returnData['etime']=$etime;//执行结束时间
        $returnData['paytime']=$paytime;//约定结款时间
        $returnData['amount']=$amount;//预算金额
        $returnData['active_amount']=$active_amount;//实际金额
        $returnData['confirm_status']=$this->businessRepository->getConfirmStatus($confirm_status);//确认函状态
        $returnData['remark']=$remark;//业务补充


        $teamData=$this->businessRepository->getBusinessTeamBykey($business_key);//获得业务的执行小组 没有就返回全部小组
        $deptTeam=$this->dictionaryRepository->getDeptTeam();
        $amountTeamArray=array();
        $actTeamArray=array();
        $teamArr=[];
        foreach($teamData as $d){
            $amountTeamArray[]=['team'=>$deptTeam[$d->team],'name'=>'y_'.$d->team,'value'=>$d->amount];
            $actTeamArray[]=['team'=>$deptTeam[$d->team],'name'=>'act_'.$d->team,'value'=>$d->active_amount];
            $teamArr[$d->team]=$d->team;
        }

        $returnData['estimate_amount']=$amountTeamArray;//预算金额拆分
        $returnData['act_amount']=$actTeamArray;//实际金额拆分

        $returnData['team']=$this->dictionaryRepository->madeDeptTeamArray($teamArr);//执行小组

        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *根据客户ID返回下属所有的合作方数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $companyid 客户ID
     * @param int $selectid 被选中的值
     * @param string $ty 函数调用或者接口调用
     * @return string
     * @internal param 客户ID $companyId
     *
     */
    public function getPartnerByCompanyId($companyid,$selectid=0,$ty='api'){
        if($companyid==0){return [];}
        $companyData=$this->companyRepository->getCompanyById($companyid);
        if($companyData->partner_id==""){return '';}
        $res=$this->partnersModel->whereRaw('id in ('.$companyData->partner_id.')')->lists('id','company_name');

        $returnData=array();
        foreach($res as $v=>$k){
            $select=0;

            if(is_array($selectid) && !empty($selectid)){//用于多个合作方的情况
                if(in_array($k,$selectid)){$select=1;}
            }else{
                if($selectid!=0 && $selectid==$k){$select=1;}
            }

            $returnData[]=['key'=>$k,'value'=>$v,'s'=>$select];
        }
        if ($ty=='api'){
            return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
        }else{
            return $returnData;
        }
    }

    /**
     *根据下游客户ID返回下属所有的合作方数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $companyid 客户ID
     * @param int $selectid 被选中的值
     * @param string $ty 函数调用或者接口调用
     * @return string
     * @internal param 客户ID $companyId
     *
     */
    public function getUnderPartnerByCompanyId($companyid,$selectid=0,$ty='api'){
        if($companyid==0){return [];}
        $companyData=$this->underCompanyRepository->getCompanyById($companyid);
        if($companyData->partner_id==""){return '';}
        $res=$this->underPartnersModel->whereRaw('id in ('.$companyData->partner_id.')')->lists('id','company_name');
        $returnData=array();
        foreach($res as $v=>$k){
            $select=0;

            if(is_array($selectid) && !empty($selectid)){//用于多个合作方的情况
                if(in_array($k,$selectid)){$select=1;}
            }else{
                if($selectid!=0 && $selectid==$k){$select=1;}
            }

            $returnData[]=['key'=>$k,'value'=>$v,'s'=>$select];
        }
        if ($ty=='api'){
            return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
        }else{
            return $returnData;
        }
    }

    /**
     *根据合同类型 获得对应的合同编号列表
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $type
     * @param string $ty 函数调用或者接口调用
     * @return array
     */
    public function getContractByType($type,$selected=0,$ty='api'){
        if(empty($type)){return [];}
        $res=$this->contractRepository->getContractByType($type);
        $returnData=array();
        foreach($res as $k=>$d){
            $select=0;
            if($selected==$k){$select=1;}
            $returnData[]=['key'=>$k,'value'=>$d,'s'=>$select];
        }
        if ($ty=='api'){
            return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
        }else{
            return $returnData;
        }
    }

    /**
     * 创建业务时 根据框架合同ID过滤子合同ID
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $frameId
     * @param int $selected
     * @return array|string
     */
    public function getChildContractByFrameId($pid,$selected=0){
        if(empty($pid)){return [];}
        $res=$this->contractRepository->getContractByParentId($pid);
        $returnData=array();
        foreach($res as $k=>$d){
            $select=0;
            if($selected==$k){$select=1;}
            $returnData[]=['key'=>$k,'value'=>$d,'s'=>$select];
        }
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);

    }

    /**
     *组织产品 新增/编辑页面数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getProductAttributes($id=''){
        $productData=[];
        if($id!=''){
            $productData=$this->productRepository->getProductById($id)->toArray();
        }
        $type='';
        $company_id='';
        $name='';
        if(!empty($productData)){
            $type=$productData['type'];
            $company_id=$productData['company_id'];
            $name=$productData['name'];
        }


        $moreValueFields=['game_screen','game_theme','screen_style','business_model','charging_mode','game_type'];

        $gameAttributes=$this->productRepository->returnGameAttributes();

        foreach($gameAttributes as $k=>$d){
            foreach($d as $value){
                $seleted=0;
                if(in_array($k,$moreValueFields)){
                    if(!empty($productData[$k])){
                        $valueArr=explode(',',$productData[$k]);
                        if(in_array($value,$valueArr)){
                            $seleted=1;
                        }
                    }
                }else{
                    if(!empty($productData[$k]) && $productData[$k]==$value){$seleted=1;}
                }
                $returnData[$k][]=['key'=>$value,'value'=>$value,'s'=>$seleted];//产品属性
            }
        }

        $returnData['type']=$this->dictionaryRepository->returnTypeArray('product_type',$type);
        $returnData['name']=$name;//产品名称
        $returnData['company_id']=$this->companyRepository->getAllCompanyArray($company_id);//上游客户数据
        $returnData['id']=$id;//编辑用的id

        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *组织 客户新增/编辑数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getCompanyEditJson($id=''){
        $companyData=[];
        $teamData=[];
        if(!empty($id)){
            $companyData=$this->companyRepository->getCompanyById($id)->toArray();
            $teamData=$this->companyTeamModel->whereRaw('company_id='.$id)->get();
        }
        $company_name='';
        $remark='';
        $partner_id_array=array();
        if(!empty($companyData)){
            $company_name=$companyData['company_name'];
            $remark=$companyData['remark'];
            $partner_id_array=explode(',',$companyData['partner_id']);
        }
        $returnData['id']=$id;//编辑用的id
        $returnData['company_name']=$company_name;//客户名称

        $returnData['partner_id']=$this->getPartnerByCompanyId($id,$partner_id_array,'fun');//合作方数据
        $returnData['remark']=$remark;//备注

        $teamArr='';
        if(!empty($teamData)){
            foreach($teamData as $t){
                $teamArr[$t->team]=$t->team;
            }
        }
        $returnData['team']=$this->dictionaryRepository->madeDeptTeamArray($teamArr);
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }
    /**
     *组织 下游客户新增/编辑数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getUnderCompanyEditJson($id=''){
        $companyData=[];
        $teamData=[];
        if(!empty($id)){
            $companyData=$this->underCompanyRepository->getCompanyById($id)->toArray();
            $teamData=$this->underCompanyTeamModel->whereRaw('company_id='.$id)->get();
        }
        $company_name='';
        $remark='';
        $partner_id_array=array();
        if(!empty($companyData)){
            $company_name=$companyData['company_name'];
            $remark=$companyData['remark'];
            $partner_id_array=explode(',',$companyData['partner_id']);
        }
        $returnData['id']=$id;//编辑用的id
        $returnData['company_name']=$company_name;//客户名称


        $returnData['partner_id']=$this->getUnderPartnerByCompanyId($id,$partner_id_array,'fun');//合作方数据
        $returnData['remark']=$remark;//备注

        $teamArr='';
        if(!empty($teamData)){
            foreach($teamData as $t){
                $teamArr[$t->team]=$t->team;
            }
        }
        $returnData['team']=$this->dictionaryRepository->madeDeptTeamArray($teamArr);

        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *上游 合作方表单数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getPartnerEditJson($id=''){
        $partnerData=[];
        if($id!=''){
            $partnerData=$this->partnersRepository->getPartnerById($id)->toArray();
        }
        $company_type='';
        $company_name='';
        $balance='';
        $taxkey='';
        $nickname='';
        $address='';
        $tel='';
        $boss='';

        if(!empty($partnerData)){
            $company_name=$partnerData['company_name'];
            $company_type=$partnerData['company_type'];
            $balance=$partnerData['balance'];
            $taxkey=$partnerData['taxkey'];
            $nickname=$partnerData['nickname'];
            $address=$partnerData['address'];
            $tel=$partnerData['tel'];
            $boss=$partnerData['boss'];
        }
        $returnData['id']=$id;//编辑用的id
        $returnData['company_name']=$company_name;//合作方全称称
        $returnData['balance']=$balance;
        $returnData['taxkey']=$taxkey;
        $returnData['nickname']=$nickname;
        $returnData['address']=$address;
        $returnData['tel']=$tel;
        $returnData['boss']=$boss;
        $returnData['company_type']=$this->partnersRepository->getPartnerTypeArray($company_type);

        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *下游游 合作方表单数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getUnderPartnerEditJson($id=''){
        $partnerData=[];
        if($id!=''){
            $partnerData=$this->underPartnersRepository->getPartnerById($id)->toArray();
        }
        $company_type='';
        $company_name='';
        $balance='';
        $taxkey='';
        $nickname='';
        $address='';
        $tel='';
        $boss='';

        if(!empty($partnerData)){
            $company_name=$partnerData['company_name'];
            $company_type=$partnerData['company_type'];
            $balance=$partnerData['balance'];
            $taxkey=$partnerData['taxkey'];
            $nickname=$partnerData['nickname'];
            $address=$partnerData['address'];
            $tel=$partnerData['tel'];
            $boss=$partnerData['boss'];
        }
        $returnData['id']=$id;//编辑用的id
        $returnData['company_name']=$company_name;//合作方全称称
        $returnData['balance']=$balance;
        $returnData['taxkey']=$taxkey;
        $returnData['nickname']=$nickname;
        $returnData['address']=$address;
        $returnData['tel']=$tel;
        $returnData['boss']=$boss;
        $returnData['company_type']=$this->underPartnersRepository->getPartnerTypeArray($company_type);

        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *组织合作方弹出层数据列表数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getPartnerList(){
        $partnerData=$this->partnersRepository->getAllPartnersPageList();
        $company_type_Array=PartnersModel::$typeArray;
        $returnData=array();
        foreach($partnerData as $d){
            $returnData[]=['company_name'=>$d->company_name,
                            'company_type'=>$company_type_Array[$d->company_type],
                            'address'=>$d->address,
                            'id'=>$d->id,
            ];
        }
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }
    /**
     *组织下游合作方弹出层数据列表数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getUnderPartnerList(){
        $partnerData=$this->underPartnersRepository->getPartnersList();
        $company_type_Array=UnderPartnersModel::$typeArray;
        $returnData=array();
        foreach($partnerData as $d){
            $returnData[]=['company_name'=>$d->company_name,
                    'company_type'=>$company_type_Array[$d->company_type],
                    'address'=>$d->address,
                    'id'=>$d->id,
            ];
        }
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *组织某个业务的月执行额 列表数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param int $bid  业务团队ID
     * @return string
     */
    public function getDeliveryList($team_id=0){
        if(empty($team_id)){return json_encode(['status'=>'error','info'=>'参数缺失','data'=>'']);}
        $delivery=$this->deliveryRepository->getByTeamId($team_id)->toArray();
        $returnData=array();
        $toatal=0.00;
        foreach($delivery as $k=>$d){
            $returnData[]=['id'=>$d['id'],
                            'business_id'=>$d['business_id'],
                            'month'=>$d['month'],
                            'amount'=>$d['amount'],
                            'created_at'=>$d['created_at'],
            ];
            $toatal=$toatal+$d['amount'];
        }

        $returnData[]=['id'=>'-',
                'business_id'=>'-',
                'month'=>'总计',
                'amount'=>number_format($toatal,2),
                'created_at'=>'-',
        ];
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *根据合作方ID获得产品下拉框数据
     * @author YangWei<yangwei@stnts.com>
     *
     * @param string $id
     * @param int $selectid
     * @return array|string
     */
    public function getProductListByCompanyId($id='',$selectid=0){

        if($id==0){return [];}
        $res=$this->productRepository->getProductListByCompanyId($id);
        $returnData=array();
        foreach($res as $v=>$k){
            $select=0;
            if($selectid!=0 && $selectid==$k){$select=1;}

            $returnData[]=['key'=>$k,'value'=>$v,'s'=>$select];
        }
        return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
    }

    /**
     *业务详情页 下方的执行月发票对应的回款数据json
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getBusinessBackcashFormData($business_key){

        $where=[];
        if(!empty($business_key)){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=',$business_key);
            };
        }else{
            $returnJsonData = ["status"=>"error","info" =>"参数错误!" ];
            return json_encode($returnJsonData);
            exit;
        }

        $data = $this->backcashInvoiceRepository->with(['backcash','invoice','business.partner'])
                                                ->applyWhere($where)
                                                ->all();

        //获得银行的数据List
        $bankArr=$this->dictionaryRepository->returnList('bank');

        //获得回款方式数据List
        $backcash_typeArr=$this->dictionaryRepository->returnList('backcash_type');
        //获得小组和小组部门信息
        $teamDept=$this->dictionaryRepository->getDeptTeam();

        $table=[];
        foreach($data as $d){
            $partner=$d->business->partner->company_name;
            $table[]=[
                    "business_key"=>["key"=>$d->business_key,"value"=>$d->business_key],
                    "partner_id" =>["key"=>$d->business_key,"value"=>$partner],
                    "team"=>["key"=>$d->team,"value"=>$teamDept[$d->team]],
                    "team_month"=>["key"=>$d->month,"value"=>$d->month],
                    "team_amount"=>["key"=>$d->month,"value"=>$d->month_amount],
                    "month_invoice_amount"=>["key"=>$d->month,"value"=>$d->invoice_month_amount],
                    "invoice_key"=>["key"=>$d->month,"value"=>$d->invoice_key],
                    "invoice_type"=>["key"=>$d->month,"value"=>$d->invoice->invoice_type],
                    "invoice_time"=>["key"=>$d->month,"value"=>$d->invoice->invoice_time],
                    "backcash_amount"=>["key"=>$d->id,"value"=>$d->active_amount],
                    "backcash_time"=>["key"=>$d->id,"value"=>$d->backcash->backtime],
                    "backcash_bank"=>["key"=>$d->id,"value"=>$bankArr[$d->backcash->bank]],
                    "backcash_type"=>["key"=>$d->id,"value"=>$backcash_typeArr[$d->backcash->backtype]],
            ];

        }
		$title=[];
        //表格 标题
		if(!empty($table)) {
			$title = [
					["name" => "业务编号", "key" => "business_key", "type" => 1],
					["name" => "合作方", "key" => "partner_id", "type" => 1],
					["name" => "执行小组", "key" => "team", "type" => 1],
					["name" => "执行月份", "key" => "team_month", "type" => 1],
					["name" => "月执行金额", "key" => "team_amount", "type" => 1],
					["name" => "月执行额的发票金额", "key" => "month_invoice_amount", "type" => 1],
					["name" => "发票号码", "key" => "invoice_key", "type" => 1],
					["name" => "发票类型", "key" => "invoice_type", "type" => 1],
					["name" => "开票日期", "key" => "invoice_time", "type" => 1],
					["name" => "回款金额", "key" => "backcash_amount", "type" => 1],

					["name" => "回款时间", "key" => "backcash_time", "type" => 1],
					["name" => "回款银行", "key" => "backcash_bank", "type" => 1],
					["name" => "回款方式", "key" => "backcash_type", "type" => 1],
			];
		}

        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,'table' =>$table,]
        ];
        return json_encode($returnJsonData);
    }

    /**
     *获得业务详情页中的 支出-付款列表数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getBusinessExpensesFormData($business_key){
        $where=[];
        if(!empty($business_key)){
            $where[] = function ($query) use ($business_key) {
                $query->where('business_key','=',$business_key);
            };
        }else{
            $returnJsonData = ["status"=>"error","info" =>"参数错误!" ];
            return json_encode($returnJsonData);
            exit;
        }

        $data=$this->paymentExpensesRepository->with([
                'business',
                'business.partner',
                'payment',
                'expenses_delivey',
                'expenses_delivey.expenses',
                'expenses_delivey.expenses.under_partner',
        ])->applyWhere($where)
          ->all();

        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');
        //获得小组和小组部门信息
        $teamDept=$this->dictionaryRepository->getDeptTeam();
        $table=[];
        foreach($data as $d){
            $expenses_delivey_id=$d->expenses_delivery_id;
            $expenses_type=$d->expenses_delivey->expenses->expenses_type;
            $under_partner=$d->expenses_delivey->expenses->under_partner->company_name;
            $payment_time=$d->expenses_delivey->expenses->payment_time;
            $table[]=[
                    "business_key"=>["key"=>$d->business_key,"value"=>$d->business_key],
                    "partner_id" =>["key"=>$d->business_key,"value"=>$d->business->partner->company_name],
                    "team"=>["key"=>$d->team,"value"=>$teamDept[$d->team]],
                    "team_month"=>["key"=>$d->team,"value"=>$d->team_month],
                    "team_amount"=>["key"=>$d->team,"value"=>$d->team_amount],

                    "expenses_active_amount"=>["key"=>$expenses_delivey_id,"value"=>$d->expenses_delivey->active_amount],
                    "expenses_type"=>["key"=>$expenses_delivey_id,"value"=>$expenses_typeArr[$expenses_type]],
                    "under_partner_id"=>["key"=>$expenses_delivey_id,"value"=>$under_partner],
                    "expenses_payment_time"=>["key"=>$expenses_delivey_id,"value"=>$payment_time],

                    "payment_active_amount"=>["key"=>$d->id,"value"=>$d->active_amount],
                    "payment_time"=>["key"=>$d->id,"value"=>$d->payment->payment_time],
                    "invoice_amount"=>["key"=>$d->id,"value"=>$d->payment->invoice_amount],
                    "invoice_num"=>["key"=>$d->id,"value"=>$d->payment->invoice_num],
                    "invoice_type"=>["key"=>$d->id,"value"=>$d->payment->invoice_type],
            ];

        }
		//表格 标题
		$title=[];
		if(!empty($table)) {
			$title = [
					["name" => "业务编号", "key" => "business_key", "type" => 1],
					["name" => "合作方", "key" => "partner_id", "type" => 1],
					["name" => "执行小组", "key" => "team", "type" => 1],
					["name" => "执行月份", "key" => "team_month", "type" => 1],
					["name" => "月执行金额", "key" => "team_amount", "type" => 1],


					["name" => "支出金额", "key" => "expenses_active_amount", "type" => 1],
					["name" => "支出类型", "key" => "expenses_type", "type" => 1],
					["name" => "支出客户", "key" => "under_partner_id", "type" => 1],
					["name" => "预付款时间", "key" => "expenses_payment_time", "type" => 1],

					["name" => "付款金额", "key" => "payment_active_amount", "type" => 1],
					["name" => "付款时间", "key" => "payment_time", "type" => 1],
					["name" => "发票金额", "key" => "invoice_amount", "type" => 1],
					["name" => "发票编号", "key" => "invoice_num", "type" => 1],
					["name" => "发票类型", "key" => "invoice_type", "type" => 1],
			];
		}
        $returnJsonData = ["status"=>"success","info" =>"","data"=>['title' => $title,
                'table' =>$table,
        ]
        ];
        return json_encode($returnJsonData);
    }

}
