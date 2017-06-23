<?php
namespace App\Http\Controllers\Admin;
use App\Models\Advertis;
use App\Models\Executive;
use App\Repositories\AdvertisRepository;
use App\Repositories\BusinessRepository;
use App\Repositories\CompanyRepository;
use App\Repositories\ExecutiveRepository;
use App\Repositories\ExecutiveRowsRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\ProductRepository;
use App\Services\ExcelMaker;
use App\Services\ExecutiveInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;

use App\Repositories\DictionaryRepository;
use File;
use Mockery\CountValidator\Exception;
use Validator;
use DB;

/**执行单排期管理
 * Class ExecutiveController
 * @package App\Http\Controllers\Admin
 */
class ExecutiveController extends Controller
{
	use CommonResponse;

	protected	$companyRepository;

	protected	$productRepository;

	protected 	$dictionaryRepository;

	protected 	$partnersRepository;

	protected 	$executiveRepository;

	protected 	$executiveRowsRepository;

	protected 	$executiveTdStyleRepository;

	protected 	$advertisRepository;

	protected 	$businessRepository;

	public function __construct(CompanyRepository $companyRepository,
								ProductRepository $productRepository,
								DictionaryRepository $dictionaryRepository,
								PartnersRepository $partnersRepository,
								ExecutiveRepository $executiveRepository,
								ExecutiveRowsRepository $executiveRowsRepository,
								AdvertisRepository $advertisRepository,
								BusinessRepository $businessRepository)
	{
		$this->companyRepository		=	$companyRepository;
		$this->productRepository		=	$productRepository;
		$this->dictionaryRepository		=	$dictionaryRepository;
		$this->partnersRepository		=	$partnersRepository;

		$this->executiveRepository			=	$executiveRepository;
		$this->executiveRowsRepository		=	$executiveRowsRepository;
		$this->advertisRepository			=	$advertisRepository;

		$this->businessRepository			=	$businessRepository;
	}

