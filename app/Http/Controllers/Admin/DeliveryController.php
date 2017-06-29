<?php
namespace App\Http\Controllers\Admin;

use App\Repositories\BackcashInvoiceRepository;
use App\Repositories\BadcashRepository;
use App\Repositories\ExpensesDeliveyRepository;
use App\Repositories\InvoiceDeliveryRepository;
use App\Repositories\PaymentExpensesRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use App\Repositories\DeliveryRepository;
use App\Models\DeliveryModel;
use App\Repositories\BusinessRepository;
use App\Repositories\BusinessTeamRepository;
use App\Services\Admin\ActionLog;
use DB;
use Admin;
/**
 * 业务执行额管理
 * @author  YangweiWei <yangwei@foxmail.com>
 */
class DeliveryController extends Controller
{
    use CommonResponse;

    protected  $deliveryRepository;

    protected  $deliveryModel;

    protected  $businessRepository;

    protected  $businessTeamRepository;

    private    $actionLog;
    /**
     * 
     */
    public function __construct(DeliveryRepository $deliveryRepository,
                                DeliveryModel $deliveryModel,
                                BusinessRepository $businessRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                ActionLog $actionLog)
    {
        $this->deliveryRepository     =$deliveryRepository;
        $this->deliveryModel          =$deliveryModel;
        $this->businessRepository     =$businessRepository;
        $this->businessTeamRepository =$businessTeamRepository;
        $this->actionLog            =$actionLog;
    }

    /**
     *新增月执行额
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function storeDelivery(Request $request){
        $team_id  =$request->get('busid');//业务团队ID
        $id     =$request->get('id');//月执行额数据ID
        $businessTeamData=$this->businessTeamRepository->with('business')
                                                        ->find($team_id);
        if(!empty($id)){
                if($this->updateDelivery($businessTeamData,$request)){
                    return $this->ajaxSuccess('更新成功！',['token'=>csrf_token()]);exit;
                }
        }
        $data['team_id']        =   $team_id;
        $data['business_id']    =   $businessTeamData->business->id;
        $data['business_key']   =   $businessTeamData->business->business_key;
        $data['team']           =   $businessTeamData->team;
        $data['month']          =   $request->get('month');
        $data['amount']         =   $request->get('amount');
        if($this->deliveryRepository->checkDelivery($data['team_id'],$data['month'])>0){
            return $this->ajaxError('该月份已经添加过执行额！');exit;
        }
        if(!$this->deliveryRepository->
                checkDeliveryAmount($team_id,$data['amount'],$businessTeamData->active_amount)){
            return $this->ajaxError('警告:录入的执行总额已经超过了该小组的实际金额！');exit;
        }

        $delivery=$this->deliveryRepository->create($data);
        $delivery_id=$delivery->id;
        //记录日志
        $this->actionLog->log('delivery.create',
                Admin::user()->id,
                [  'id'    =>$delivery_id,
                   'month' => $data['month'],
                   'amount' => $data['amount'],
                   'business_key'  =>$data['business_key']   ,
                ]
        );
        return $this->ajaxSuccess('新增成功！',['token'=>csrf_token()]);
    }

    /**
     *更新月执行额
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function updateDelivery($businessTeamData,$request){
        $id         =$request->get('id');
        $month      =$request->get('month');
        $amount     =$request->get('amount');
        $delivery=$this->deliveryRepository->find($id);

        DB::beginTransaction();
        if($month!=$delivery->month){//如果修改后的月份发生变化 需要检测是否和其他月份冲突
            if($this->deliveryRepository->checkDelivery($businessTeamData->id,$month)>0){
                throw new BusinessException('该月份已经添加过执行额!');
            }
            $data['month']          =   $month;
        }
        if($amount!=$delivery->amount){//如果修改后的金额发生变化 需要检测金额是否超出
            if(!$this->deliveryRepository->
            checkDeliveryAmount($businessTeamData->id,$amount,$businessTeamData->active_amount,$delivery->amount)){
                throw new BusinessException('警告:录入的执行总额已经超过了该小组的实际金额！');
            }
            $data['amount']         =   $request->get('amount');
        }
        if(empty($data)){
            throw new BusinessException('没有任何数据修改!');
        }

        $this->deliveryRepository->update($id,$data);
        //记录日志
        $this->actionLog->log('delivery.edit',
                Admin::user()->id,
                [  'id'    =>$id,
                        'month' => $month,
                        'amount' => $amount,
                        'business_key'  =>$delivery->business_key   ,
                ]
        );
        DB::commit();
        return true;
    }

    /**
     *删除 月执行额
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function deleteDelivery($id=0,InvoiceDeliveryRepository $invoice,
                                   ExpensesDeliveyRepository $expenses,
                                   BackcashInvoiceRepository $backcash,
                                   PaymentExpensesRepository $payment,
                                   BadcashRepository $badcash){
        DB::beginTransaction();
        if(!$this->checkDeliveryRelation($id,$invoice,$expenses,$backcash,$payment,$badcash)){
            return $this->ajaxError('发现执行月有关联数据存在！请先删除相关数据后再操作！');
        }

        $delivery=$this->deliveryRepository->find($id);
        $delivery->update(['isshow'=>0]);

        //记录日志
        $this->actionLog->log('delivery.delete',
                Admin::user()->id,
                [  'id'    =>$id,
                        'month' => $delivery->month,
                        'amount' => $delivery->amount,
                        'business_key'  =>$delivery->business_key   ,
                ]
        );
        DB::commit();
        return $this->ajaxSuccess('删除成功！');
    }

    /**
     *  检测 业务小组执行月 下面是否还有关联数据
     *  涉及 发票 支出 回款 付款 坏账
     * @author YangWei<yangwei@foxmail.com>
     *
     */
    public function checkDeliveryRelation( $id,$invoice,
                                             $expenses,
                                             $backcash,
                                             $payment,
                                             $badcash)
    {
        if($invoice->findByDeliveryId($id)){return false;}
        if($expenses->findByDeliveryId($id)){return false;}
        if($backcash->findByDeliveryId($id)){return false;}
        if($payment->findByDeliveryId($id)){return false;}
        if($badcash->findByDeliveryId($id)){return false;}
        return true;
    }

}
