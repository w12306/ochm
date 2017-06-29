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
use Admin;
use App\Services\Admin\ActionLog;
use App\Services\ExcelMaker;
/**
 * 独立支出管理
 *
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class StandExpensesController extends Controller {
    use CommonResponse;

    protected $loginUser;

    protected $dictionaryRepository;

    protected $underPartnersRepository;

    protected $standExpensesModel;

    protected $standExpensesRepository;

    protected $actionLog;
    /**
     *
     */
    public function __construct(AdminGuard $loginUser,
                                DictionaryRepository $dictionaryRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                StandExpensesModel $standExpensesModel,
                                StandExpensesRepository $standExpensesRepository,
                                ActionLog $actionLog
                             ) {
        $this->loginUser = $loginUser->user();
        $this->underPartnersRepository=$underPartnersRepository;
        $this->dictionaryRepository =$dictionaryRepository;
        $this->standExpensesModel   =$standExpensesModel;
        $this->standExpensesRepository=$standExpensesRepository;
        $this->actionLog=$actionLog;
    }

    private function seach($request){
        $where=[];

        $under_partner      =$request->get('under_partner');//下游合作方
        $expend_type        =$request->get('expend_type');//支出类型

        if(!empty($under_partner)){
            $where[] = function ($query) use ($under_partner) {
                $query->whereRaw(' under_partner_id in ('.$under_partner.')');
            };
        }
        if(!empty($expend_type)){
            $where[] = function ($query) use ($expend_type) {
                $query->whereIn('expenses_type' ,$expend_type );
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

        //支出类型
        $viewData['expenses_type']=$this->dictionaryRepository->returnList('expenses_type');

        $where=$this->seach($request);

        $viewData['listdata']=$this->standExpensesRepository
                                            ->with(['under_partner',
                                                    'under_partner.undercompany',
                                                    'stand_payments',
                                            ])
                                            ->applyWhere($where)
                                            ->applyOrder('id', 'desc')
                                            ->paginate(20);

        return view('admin.stand-expenses.list', $viewData);
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

        $res=$this->standExpensesRepository
                ->with(['under_partner',
                        'under_partner.undercompany',
                        'stand_payments'])
                ->applyWhere($where)
                ->applyOrder('id', 'desc')
                ->all();

        //表头
        $headers = [
                "支出单号" ,
                "支出类型" ,
                "下游客户"  ,
                "下游合作方"  ,
                "下游合作形式"  ,
                "合同编号"  ,
                "付款状态"  ,
                "支出金额"  ,
                "备注"  ,
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
            foreach($data->under_partner->undercompany as $cv){
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
                    $data->expenses_key,
                    $expenses_typeArr[$data->expenses_type],
                    $under_company,
                    $data->under_partner->company_name,
                    $data->cooperation_type,
                    $data->contract_key,
                    $payment_status,
                    $data->amount,
                    $data->remark,
                    $status,
            ];
        });
        $excel = $excelMaker->makeExcel($headers, $rows);
        $excel->download('xls');
    }


    /**
     *录入支出信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function createStandExpenses($expenses_id="") {

        //加载上游合作方数据
        //$viewData['partnerData']=$this->partnersRepository->all();
        //加载下游合作方数据
        $viewData['under_partnerData']=$this->underPartnersRepository->all();
        //加载下游合作方数据
        $viewData['cooperation']=$this->dictionaryRepository->returnList('cooperation_type');
        //加载分成费用
        $viewData['expenses_type']=$this->dictionaryRepository->returnTypeArray('expenses_type');

        //编辑的情况下 加载发票信息
        if(!empty($expenses_id)){
            $viewData['expensesData']=$this->standExpensesModel->where('id','=',$expenses_id)->first();
        }

        return view('admin.stand-expenses.edit', $viewData);
    }

    /**
     * 保存录入支出信息
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function storeExpenses(Request $request){



        $storeData['expenses_type']          =$request->get('expenses_type');
        $storeData['payment_time']           =$request->get('payment_time');
        $storeData['cooperation_type']       =$request->get('cooperation_type');
        $storeData['contract_key']           =$request->get('contract_key');
        $storeData['under_partner_id']       =$request->get('under_partner_id');
        $storeData['amount']                 =$request->get('amount');
        $storeData['contract_btime']         =$request->get('btime');
        $storeData['contract_etime']         =$request->get('etime');
        $storeData['remark']                 =$request->get('remark');

        $expenses_id=$request->get('id');//编辑的时候会有iD传回
        if(!empty($expenses_id)){
            if($this->updateExpenses($storeData,$expenses_id)){
                return $this->ajaxSuccess('修改支出成功！',['url'=>route('admin.stand-expenses.list-expenses')]);
                exit;
            }
        }


        DB::beginTransaction();//事务开始
        //保存支出数据
        $expenses=$this->standExpensesModel->create($storeData);
        $expenses_id=$expenses->id;

        //更新支出单号
        $updateData['expenses_key']     =static::makeNumber($expenses_id,'SZC');
        $expenses=$this->standExpensesRepository->update($expenses_id,$updateData);

        $this->actionLog->log('stand-expenses.create',
                Admin::user()->id,
                [  'id'         =>$expenses_id,
                   'amount'     =>$storeData['amount'] ,
                ]
        );

        DB::commit();
        return $this->ajaxSuccess('录入独立支出成功！',['url'=>route('admin.stand-expenses.list-expenses')]);
    }

    /**
     *更新发票信息  只对填写的发票金额/发票基础信息修改
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updateExpenses($storeData,$expenses_id){

        DB::beginTransaction();//事务开始
        //保存支出主体数据
        $this->standExpensesModel->where('id','=',$expenses_id)->update($storeData);

        $this->actionLog->log('stand-expenses.edit',
                Admin::user()->id,
                [  'id'         =>$expenses_id,
                   'amount'     =>$storeData['amount'] ,
                ]
        );

        DB::commit();
        return true;

    }

    /**
     *删除支出
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteExpenses(Request $request){
        $expenses_id=$request->get('id');

        DB::beginTransaction();//事务开始
        //删除支出 软删除
        $this->standExpensesRepository->deleteStandExpenses($expenses_id);
        $this->actionLog->log('stand-expenses.delete',
                Admin::user()->id,
                [  'id'         =>$expenses_id,
                   'amount'     =>0 ,
                ]
        );
        DB::commit();
        return $this->ajaxSuccess('删除支出成功！',['url'=>route('admin.stand-expenses.list-expenses')]);
    }

}