	/**
	 * 页面搜索条件组织
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function _seach($request){
		$where[]=['isshow','=',1];
		$company_id=$request->input('company_id');
		$partner_id=$request->input('partner_id');
		$product_id=$request->input('product_id');
		$status=$request->input('status');
		if(!empty($company_id)){
			$where[]=function ($query) use ($company_id) {
				$query->where('company_id','=',$company_id);
			};
		}

		if(!empty($partner_id)){
			$where[]=function ($query) use ($partner_id) {
				$query->where('partner_id','=',$partner_id);
			};
		}

		if(!empty($product_id)){
			$where[]=function ($query) use ($product_id) {
				$query->where('product_id','=',$product_id);
			};
		}

		if(!empty($status)){
			$status_ids=implode(',',$status);
			$where[]=function ($query) use ($status_ids) {
				$query->whereRaw(' status in ('.$status_ids.')');
			};
		}
		//增加客户范围限制  改为 能查看所有 但是只能编辑子
		/*$where[]=function ($query){
			$query->whereRaw(session('company_sql'));
		};*/
		return $where;
	}

	public function getUserExecutiveByCompany(){
		return $this->executiveRepository
				->applyWhere([
						function ($query) {
							$query->whereRaw(session('company_sql'));
						}
				])->all()->lists('id','id')->toArray();
	}

	/**
	 * 执行单列表
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getList(Request $request){
		//搜索区域的数据
		$viewData['company_id']=$this->companyRepository->getAllCompanyArray();//上游客户数据
		$where=$this->_seach($request);

		$company_id=$request->input('company_id');
		$partner_id=$request->input('partner_id');
		$product_id=$request->input('product_id');
		if(!empty($company_id) ){//&& !empty($partner_id)
			$viewData['partner_list']=$this->partnersRepository->getPartnerByCompanyId($company_id,$partner_id,'fun');
			$viewData['partner_id']=$partner_id;
		}
		if(!empty($company_id) ){//&& !empty($product_id)
			$viewData['product_list']=$this->productRepository->getProductListByCompanyId($company_id);
			$viewData['product_id']=$product_id;
		}

		//$viewData['business_cooperation']=$this->dictionaryRepository->returnList('business_cooperation');//合作方式
		$viewData['listdata'] = $this->executiveRepository
				->with(['company','partner','product','executive_rows'])
				->applyWhere($where)
				->applyOrder('id', 'desc')
				->paginate(20);
		$viewData['myExecution']=$this->getUserExecutiveByCompany();
		return view('admin.executive.list',$viewData);
	}

	/**
	 * 新增执行单页面  构建数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function createExecutive(){
		return view('admin.executive.edit',['id'=>0]);
	}

	/**
	 * 编辑执行单页面  构建数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function editExecutive($id){
		return view('admin.executive.edit',['id'=>$id]);
	}

	public function executiveGather(){
		$advertis=$this->advertisRepository->applyOrder('isshow')->all();
		return view('admin.executive.gather',['advertis'=>$advertis,'month'=>date('Y-m',time())]);
	}

	/**
	 * 编辑/新增页面公用数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $execute
	 * @return mixed
	 */
	public function getExecuteCommData($execute){
		$company_id=(isset($execute->company_id))?$execute->company_id:0;
		$partner_id=(isset($execute->partner_id))?$execute->partner_id:0;
		$product_id=(isset($execute->product_id))?$execute->product_id:0;
		$business_cooperation=(isset($execute->business_cooperation))?$execute->business_cooperation:'';

		$returnData['id']=(isset($execute->id))?$execute->id:'';

		$returnData['company_id']=$this->companyRepository->getAllCompanyArray($company_id);//上游客户数据
		$returnData['partner_id']=$this->partnersRepository->getPartnerByCompanyId($company_id,$partner_id,'fun');
		//合作方数据
		$returnData['product_id']=$this->productRepository->returnProductNameArray($product_id);//产品
		$returnData['pd_sname']='';
		$returnData['business_cooperation']=$this->dictionaryRepository
				->returnTypeArray('business_cooperation',$business_cooperation);//业务类型
		$returnData['target_type']=(isset($execute->target_type))?$execute->target_type:1;
		$returnData['gid_string']=(isset($execute->gid_string))?$execute->gid_string:'';
		$returnData['city_ids']=(isset($execute->city_ids))?$execute->city_ids:'';
		$returnData['pd_sname']=(isset($execute->pd_sname))?$execute->pd_sname:'';

		$returnData['advertis']=$this->advertisRepository->getAdvertisList($returnData['id']);

		$returnData['sell_type']=collect(Executive::$sell_type);
        $returnData['bz']=(isset($execute->bz))?$execute->bz:'';

		$returnData['status']=(isset($execute->status))?$execute->status:1;

		return $returnData;
	}

	/**
	 *	 新增/编辑执行单 生成tablejson
	 *
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $executive_id
	 * @param $type 需要显示的格式 如：详情页需要增加广告位规则一列
	 * @param bool|false $showOtherAdvertis  是否显示其他剩余空白广告位
	 * @return array
	 */
	public function getExecutiveTable($executive_id,$type='',$showOtherAdvertis=false){


		$rows=$this->executiveRowsRepository->with(['executive'])->applyWhere([
			['parent_id','=',$executive_id]
		])->applyOrder('month','asc')
		->all()
		->groupBy('month')
		->toArray();
		$executive=[];
		if(!empty($executive_id) ) {
			$executive=$this->executiveRepository->find($executive_id);
			//得到本次编辑的数据中的所有广告位ID；
			$advertis_ids = [];
			foreach ($rows as $month=>$row) {
				foreach($row as $r){
					$advertis_ids[$month][$r['advertis_id']] = $r['advertis_id'];
				}
			}
		}
		//通用表头 两行
		$header_comm[]=['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""];

		if($type=='detail') {
			$header_comm[] = ['value' => '广告规格', 'weight' => "bold", "color" => "", "background" => "", "align" => ""];
			$remarkList = $this->advertisRepository->getAdvertisRemarkList();
		}
		$header_comm[]=['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""];

		$delAdvertis=$this->advertisRepository->getDelAdvertisNameList();
		$table=[];
		foreach($rows as $month=>$tb){
			//检查是否是草稿 如果不是草稿则需要检测售罄
			if($executive->status!=1){
				$advertis_status=$this->advertisStatusData($month,$advertis_ids[$month]);
			}


			$team_table=[];
			$firstHeader=$header_comm;
			$m=$month;
			for($i=1;$i<=31;$i++){
				$firstHeader[]=['value'=>$m,'weight'=>"bold","color"=>"","background"=>"","align"=>""];
			}
			$secHeader=$header_comm;
			for($i=1;$i<=31;$i++){
				$secHeader[]=['value'=>$i,'weight'=>"bold","color"=>"","background"=>"","align"=>""];
			}
			$team_table[]=$firstHeader;
			$team_table[]=$secHeader;

			$advertis_act=[];
			foreach($tb as $d){
				$tbaleRow=[];
				$advertis_json_arr=json_decode($d['advertis_json'],true);
				if(isset($delAdvertis[$d['advertis_id']])){//检查是否属于已经删除过的广告位 如果是则更新显示json
					$advertis_json_arr['value']=$delAdvertis[$d['advertis_id']];
				}
				//1组织广告位
				$tbaleRow[]=$advertis_json_arr;
				if($type=='detail') {
					$tbaleRow[] = ['value' => $remarkList[$d['advertis_id']], 'weight' => "", "color" => "",
								"background" => "",	"align" => ""];
				}
				if($showOtherAdvertis) {
					$advertis_act[$d['advertis_id']] = $d['advertis_id'];
				}
				//2组织收买方式
				$tbaleRow[]=json_decode($d['sell_type']);
				//3组织31天的数据
				for($i=1;$i<=31;$i++){
					$day_cell=json_decode($d['d'.$i],true);
					if(isset($day_cell['value']) && !empty($day_cell['value'])){
						$tbaleRow[]=$day_cell;
					}else{
						if($executive->status!=1 && $advertis_status[$d['advertis_id']][$i-1]['value']==0){
							$day_cell['type']= "disabled";
						}
						$tbaleRow[]=$day_cell;
					}
				}
				$team_table[]=$tbaleRow;
			}
			//特殊功能单独功能 当需要显示其他空白广告位的时候这里查询剩余的广告 并显示
			if($showOtherAdvertis) {
				$otherAdvertis = $this->advertisRepository->applyWhere([
						['isshow', '=', 1],
						function ($query) use ($advertis_act) {
							if (!empty($advertis_act)) {
								$query->whereRaw(' id not in (' . implode(',', $advertis_act) . ')');
							}
						},
				])->all();
				foreach ($otherAdvertis as $d) {
					$tbaleRow = [];
					$tbaleRow[] = ['value' => $d->name];
					$tbaleRow[] = ['value' => ""];
					for ($i = 1; $i <= 31; $i++) {
						$tbaleRow[] = ['value' => ""];;
					}
					$team_table[] = $tbaleRow;
				}
			}
			$table[]=$team_table;
		}
		return $table;
	}

	/**
	 * 新增执行单加载数据接口
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function createExecutiveApi($id){
		$executive=[];
		if(!empty($id)){
			$executive=$this->executiveRepository->find($id);
		}

		$returnData=$this->getExecuteCommData($executive);//获得编辑新增页面公共数据
		$returnData['table']=$this->getExecutiveTable($id);
		return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
	}

	/**
	 * 执行单详情
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function detailExecutive($id,$showother=0){
		$executive=$this->executiveRepository->find($id);
		return view('admin.executive.detail',['id'=>$id,'showother'=>$showother,'executive'=>$executive]);
	}


	/**
	 * 执行单详情页 获取数据接口
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $id
	 * @param string $t=1(默认)  2（显示空白剩余广告位）
	 * @return string
	 */
	public function getDetailExecutiveApi($id,$t=0){
		$executive=[];
		if(!empty($id)){
			$executive=$this->executiveRepository->with('executive_rows')->find($id);
		}else{
			return json_encode(['status'=>'success','info'=>'','data'=>[]]);
		}

		$returnData['id']=$executive->id;
		$returnData['executive_key']=$executive->key;

		//DB::enableQueryLog();
		$productArr=$this->productRepository->getAllProductList();
		//print_r(DB::getQueryLog());
		//dd($productArr);
		$returnData['product_id']=$productArr[$executive->product_id];
		$productArr=null;

		$company=$this->companyRepository->getAllCompanyList($executive->company_id);
		$returnData['company_id']=$company[$executive->company_id];
		$company=null;

		$returnData['pd_sname']=$executive->pd_sname;

		$partners=$this->partnersRepository->getAllPartnerList();
		$returnData['partner_id']=$partners[$executive->partner_id];
		$partners=null;

		$returnData['executive_name']=	$executive->executive_rows[0]->month.'-'.$returnData['partner_id'].'-'.
										$returnData['product_id'].'-('.$returnData['executive_key'].')';

		$returnData['business_cooperation']=$this->dictionaryRepository
				->keyToValue('business_cooperation',$executive->business_cooperation);


		$returnData['target_type']=$executive->targettype_text;


		$returnData['status_text']=$executive->status_text;

		if($t==1){
			$showother=true;
		}else{
			$showother=false;
		}

		$returnData['table']=$this->getExecutiveTable($id,'detail',$showother);
		return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);
	}

	/**
	 * 保存执行单数据
	 * 检测当天广告位是否超过最大轮数
	 * 编辑：如果 合作方，客户 产品发生变化需要同步更新业务哪边的数据
	 *
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function storeExecutive(Request $request){
		$tableJsonData=$request->input('table');

		$tableData=json_decode($tableJsonData,true);
		$tableJsonData=null;


		$id			=$request->input('id');

		DB::beginTransaction();//事务开始
		$executive_main_arr=array_only($request->all(), [
				'product_id',
				'pd_sname',
				'company_id',
				'partner_id',
				'business_cooperation',
				'target_type',
				'gid_string',
				'city_ids',
				'status',
                'bz',
		]);

		$month_main	=$tableData[0][0][2]['value'];//当前月份或者主表的选择月份（用于生成执行单名称的组成）
		//编辑的数据操作
		if(isset($id) && !empty($id)){
			$execut=$this->executiveRepository->find($id);
            $executive_main_arr['updated_at']=date('Y-m-d H:i:s',time());
			if($execut->status==3){$executive_main_arr['status']=3;}

			$execut->update($executive_main_arr);

			//检查基础数据是否改变 如果 改变则更新关联业务那边的数据
			if(!empty($execut->business_id)){//同步数据
				$this->synBusinessData($execut,$executive_main_arr);
			}

			$execut_id=$id;
			//删除原有关联下的所有明细  然后重新保存
			$this->executiveRowsRepository->deleteRows($execut_id);

		}else {
			$key = $this->getExecutiveKey();
			$executive_main_arr['key'] = $key;

			//获得合作方名称信息
			$partner = $partners = $this->partnersRepository->getPartnerById($executive_main_arr['partner_id']);

			//获得产品名称信息
			$product = $this->productRepository->getProductById($executive_main_arr['product_id']);

			//拼接执行单名
			$executive_main_arr['name'] = $month_main . '-' . $partner->company_name . '-' . $product->name . '-(' . $key . ')';

			//保存执行单主表信息
			$execut = $this->executiveRepository->create($executive_main_arr);
			$execut_id = $execut->id;
		}
		if($executive_main_arr['status']==1){
			$this->storeDraft($tableData,$execut_id);
			DB::commit();
			return $this->ajaxSuccess('保存草稿成功！',['id'=>$execut_id]);
		}else {
			$advertisMaxRoundsArr = $this->advertisRepository->getAdvertisMaxRounds();
			//保存执行单 明细信息
			foreach ($tableData as $tb) {//循环table 会有本月和跨月两个table
				$month = $tb[0][2]['value'];//当前表的月份
				if (count($tb) <= 2) {
					continue;
				}//空表也会提交过来 前两行是表头数据
				foreach ($tb as $rk => $row) {//循环行
					if ($rk <= 1) {
						continue;
					}//跳过表头两行
					if (empty($row[0]['value']) && empty($row[1]['value'])) {
						continue;
					}
					if (!empty($row[0]['value']) && empty($row[1]['value'])) {
						DB::rollBack();
						return $this->ajaxError('广告位对应的售卖方式不能为空！');
					}
					if (empty($row[0]['value']) && !empty($row[1]['value'])) {
						DB::rollBack();
						return $this->ajaxError('广告位不能为空！');
					}
					$rowData = [];
					$advertis_id = $row[0]['id'];
					$rowData['parent_id'] = $execut_id;
					$rowData['advertis_id'] = $advertis_id;
					$rowData['advertis_json'] = json_encode($row[0]);
					$rowData['sell_type'] = json_encode($row[1]);
					$rowData['month'] = $month;
					$rowData['ishotgame'] = in_array($row[0]['value'], Advertis::$hotgames) ? 1 : 0;

					//处理每天对应的广告位数据
					$check_round_bl = true;
					for ($i = 2; $i <= 32; $i++) {
						$day = 'd' . ($i - 1);
						//$rowData[$day]	=	json_encode($row[$i]);
						$round_value = trim($row[$i]['value']);
						if (!empty($round_value)) {//检查格子里面是否填写了轮数 如果填写了轮数则需要检测是否超过最大轮数
							$where[] = ['advertis_id', '=', $advertis_id];
							$where[] = ['month', '=', $month];
							$rounds = $row[$i]['value'];
							if (!is_numeric($rounds)) {
								DB::rollBack();
								return $this->ajaxError('排期轮数必须为数字，保存失败!');
							}
							$this->checkAdvertisRounds($advertisMaxRoundsArr[$advertis_id], $where, $day, $round_value);
							if ($rowData['ishotgame']) {
								$h_where[] = ['ishotgame', '=', 1];
								$h_where[] = ['month', '=', $month];
								$this->checkAdvertisHotGamesRounds($h_where, $day, $round_value);
							}
							$check_round_bl = false;//表示该行找到填写了 轮数的格子
						}
						$rowData[$day] = json_encode($row[$i]);
					}
					if ($check_round_bl) {
						DB::rollBack();
						return $this->ajaxError('排期表中发现有未填写占坑轮数的广告位，保存失败!');
					}
					$executiveRow = $this->executiveRowsRepository->create($rowData);
				}
			}
			$rowsCount = $this->executiveRowsRepository->applyWhere([
					['parent_id', '=', $execut_id]
			])->all()->count();
			if (empty($rowsCount) || $rowsCount < 1) {
				DB::rollBack();
				return $this->ajaxError('排期表中没有任何数据，保存失败!');
			}

			DB::commit();
			return $this->ajaxSuccess('保存成功！', ['url' => route('admin.executive.list')]);
		}
	}

	/**
	 * 保存草稿数据  去除任何检测限制
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $tableData
	 * @param $execut_id
	 */
	public function storeDraft($tableData,$execut_id){
		DB::beginTransaction();//事务开始
		foreach($tableData as $tb){//循环table 会有本月和跨月两个table
			$month	=$tb[0][2]['value'];//当前表的月份
			if(count($tb)<=2){continue;}//空表也会提交过来 前两行是表头数据
			foreach($tb as $rk=> $row){//循环行
				if($rk<=1){continue;}//跳过表头两行
				if(empty($row[0]['value']) && empty($row[1]['value'])){continue;}
				$rowData=[];
				$advertis_id=$row[0]['id'];
				$rowData['parent_id']	=	$execut_id;
				$rowData['advertis_id']	=	$advertis_id;
				$rowData['advertis_json']	=json_encode($row[0]);
				$rowData['sell_type']		=json_encode($row[1]);
				$rowData['month']			=$month;
				$rowData['ishotgame']		=in_array($row[0]['value'],Advertis::$hotgames)?1:0;

				//处理每天对应的广告位数据
				for($i=2;$i<=32;$i++){
					$day='d'.($i-1);
					$rowData[$day]	=	json_encode($row[$i]);
				}
				$executiveRow=$this->executiveRowsRepository->create($rowData);
			}
		}
		DB::commit();
		return ;
	}

	/**
	 *  同步业务那边的 客户 合作方 产品 数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function synBusinessData($execut,$executive_main_arr){
		$updata=[];
		$updata['company_id']=$executive_main_arr['company_id'];
		$updata['partner_id']=$executive_main_arr['partner_id'];
		$updata['product_id']=$executive_main_arr['product_id'];
		if(!empty($updata)){
			DB::beginTransaction();//事务开始
			$business=$this->businessRepository->find($execut->business_id);
			$business->update($updata);
			DB::commit();
		}
		return ;
	}

	/**
	 * 检查广告位当天的轮数最大限制
	 * @author YangWei<yangwei@stnts.com>
	 * @param $max_rounds
	 * @param $where
	 * @param $day
	 * @param $round
	 * @throws BusinessException
	 */
	public function checkAdvertisRounds($max_rounds,$where,$day,$round){
		$executiveRows=$this->getTruthExecutiveRowsData($where);
		$real_rounds=0;
		foreach($executiveRows as $row){
			$rowArr=json_decode($row->$day,true);
			if(empty($rowArr['value'])){continue;}
			$real_rounds=$real_rounds+$rowArr['value'];
		}
		if(($real_rounds+$round)>$max_rounds){
			throw new BusinessException('发现有广告位轮数超过最大值!请检查！');
		}
		return ;
	}

	/**
	 *
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * $max_rounds=10  固定最大论述
	 * @param $where
	 * @param $day
	 * @param $round
	 * @throws BusinessException
	 */
	public function checkAdvertisHotGamesRounds($where,$day,$round){
		$max_rounds=10;
		$executiveRows=$this->getTruthExecutiveRowsData($where);
		$real_rounds=0;
		foreach($executiveRows as $row){
			$rowArr=json_decode($row->$day,true);
			if(empty($rowArr['value'])){continue;}
			$real_rounds=$real_rounds+$rowArr['value'];
		}
		if(($real_rounds+$round)>$max_rounds){
			throw new BusinessException('热门游戏当天最大轮数总和限制10轮！'.$day.'号的数据超过最大轮数！');
		}
		return ;
	}



	/**
	 * 删除执行单
	 *
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function deleteExecutive($id){
		DB::beginTransaction();//事务开始

		$executive=$this->executiveRepository->find($id);
		$business_id=$executive->business_id;
		$up['isshow']=0;
		$up['business_id']=0;
		$up['status']=1;
		$executive->update($up);

		if($business_id>0){
			$business=$this->businessRepository->find($business_id);
			$business->update(['executive_id'=>0]);
		}
		DB::commit();
		return $this->ajaxSuccess('删除成功！');
	}


	/**
	 * 得到当前排期key
	 * 规则：0100 开始的 字符串
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getExecutiveKey(){
		$last_index=$this->executiveRepository->all()->count();
		$last_index=($last_index+1)+100;

		$len=strlen($last_index);
		$addlen=0;
		if($len<4){
			$addlen=4-$len;
		}
		if($addlen>0){
			return str_repeat('0',$addlen).$last_index;
		}
		return $last_index;
	}

	/**
	 * 下单 根据执行单下拉框 获得关联数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param Request $request
	 * @return string
	 */
	public function getExecutiveCompanyDatas(Request $request){
		$executive_id=$request->input('id');
		$type=$request->input('type');
		if($type==2){
			return $this->getDetailExecutiveApi($executive_id);
		}
		$executive=$this->executiveRepository->find($executive_id);

		$returnData['company_id']=$this->companyRepository->getAllCompanyArray($executive->company_id);

		$returnData['partner_id']=$this->partnersRepository
				->getPartnerByCompanyId($executive->company_id,$executive->partner_id,'fun');

		$returnData['product_id']=$this->productRepository->returnProductNameArray($executive->product_id);

		return json_encode(['status'=>'success','info'=>'','data'=>$returnData]);

	}

	/**
	 * 获得大排期的数据
	 * 可优化:冗余 “产品简称”  增加汇总表等等
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getGatherApi(Request $request){
		$month=$request->input('date');
		$ad_type=$request->input('ad_type');

		$status=$request->input('status');

		if(empty($month)){
			$month=date('Y-m',time());
		}
		//查询所有的广告位
		$ad_w=[];
		if(!empty($ad_type)){
			$ad_w[]=function ($query) use ($ad_type) {
				$query->whereRaw('id in ('.$ad_type.')');
			};
		}
		$advertis=$this->advertisRepository
				->applyWhere($ad_w)
				->applyOrder('sequence','asc')
				->all();

		$table=[];
		//通用表头 两行
		/*$header_comm_1[]=['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""];
		for($i=1;$i<=31;$i++){
			$header_comm_1[]=['value'=>$month,'weight'=>"bold","color"=>"","background"=>"","align"=>""];
		}
		$table[]=	$header_comm_1;*/

		$header_comm_2[]=['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""];
		for($i=1;$i<=31;$i++){
			$header_comm_2[]=['value'=>$i,'weight'=>"bold","color"=>"","background"=>"","align"=>""];
		}
		$table[]=	$header_comm_2;

        $sell_type_color_arr=Executive::$gather_sell_type_color;//大排期中的字体颜色
        
		foreach($advertis as $ad){//循环广告位
			//按照广告位查询广告位所属执行单
			$w=[];
			$w[]=['advertis_id','=',$ad->id];
			$w[]=['month','=',$month];
			if(!empty($status)){
				$w[]=function ($query) use ($status) {
					$query->whereHas('executive', function ($subQuery) use ($status) {
						$subQuery->where('status','=',$status);
					});
				};
			}
			//得到所属广告位执行单中的所有明细
			$executiveRows=$this->getTruthExecutiveRowsData($w)->toArray();

			//广告位占行
			$temp_table=[];//某个广告位的行数table 只填充广告位名称 其他数据填充默认值
			for($i=0;$i<$ad->max_rounds;$i++) {//循环广告位轮数
				$row=[];
				$name=$ad->name;
				if($ad->isshow!=1){
					$name=$name.'(已删除)';
				}
				$row[]=['value' => $name, 'weight' => "bold", "color" => "", "background" => "",
						"align" => "", 'id' => 0];
				for($d=1;$d<=31;$d++){
					$row[]=['value'=>''];
				}
				$temp_table[]=$row;
			}

			//广告位填充数据  循环执行单中某个广告位每天的数据 得到轮数 并填充到上面已经生成好的广告位行数table中
			foreach($executiveRows as $key=>$executive_row){
				for($d=1;$d<=31;$d++){
					$d_row=json_decode($executive_row['d'.$d],true);
                    $sell_type_color='';
                    $sell_type_row=json_decode($executive_row['sell_type'],true);
                    $sell_type_color=$sell_type_color_arr[$sell_type_row['value']];//字体颜色
					if(!empty($d_row['value'])){
                        //if($executive_row['id']==1109){
                           // dd($executive_row);
                       // }
						$d_row_new = $d_row;
						$d_row_new['value'] =$executive_row['executive']['pd_sname'];
						$d_row_new['id'] = $executive_row['id'];
                        $d_row_new['color'] = $sell_type_color;
                        $d_row_new['background'] = "RGB( 255, 255, 255)";

						//检测广告位轮数中是否有0.5轮
						$check_index=strpos($d_row['value'],'.');
						if($check_index>0){//如果有半轮 则先填充整数轮的数据
							$in_max=substr($d_row['value'],0,$check_index);
						}else{
							$in_max=$d_row['value'];
						}
						//处理整数轮的数据
						if($in_max>0) {
							for ($in = 0; $in < $in_max; $in++) {
								$act_index = $this->setTableDay($temp_table, $in, $d);//给广告位每天的轮数排序
								$temp_table[$act_index][$d] = $d_row_new;
							}
						}
						//处理完整数轮的数据 开始处理半轮的数据
						if($check_index>0){
							//1.检测本广告位 当天是否已经有占本轮的数据
							$check_return=true;
							foreach($temp_table as $act_k1=>$day_arr){
								foreach($day_arr as $day_k2=>$day_row){
									//追加前面已经出现的半格 合并数据
									if($d==$day_k2 && isset($day_row['half']) && count($day_row['half'])==1){
										$temp_table[$act_k1][$day_k2]['half'][]=$d_row_new;
										$check_return=false;
										break;
									}
								}
							}
							//处理第一次出现的办格
							if($check_return){
								$act_index = $this->setTableDay($temp_table, 0, $d);//给广告位每天的轮数排序
								$temp_table[$act_index][$d]="";
								$temp_table[$act_index][$d]['half'][]=$d_row_new;
							}
						}

					}
				}
			}
			if(empty($table)){
				$table=$temp_table;
			}else{
				$table=array_merge($table,$temp_table);
			}
		}
		$return['table'][]=$table;

		return json_encode(['status'=>'success','info'=>'','data'=>$return]);
	}

    /**
     * 大排期导出数据到excel
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function executiveGatherToExcel(Request $request,ExecutiveInfo $executiveInfo, ExcelMaker $excelMaker){
        $btime=$request->input('btime');
        //$etime=$request->input('etime');
        //表头
        $headers = [
            '日期',
            '广告位',
            '客户名称',
            '合作方名称',
            '合作方式',
            '产品名称',
            '售卖方式',
            '轮数',
            '排期id',
            '业务编号',
            '更新时间',
        ];
        $excel = $excelMaker->makeExcel($headers, $executiveInfo->getDataBySetTime($btime));
        $excel->download('xls');


    }

	/**
	 * 递归 给广告位每天的轮数排序填充
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $temp_table
	 * @param $n
	 * @return mixed
	 */
	public function setTableDay($temp_table,$n,$d){
		if(!empty($temp_table[$n][$d]['value']) || !empty($temp_table[$n][$d]['half'])){
			return $this->setTableDay($temp_table,$n+1,$d);
		}else{
			return $n;
		}
	}

	/**
	 * 获得大排期格子上的详细信息
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getGatherToopApi(Request $request){
		$id	=$request->input('id');
		$executive=$this->executiveRowsRepository->with([
				'executive',
				'executive.product',
				'executive.partner',
				'executive.company'])
				->find($id);
		$advertis_arr=json_decode($executive->advertis_json,true);
		$sell_type_arr=json_decode($executive->sell_type,true);
		$arr=[
				'company_id'=>$executive->executive->company->company_name,
				'pd_sname'=>$executive->executive->pd_sname,
				'product_id'=>$executive->executive->product->name,
				'partner_id'=>$executive->executive->partner->company_name,
				'business_cooperation'=>$executive->executive->business_cooperation,
				'sell_type'=>$sell_type_arr['value'],
				'advertis_name'=>$advertis_arr['value'],
				'dtime'=>$executive->month,
				'status_text'=>$executive->executive->status_text,
				'executive_key'=>$executive->executive->key,
		];
		return json_encode(['status'=>'success','info'=>'','data'=>$arr]);
	}


	/**
	 * 获得剩余广告位数量
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getSurplusAdvertis(Request $request){
		$month=$request->input('date');
		if(empty($month)){
			$month=date('Y-m',time());
		}
		$advertis=$this->advertisRepository->applyOrder('isshow')->all();

		$table=[];

		//通用表头 两行
		$header_comm_1[]=['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""];
		$header_comm_1[]=['value'=>'广告规格','weight'=>"bold","color"=>"","background"=>"","align"=>""];
		for($i=1;$i<=31;$i++){
			$header_comm_1[]=['value'=>$month,'weight'=>"bold","color"=>"","background"=>"","align"=>""];
		}
		$table[]=	$header_comm_1;

		$header_comm_2[]=['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""];
		$header_comm_2[]=['value'=>'广告规格','weight'=>"bold","color"=>"","background"=>"","align"=>""];
		for($i=1;$i<=31;$i++){
			$header_comm_2[]=['value'=>$i,'weight'=>"bold","color"=>"","background"=>"","align"=>""];
		}
		$table[]=	$header_comm_2;

		foreach($advertis as $ad){//循环广告位
			//查询广告位所属执行单
			$w=[];
			$w[]=['advertis_id','=',$ad->id];
			$w[]=['month','=',$month];
			$executiveRows=$this->getTruthExecutiveRowsData($w)->toArray();
			$row=[];
			$row[]=['value' => $ad->name, 'weight' => "", "color" => "", "background" => "",
					"align" => "", 'id' => 0];
			$row[]=['value' => $ad->remark, 'weight' => "", "color" => "", "background" => "",
					"align" => "", 'id' => 0];
			$max_rounds=$ad->max_rounds;

			//循环执行单中某个广告位每天的数据 得到轮数 并填充到上面已经生成好的广告位行数table中
			$day_use_rounds=[];
			foreach($executiveRows as $key=>$executive_row){
				for($d=1;$d<=31;$d++){
					$d_row=json_decode($executive_row['d'.$d],true);
					if(!empty($d_row['value'])){
						if(isset($day_use_rounds[$d])){
							$day_use_rounds[$d]=$day_use_rounds[$d]+$d_row['value'];
						}else{
							$day_use_rounds[$d]=$d_row['value'];
						}
					}
				}
			}

			for($d=1;$d<=31;$d++){
				$last_rounds='';
				if(isset($day_use_rounds[$d])){
					$last_rounds=$max_rounds-$day_use_rounds[$d];
				}
				if($last_rounds===0){
					$last_rounds="";
				}
				$row[]=['value' =>$last_rounds];
			}
			$table[]=$row;
		}
		$return['table'][]=$table;

		return json_encode(['status'=>'success','info'=>'','data'=>$return]);
	}

	/**
	 * 查询广告位是否售罄
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getAdvertisStatusApi(Request $request){
		$id=$request->input('id');
		$month=$request->input('date');
		$advertisStatusArr=$this->advertisStatusData($month,$id);
		$return_arr=[];
		if(!empty($advertisStatusArr)){
			$return_arr=$advertisStatusArr[$id];
		}

		return json_encode(['status'=>'success','info'=>'','data'=>$return_arr]);
	}

	/**
	 * 查询单个或多个广告是否售罄
	 * $moth 查询所属月份
	 * $id 查询某个/多个广告位（数组）
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function advertisStatusData($month='',$id=''){
		//1得到广告位的最大轮数
		$maxRound=$this->advertisRepository->getAdvertisMaxRounds($id);
		//2得到相关的数据行
		$executiveRowsWhere[]=['month','=',$month];
		/*$executiveRowsWhere[]=function ($query)  {
			$query->whereHas('executive', function ($subQuery) {
				$subQuery->where('isshow','=',1);
			});
		};*/
		/*$executiveRowsWhere[]=function ($query)  {
			$query->whereHas('executive', function ($subQuery) {
				$subQuery->where('status','>',1);
			});
		};*/
		if(is_array($id)){
			$executiveRowsWhere[]=function ($query) use($id) {
				$query->whereRaw(' advertis_id in ('.implode(',',$id).')');
			};
		}else{
			$executiveRowsWhere[]=['advertis_id','=',$id];
		}
		/*$executiveRows=$this->executiveRowsRepository
				->with(['executive'])
				->applyWhere($executiveRowsWhere)->all();*/

		$executiveRows=$this->getTruthExecutiveRowsData($executiveRowsWhere);


		$table_cell=[];
		foreach($executiveRows as $row){
			for($i=1;$i<=31;$i++){
				$cell_arr=json_decode($row['d'.$i],true);
				if(isset($cell_arr['value']) && !empty($cell_arr['value'])){
					$cell_value=$cell_arr['value'];
				}else{
					$cell_value=0;
				}
				if(isset($table_cell[$row['advertis_id']][$i])){
					$table_cell[$row['advertis_id']][$i]=$table_cell[$row['advertis_id']][$i]+$cell_value;
				}else{
					$table_cell[$row['advertis_id']][$i]=$cell_value;
				}
			}
		}
		$return_arr=[];
		if(!empty($table_cell)){
			foreach($table_cell as $adid=>$re){
				foreach($re as $d=>$v){
					$maxRoundNum=$maxRound;
					if(is_array($maxRound)){
						$maxRoundNum=$maxRound[$adid];
					}
					if($v>=$maxRoundNum){
						$return_arr[$adid][]=['key'=>$d,'value'=>0];
					}else{
						$return_arr[$adid][]=['key'=>$d,'value'=>1];
					}
				}
			}
		}
		return $return_arr;
	}

	/**
	 * 通用 获得执行单明细数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 */
	public function getTruthExecutiveRowsData($where){
		$where[]=function ($query)  {
			$query->whereHas('executive', function ($subQuery) {
				$subQuery->where('isshow','=',1);
			});
		};
		$where[]=function ($query)  {
			$query->whereHas('executive', function ($subQuery) {
				$subQuery->where('status','>',1);
			});
		};
		return $this->executiveRowsRepository->with(['executive'])
				->applyWhere($where)
				->applyOrder('id')
				->all();
	}


}