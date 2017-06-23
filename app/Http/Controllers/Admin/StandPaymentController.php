<?php
namespace App\Http\Controllers\Admin;

use App\Models\UnderPartnersModel;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use File;
use Validator;
use DB;
use App\Repositories\UnderPartnersRepository;
use App\Repositories\DictionaryRepository;
use Common\Packages\Admin\Contracts\Guard as AdminGuard;
use App\Models\StandExpensesModel;
use App\Repositories\StandExpensesRepository;
use App\Repositories\StandPaymentRepository;
use App\Models\StandPayment;
use App\Services\Admin\ActionLog;
use App\Services\ExcelMaker;
use Admin;
/**
 * 独立付款管理
 *
 * @author  YangweiWei <yangwei@stnts.com>
 */
class StandPaymentController extends Controller {
    use CommonResponse;

    protected $loginUser;

    protected $dictionaryRepository;

    protected $underPartnersRepository;

    protected $standExpensesModel;

    protected $standExpensesRepository;

    protected $standPaymentRepository;

    protected $standPayment;

    protected $actionLog;

    /**
     *
     */
    public function __construct(AdminGuard $loginUser,
                                DictionaryRepository $dictionaryRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                StandExpensesModel $standExpensesModel,
                                StandExpensesRepository $standExpensesRepository,
                                StandPaymentRepository $standPaymentRepository,
                                StandPayment $standPayment,
                                ActionLog $actionLog){
        $this->loginUser = $loginUser->user();
        $this->underPartnersRepository=$underPartnersRepository;
        $this->dictionaryRepository =$dictionaryRepository;
        $this->standExpensesModel   =$standExpensesModel;
        $this->standExpensesRepository=$standExpensesRepository;
        $this->standPaymentRepository=$standPaymentRepository;
        $this->standPaymen=$standPayment;
        $this->actionLog                     = $actionLog;
    }

    private function seach($request){
        $where=[];

        $btime              =$request->get('btime');//付款时间段
        $etime              =$request->get('etime');
        $under_partner      =$request->get('under_partner');//下游合作方
        $invoice_type       =$request->get('invoice_type');//发票类型


        //付款时间
        if(!empty($btime) && !empty($etime)){
            $where[] = function ($query) use ($btime,$etime) {
                $query->whereBetween('payment_time', [$btime, $etime]);
            };
        }
        //下游合作方
        if(!empty($under_partner) ){
            $where[] =function ($query) use ($under_partner) {
                $query->whereHas('stand_expenses',
                        function ($query) use ($under_partner) {
                            $query->whereRaw(' under_partner_id in ('.$under_partner.')');
                        });
            };
        }
        //发票类型
        if(!empty($invoice_type) ){
            $where[] = function ($query) use ($invoice_type) {
                $query->whereRaw(' invoice_type like "%'.$invoice_type.'%"');
            };
        }

        return $where;
    }
    /**
     * 支出查询列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request) {
        //加载下游合作方数据
        $viewData['under_partnerData']=$this->underPartnersRepository->all();
        $where=$this->seach($request);

        //支出类型
        $viewData['expenses_type']=$this->dictionaryRepository->returnList('expenses_type');

        $viewData['listdata']=$this->standPaymentRepository
                                            ->with(['stand_expenses',
                                                    'stand_expenses.under_partner',
                                                     'stand_expenses.under_partner.undercompany'
                                                    ]
                                            )->applyWhere($where)
                                            ->applyOrder('id', 'desc')
                                            ->paginate(20);
        return view('admin.stand-payment.list', $viewData);
    }

    /**
     * 列表导出Excel
     *
     * @param Request    $request
     * @param ExcelMaker $excelMaker
     */
    public function getExportExcel(Request $request, ExcelMaker $excelMaker)
    {
        //支出类型
        $expenses_typeArr=$this->dictionaryRepository->returnList('expenses_type');

        $where=$this->seach($request);

        $res=$this->standPaymentRepository
                ->with(['stand_expenses',
                                'stand_expenses.under_partner',
                                'stand_expenses.under_partner.undercompany'
                        ]
                )->applyWhere($where)
                ->applyOrder('id', 'desc')
                ->all();

        //表头
        $headers = [
                    "付款单号",
                    "付款金额",
                    "付款时间",
                    "发票金额",
					"发票编号",
					"发票类型",
					"支出金额（元）",
					"支出类型",
					"下游客户",
					"下游合作方",
					"下游合作形式",
					"备注",
					"状态"  ,
        ];
        //格式化数据
        $rows=[];
        $res->map(function ($data) use (&$rows,$expenses_typeArr){
            $status='正常';
            if($data->isshow==0){
                $status='已删除';
            }

            $under_company='';
            foreach($data->stand_expenses->under_partner->undercompany as $cv){
                $under_company .=$cv->company_name.',';
            }

            //计算支出单付款状态
            $payment_status='未付清';
            if(!empty($data->stand_payments)){
                $payment=0.00;
                foreach($data->stand_payments as $pv){
                    $payment=$payment+$pv->payment_amount;
                }
                if($payment>=$data->amount){
                    $payment_status='已付清';
                }
            }

            $rows[]=[
                    $data->payment_key,
                    $data->payment_amount,
                    $data->payment_time,
                    $data->invoice_amount,
                    $data->invoice_num,
                    $data->invoice_type,
                    $data->stand_expenses->amount,
                    $expenses_typeArr[$data->stand_expenses->expenses_type],
                    $under_company,
                    $data->stand_expenses->under_partner->company_name,
                    $data->stand_expenses->cooperation_type,
                    $data->remark,
                    $status,
            ];
        });
        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }


    /**
     *录入付款信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function createStandPayment($payment_id="") {

        //加载支出单信息
        $viewData['expensesData']=$this->standExpensesRepository->applyWhere([
			['isshow','=',1]
		])->all();

        //编辑的情况下 加载发票信息
        if(!empty($payment_id)){
            $viewData['paymentData']=$this->standPaymen->where('id','=',$payment_id)->first();
        }

        return view('admin.stand-payment.edit', $viewData);
    }

    /**
     * 保存录入支出信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function storePayment(Request $request){


        $storeData['payment_time']              =$request->get('payment_time');
        $storeData['payment_amount']            =$request->get('payment_amount');
        $storeData['invoice_num']               =$request->get('invoice_num');
        $storeData['invoice_amount']            =$request->get('invoice_amount');
        $storeData['invoice_type']              =$request->get('invoice_type');
        $storeData['expenses_id']               =$request->get('expenses_id');
        $storeData['remark']                    =$request->get('remark');

        $expenses=$this->standExpensesRepository->find($storeData['expenses_id']);
        if($storeData['payment_amount']>$expenses->amount){
            return $this->ajaxError('付款金额不能大于对应的支出金额！');
        }
        $payment_id=$request->get('id');//编辑的时候会有iD传回
        if(!empty($payment_id)){
            if($this->updatePayment($storeData,$payment_id)){
                return $this->ajaxSuccess('修改付款成功！',['url'=>route('admin.stand-payment.list')]);
                exit;
            }
        }


        DB::beginTransaction();//事务开始
        //保存独立付款数据
        $payment=$this->standPaymentRepository->create($storeData);
        //更新支付单号
        $updateData['payment_key']          =static::makeNumber($payment->id,'SFK');
        $this->standPaymentRepository->update($payment->id,$updateData);

        $this->actionLog->log('stand-payment-expenses.create',
                Admin::user()->id,
                [  'id'         =>$payment->id,
                   'amount'     =>$storeData['payment_amount']  ,
                ]
        );

        DB::commit();
        return $this->ajaxSuccess('录入独立付款成功！',['url'=>route('admin.stand-payment.list')]);
    }

    /**
     *更新发信息修改
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function updatePayment($storeData,$payment_id){

        DB::beginTransaction();//事务开始
        //保存支出主体数据
        $this->standPaymentRepository->update($payment_id,$storeData);
        $this->actionLog->log('stand-payment-expenses.edit',
                Admin::user()->id,
                [  'id'         =>$payment_id,
                   'amount'     =>$storeData['payment_amount'] ,
                ]
        );
        DB::commit();
        return true;

    }

    /**
     *删除支出
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function deletePayment(Request $request){
        $payment_id=$request->get('id');
        DB::beginTransaction();//事务开始
        //删除  软删除
        $this->standPaymentRepository->deleteStandPayment($payment_id);
        $this->actionLog->log('stand-payment-expenses.delete',
                Admin::user()->id,
                [  'id'         =>$payment_id,
                   'amount'     =>0 ,
                ]
        );
        DB::commit();
        return $this->ajaxSuccess('删除独立付款成功！',['url'=>route('admin.stand-payment.list')]);
    }

    /**
     *下拉框筛选独立支出单数据 更新级联数据
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getExpensesDataApi(Request $request){
        $expenses_id=$request->get('expenses_id');
        $data=$this->standExpensesRepository->with(['under_partner'])->applyWhere([['id','=',$expenses_id]])->all();
        $data=$data->toArray();
        $returnJsonData = ["status"=>"success","info" =>"",
                        "data"=>['amount' => $data[0]['amount'],
                        'under_partner' =>$data[0]['under_partner']['company_name'],
                        ]
        ];
        return json_encode($returnJsonData);
    }



}
