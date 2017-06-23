<?php

	namespace App\Http\Controllers\Admin;

	use App\Exceptions\BusinessException;
	use App\Models\Advancecash;
	use App\Models\BackcashModel;
	use App\Models\Contract;
	use App\Models\ExpensesModel;
	use App\Models\InvoiceModel;
	use App\Models\Payment;
	use App\Repositories\StandPaymentRepository;
	use App\Services\Admin\UploadServiceManager;
	use Illuminate\Database\Eloquent\ModelNotFoundException;
	use Illuminate\Foundation\Application;
	use Illuminate\Http\Request;
	use Bican\Roles\Models\Role;
	use Bican\Roles\Models\Permission;
	use App\Traits\Controller\CommonResponse;
	use App\Repositories\Criterias\LatestCriteria;
	use Illuminate\Database\Eloquent\Model;
	use App\Models\AdminUser;
	use App\Repositories\AdminUserActionLogRepository;
	use App\Repositories\AdminUserRepository;
	use App\Repositories\AdminUserTeamRepository;
	use App\Repositories\DictionaryRepository;
	use App\Repositories\BusinessRepository;
	use App\Repositories\DeliveryRepository;
	use App\Repositories\ContractRepository;
	use App\Repositories\BadcashRepository;
	use App\Repositories\EarnestcashRepository;
	use App\Repositories\ProductRepository;
	use App\Repositories\CompanyRepository;
	use App\Repositories\UnderCompanyRepository;
	use App\Repositories\PartnersRepository;
	use App\Repositories\UnderPartnersRepository;
	use App\Repositories\StandExpensesRepository;
	use DB;
	use Admin;
	use Illuminate\Pagination\LengthAwarePaginator;
	use Illuminate\Support\Str;
	use Common\Packages\SimpleVerificationCode\Code as VerificationCode;

	/**
	 *
	 * @author  AaronLiu <liukan0926@stnts.com>
	 * @package App\Http\Controllers\Admin
	 */
	class TestController extends Controller
	{




		public function __construct()
		{

		}

		function  addjson(){
			$arr=[
					"company_id"=>[
							[ "key"=>1,"value"=>"4399","s"=> 0],
							[ "key"=>1,"value"=>"冰川网络" ,"s"=> 0],
							[ "key"=>1,"value"=> "盛大网络","s"=> 0],
							[ "key"=>1,"value"=> "三七玩","s"=> 0],

					],
					'partner_id'=>[],
					'prduct_id'=>[],
					'pd_sname'=>'名',
					'business_cooperation'=>[
							['key'=>'HKD', 'value'=>'HKD','s'=>0],
							['key'=>"FRD", 'value'=>'FRD','s'=>1],
							['key'=>"CHK", 'value'=>'CHK','s'=>0],
							['key'=>"DYU", 'value'=>'DYU','s'=>0],
					],
					'target_type'=>'3',
					'gid_string'=>'1006458,56654522',
					'city_ids'=>'',
					'advertis'=>[
							['key'=>'1', 'value'=>'客户端推送'],
							['key'=>"2", 'value'=>'右下角弹窗'],
							['key'=>"3", 'value'=>'人气新游'],
							['key'=>"4", 'value'=>'游戏退弹'],
					],
					'sell_type'=>[
							['key'=>'1', 'value'=>'购买'],
							['key'=>"2", 'value'=>'配送'],
							['key'=>"3", 'value'=>'框架'],
							['key'=>"4", 'value'=>'额外支持'],
					],
					'table'=>[
							[
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'3','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'4','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'5','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'6','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'7','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'8','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'9','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'10','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'11','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'12','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'13','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'14','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'15','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'16','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'17','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'18','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'19','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'20','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'21','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'22','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'23','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'24','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'25','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'26','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'27','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'28','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'29','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'30','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'31','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'客户端推送', 'id'=>1,'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'购买','id'=>1,'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'右下角弹窗','id'=>2,'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'配送','id'=>2,'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									]

							]
					]

			];

			echo $this->decodeUnicode(json_encode($arr));
		}

		/**
		 *新增编辑广告位
		 * @author YangWei<yangwei@stnts.com>
		 *
		 */
		public  function add_advjson(){
			$arr=[
					'name'=>'广告位名称',
					'code'=>'',
					'max_rounds'=>1,
					'usually_price'=>2.2,
					'weekend_price'=>1,
					'usually_discount'=>1,
					'weekend_discount'=>1,
					'usually_value'=>1,
					'weekend_value'=>1,
					'remark'=>'',
			];
			echo $this->decodeUnicode(json_encode($arr));
		}

		/**
		 *      执行单详情接口数据
		 * @author YangWei<yangwei@stnts.com>
		 *
		 */
		public  function executive_info(){
			$arr=[
					'id'=>122,
					'executive_key'=>'执行单排期id',
					'executive_name'=>'排期名称',
					'product_id'=>'产品名称',
					'pd_sname'=>'产品简称',
					'partner_id'=>"合作方名称",
					'business_cooperation'=>"合作方式",
					'target_type'=>'区域定向',
					'status_text'=>'执行状态',
					'table'=>[
							[
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'广告规格','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'广告规格','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'3','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'4','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'5','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'6','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'7','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'8','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'9','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'10','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'11','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'12','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'13','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'14','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'15','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'16','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'17','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'18','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'19','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'20','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'21','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'22','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'23','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'24','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'25','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'26','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'27','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'28','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'29','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'30','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'31','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'客户端推送', 'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'48000家网吧（多少M以内）','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'购买','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'客户端推送222', 'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'60*60  fla.swf（推荐swf）','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'购买','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									]

							]
					]

			];

			echo $this->decodeUnicode(json_encode($arr));
		}

		/**
		 *   大排期接口数据
		 * @author YangWei<yangwei@stnts.com>
		 *
		 */
		public  function executive_all(){
			$arr=[
					'table'=>[
							[
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'3','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'4','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'5','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'6','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'7','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'8','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'9','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'10','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'11','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'12','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'13','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'14','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'15','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'16','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'17','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'18','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'19','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'20','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'21','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'22','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'23','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'24','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'25','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'26','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'27','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'28','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'29','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'30','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'31','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'更新位', 'weight'=>"bold","color"=>"","background"=>"",
													"align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'D2', 'id'=>'11','weight'=>"bold","color"=>"",
													"background"=>"",
													"align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','id'=>'12','weight'=>"bold","color"=>"","background"=>"",
													"align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],

											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'更新位', 'weight'=>"bold","color"=>"","background"=>"",
													"align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'FGH', 'id'=>'11','weight'=>"bold","color"=>"",
													"background"=>"",
													"align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','id'=>'12','weight'=>"bold","color"=>"","background"=>"",
													"align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],

											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','id'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],

							]
					]

			];

			echo $this->decodeUnicode(json_encode($arr));
		}

		/**
		 * 大排期中的详细信息
		 * @author YangWei<yangwei@stnts.com>
		 *
		 */
		public  function adtoop(){
			$arr=[
					'company_id'=>'客户名称',
					'pd_sname'=>'产品简称',
					'product_id'=>'',
					'partner_id'=>"",
					'business_cooperation'=>'',
					'sell_type'=>'',
					'advertis_name'=>'广告位',
					'dtime'=>'投放时间',
					'status_text'=>'wwww',
					'executive_key'=>'wwww',

			];
			echo $this->decodeUnicode(json_encode($arr));
		}


		/**
		 * 创建业务时 选择执行单后的数据请求
		 * @author YangWei<yangwei@stnts.com>
		 *
		 */
		public   function business_select_executive_json1(){
			$arr=[
					"company_id"=>[
							[ "key"=>1,"value"=>"4399","s"=> 0],
							[ "key"=>1,"value"=>"冰川网络" ,"s"=> 0],
							[ "key"=>1,"value"=> "盛大网络","s"=> 1],
							[ "key"=>1,"value"=> "三七玩","s"=> 0],

					],
					'partner_id'=>[
							[ "key"=>1,"value"=>"合作方1","s"=> 0],
							[ "key"=>1,"value"=>"合作方2" ,"s"=> 0],
							[ "key"=>1,"value"=> "合作方3","s"=> 1],
							[ "key"=>1,"value"=> "合作方4","s"=> 0],
					],
					'product_id'=>[
							[ "key"=>1,"value"=>"产品1","s"=> 0],
							[ "key"=>1,"value"=>"产品2" ,"s"=> 0],
							[ "key"=>1,"value"=> "产品3","s"=> 1],
							[ "key"=>1,"value"=> "产品4","s"=> 0],
					]
			];
			echo $this->decodeUnicode(json_encode($arr));
		}

		/**
		 * 创建业务时 选择执行单后的数据请求
		 * @author YangWei<yangwei@stnts.com>
		 *
		 */
		public   function business_select_executive_json2(){
			$arr=[
					'id',
					'executive_key'=>'执行单排期id',
					'executive_name'=>'排期名称',
					'product_id'=>'产品名称',
					'pd_sname'=>'产品简称',
					'partner_id'=>"合作方名称",
					'business_cooperation'=>"合作方式",
					'target_type'=>'区域定向',
					'status_text'=>'执行状态',
					'table'=>[
							[
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'广告规格','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2016年4月','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'广告位','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'广告规格','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'售卖方式','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'3','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'4','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'5','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'6','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'7','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'8','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'9','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'10','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'11','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'12','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'13','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'14','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'15','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'16','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'17','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'18','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'19','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'20','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'21','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'22','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'23','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'24','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'25','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'26','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'27','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'28','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'29','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'30','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'31','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'客户端推送', 'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'48000家网吧（多少M以内）','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'购买','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									],
									[
											['value'=>'客户端推送222', 'weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'60*60  fla.swf（推荐swf）','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'购买','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'2','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'1','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
											['value'=>'','weight'=>"bold","color"=>"","background"=>"","align"=>""],
									]

							]
					]

			];
			echo $this->decodeUnicode(json_encode($arr));
		}


		function getAdStatusJson(){
			$json=[
				['key'=>1,'value'=>1],
					['key'=>2,'value'=>1],
					['key'=>3,'value'=>1],
					['key'=>4,'value'=>1],
					['key'=>5,'value'=>1],
					['key'=>6,'value'=>1],
					['key'=>7,'value'=>0],
					['key'=>8,'value'=>1],
					['key'=>9,'value'=>1],
					['key'=>10,'value'=>0],
					['key'=>11,'value'=>1],
					['key'=>12,'value'=>1],
					['key'=>13,'value'=>0],
					['key'=>14,'value'=>1],
					['key'=>15,'value'=>1],
					['key'=>16,'value'=>1],
					['key'=>17,'value'=>1],
					['key'=>18,'value'=>1],
					['key'=>19,'value'=>1],
					['key'=>20,'value'=>1],
					['key'=>21,'value'=>1],
					['key'=>22,'value'=>1],
					['key'=>23,'value'=>1],
					['key'=>24,'value'=>1],
					['key'=>25,'value'=>1],
					['key'=>26,'value'=>1],
					['key'=>27,'value'=>1],
					['key'=>28,'value'=>1],
					['key'=>29,'value'=>1],
					['key'=>30,'value'=>1],
					['key'=>11,'value'=>1],
			];
			echo $this->decodeUnicode(json_encode($json));
		}

		/**
		 * 处理json转码
		 * Enter description here ...
		 * @param unknown_type $str
		 */
		function decodeUnicode($str){
			return preg_replace_callback('/\\\\u([0-9a-f]{4})/i', create_function('$matches',
					'return iconv("UCS-2BE","UTF-8",pack("H*", $matches[1]));'), $str);
		}

	}
