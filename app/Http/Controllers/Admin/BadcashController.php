<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\DictionaryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Exceptions\BusinessException;
use Symfony\Component\Finder\SplFileInfo;
use File;
use Illuminate\Support\Str;
use Validator;
use DB;
use App\Models\Badcash;
use App\Repositories\BadcashRepository;
use App\Repositories\PartnersRepository;
use App\Repositories\BusinessTeamRepository;
use App\Repositories\DeliveryRepository;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use App\Services\ExcelMaker;
use App\Services\Admin\ActionLog;
use Admin;

/**
 * 坏账管理
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers\Admin
 */
class BadcashController extends Controller
{
    use CommonResponse;

    /**
     * @var BadcashRepository
     */
    protected $badcashRepository;

    /**
     * @var PartnersRepository
     */
    protected $partnersRepository;

    /**
     * @var BusinessTeamRepository
     */
    protected $businessTeamRepository;

    /**
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    protected $dictionaryRepository;

    protected $actionLog;

    /**
     * @param BadcashRepository      $badcashRepository
     * @param PartnersRepository     $partnersRepository
     * @param DeliveryRepository     $deliveryRepository
     * @param BusinessTeamRepository $businessTeamRepository
     */
    public function __construct(BadcashRepository $badcashRepository,
                                PartnersRepository $partnersRepository,
                                DeliveryRepository $deliveryRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                DictionaryRepository $dictionaryRepository,
                                ActionLog $actionLog)
    {
        $this->badcashRepository      = $badcashRepository;
        $this->partnersRepository     = $partnersRepository;
        $this->deliveryRepository     = $deliveryRepository;
        $this->businessTeamRepository = $businessTeamRepository;
        $this->dictionaryRepository   = $dictionaryRepository;
        $this->actionLog              = $actionLog;
    }

    /**
     *组织页面查询条件
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function seachWhere($request){
        $where = [];
        //已选择的合作方
        if ($request->has('partner_id_csv')) {
            $queryPartnerIdCSV = $request->input('partner_id_csv');
            $queryPartnerIds   = array_filter(explode(',', $queryPartnerIdCSV));
            $where[]           = function ($query) use ($queryPartnerIds) {
                /**
                 * @var $query QueryBuilder|EloquentBuilder
                 */
                $query->whereHas('business', function ($query) use ($queryPartnerIds) {
                    /**
                     * @var $query QueryBuilder|EloquentBuilder
                     */
                    $query->whereIn('partner_id', $queryPartnerIds);
                });
            };
        }

        $where[]    = function ($query)  {
            $query->whereHas('business', function ($query) {
                $query->whereRaw(session('company_sql'));
            });
        };
        $where[]    = function ($query)  {
            $query->whereHas('businessTeam', function ($query) {
                $query->whereRaw(session('team_sql'));
            });
        };

        //业务编号
        if ($request->has('business_key')) {
            $queryBusinessKey = $request->input('business_key');
            $where[]          = function ($query) use ($queryBusinessKey) {
                /**
                 * @var $query QueryBuilder|EloquentBuilder
                 */
                $query->whereHas('business', function ($query) use ($queryBusinessKey) {
                    /**
                     * @var $query QueryBuilder|EloquentBuilder
                     */
                    $query->where('business_key', '=', $queryBusinessKey);
                });
            };
        }

        //录入坏账时间（段）
        if ($request->has('created_at_begin') || $request->has('created_at_end')) {
            $where[] = function ($query) use (&$request) {
                $whereTime = static::getWhereConditionForTimeSpan(
                        'created_at',
                        'created_at',
                        $request->input('created_at_begin'),
                        $request->input('created_at_end')
                );

                /**
                 * @var $query QueryBuilder|EloquentBuilder
                 */
                $query->whereHas('badcash', function ($query) use (&$whereTime) {
                    call_user_func_array(
                            [$query, 'where'],
                            (($whereTime instanceof \Closure) ? [$whereTime] : $whereTime)
                    );
                });
            };
        }
        return $where;
    }

    /**
     * 坏账列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $renderData = [];

        //获得通用数据 ----------------------------------------------------------

        //获得所有合作方信息
        $renderData['partners'] = $this->partnersRepository->getAllPartversArray();
        //查询小组所属的部门
        $renderData['teamDept']=$this->dictionaryRepository->getDeptTeam();
        //查询条件 --------------------------------------------------------------
        $where=$this->seachWhere($request);

        //查询数据 --------------------------------------------------------------
        //查询所有执行月信息，附加上坏账信息，如果没有坏账信息则显示“添加坏账”，如果有则显示
        //“编辑”、“删除”。
        $deliveries=$this->badcashRepository->with([
                'business',
                'businessTeam',
                'delivery',
                'business.product',
                'business.partner',
                'delivery.invoice_deliveys',
                'delivery.backcash_invoices',
                'delivery.expenses_deliveys',
        ])
                ->applyWhere($where)
                ->applyOrder('created_at', 'desc')
                ->paginate(15);

        $renderData['deliveries'] = $deliveries;

        return view('admin.badcash.list', $renderData);
    }


    /**
     * 列表导出Excel
     *
     * @param Request    $request
     * @param ExcelMaker $excelMaker
     */
    public function getExportExcel(Request $request, ExcelMaker $excelMaker)
    {
        $where=$this->seachWhere($request);
        $where[]= ['isshow','=',1];
        //获取数据
        $badcash=$this->badcashRepository->with([
                'business',
                'businessTeam',
                'delivery',
                'business.product',
                'business.partner',
                'delivery.invoice_deliveys',
                'delivery.backcash_invoices',
                'delivery.expenses_deliveys',
        ])
        ->applyWhere($where)
        ->applyOrder('created_at', 'desc')
        ->all();

        //表头
        $headers = [
                '业务编号',
                '执行小组',
                '执行月份',
                '合作方',
                '产品名称',
                '实际金额拆分',
                '执行金额',
                '发票总额',
                '回款总额',
                '支出总额',
                '约定结款时间',
                '回款状态',
                '坏账金额',
                '坏账录入时间',
        ];
        //格式化数据
        $rows = $badcash->map(function ($data) {
            $invoice_total=0.00;
            foreach ($data->delivery->invoice_deliveys as $de){
                $invoice_total=$invoice_total+$de->active_amount;
            }
            $invoice_total=number_format($invoice_total,2);

            $backcash_total=0.00;
            foreach ($data->delivery->backcash_invoices as $de){
                $backcash_total=$backcash_total+$de->active_amount;
            }
            $backcash_total=number_format($backcash_total,2);

            $expenses_total=0.00;
            foreach ($data->delivery->expenses_deliveys as $de){
                $expenses_total=$expenses_total+$de->active_amount;
            }
            $expenses_total=number_format($expenses_total,2);

            return [
                    $data->business->business_key,
                    $data->businessTeam->team,
                    $data->delivery->month,
                    $data->business->partner->company_name,
                    $data->business->product->name,
                    $data->businessTeam->active_amount,
                    $data->businessTeam->amount,
                    $invoice_total,
                    $backcash_total,
                    $expenses_total,
                    $data->business->paytime,
                    $data->businessTeam->backcashstatus_text,
                    $data->amount,
                    $data->created_at->toDatetimeString(),
            ];
        });



        $excel = $excelMaker->makeExcel($headers, $rows);

        $excel->download('xls');
    }

    /**
     *录入坏账页面
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getAdd(Request $request){
        $parent_id=$request->get('partner_id');
        $business_key=$request->get('business_key');

        $where = [];
        $bad_where = [];
        //已选择的合作方
        if ($request->has('partner_id')) {
            $where[]           = function ($query) use ($parent_id) {
                $query->whereHas('businessTeam.business', function ($query) use ($parent_id) {
                    $query->where('partner_id', $parent_id);
                });
            };
            $bad_where[]           = function ($query) use ($parent_id) {
                $query->whereHas('business', function ($query) use ($parent_id) {
                    $query->where('partner_id', $parent_id);
                });
            };

        }
		//过滤已经删除的数据
		$where[]           = function ($query) {
				$query->where('isshow','=',1);
		};

        $where[]           = function ($query) {
            $query->whereHas('businessTeam.business', function ($query) {
                $query->whereRaw(session('company_sql'));
            });
        };
        $bad_where[]           = function ($query) {
            $query->whereHas('business', function ($query) {
                $query->whereRaw(session('company_sql'));
            });
        };

        $where[]           = function ($query) {
            $query->whereHas('businessTeam', function ($query) {
                $query->whereRaw(session('team_sql'));
            });
        };
        $bad_where[]           = function ($query) {
            $query->whereHas('businessTeam', function ($query) {
                $query->whereRaw(session('team_sql'));
            });
        };

        //业务编号
        if ($request->has('business_key')) {
            $queryBusinessKey = $request->input('business_key');
            $where[]          = function ($query) use ($queryBusinessKey) {
                $query->whereHas('businessTeam.business', function ($query) use ($queryBusinessKey) {
                    $query->where('business_key', '=', $queryBusinessKey);
                });
            };
            $bad_where[]          = function ($query) use ($queryBusinessKey) {
                $query->whereHas('business', function ($query) use ($queryBusinessKey) {
                    $query->where('business_key', '=', $queryBusinessKey);
                });
            };
        }
        //加载合作方数据
        $viewData['partnerData']=$this->partnersRepository->getPartnersList();
        //查询小组所属的部门
        $viewData['teamDept']=$this->dictionaryRepository->getDeptTeam();

        $viewData['deliveries']=[];
        if(!empty($parent_id) || !empty($business_key) ){
            //查询数据 --------------------------------------------------------------
            //查询所选择的条件下的 未录入坏账的执行月信息
            //“编辑”、“删除”。
            $deliveries               = $this->deliveryRepository
                    ->with([
                            'badcash',
                            'businessTeam',
                            'businessTeam.deliverys',
                            'businessTeam.business',
                            'businessTeam.business.product',
                            'businessTeam.business.partner',
                            'invoice_deliveys',
                            'backcash_invoices',
                            'expenses_deliveys',
                    ])
                    ->applyWhere($where)
                    ->applyOrder('created_at', 'desc')
                    ->paginate(15);
            $viewData['deliveries'] = $deliveries;
            //查询这个条件下已经录入过的坏账信息
            $badcash=$this->badcashRepository->with(['business','businessTeam'])
                    ->applyWhere($bad_where)
                    ->applyOrder('updated_at', 'desc')
                    ->all();
            $badHasArr=[];
            foreach($badcash as $bd){
                $badHasArr[$bd->business_id.$bd->team_id.$bd->delivery_id]=true;
            }
            $viewData['badHasArr'] = $badHasArr;
        }
        return view('admin.badcash.add', $viewData);
    }

    /**
     * 保存、更新坏账
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    public function postSave(Request $request)
    {
        if ( ! $request->has('id')) {
            /**
             * 添加坏账
             * 一个执行月只能关联一条坏账记录，需要检查防止重复添加
             */

            $queryDeliveryId    = $request->get('delivery_id');
            $queryBadcashAmount = $request->get('badcash_amount');

            DB::beginTransaction();

            if ($this->badcashRepository->existsByDeliveryId($queryDeliveryId)) {
                throw new BusinessException('该执行月下已添加坏账信息，同一执行月只能存在一条坏账信息');
            }
            if(!$this->badcashRepository->checkBadAmount($queryDeliveryId,$queryBadcashAmount)){
                throw new BusinessException('警告:录入的坏账金额不能大于‘执行金额’！');
            }
            $delivery=$this->deliveryRepository->getById($queryDeliveryId);

            $badcash = $this->badcashRepository->create([
                        'amount'      => $queryBadcashAmount,
                        'delivery_id' => $queryDeliveryId,
                        'team_id'     => $delivery->team_id,
                        'business_id' => $delivery->business_id,
            ]);

            $badcash->updateKey();
            //记录日志
            try{
                $this->actionLog->log('badcash.create',
                        Admin::user()->id,
                        [  'id'    =>$badcash->id,
                           'amount'  =>$queryBadcashAmount ,
                        ]
                );
            }catch (Exception $e){
                DB::rollBack();
            }
            DB::commit();

            return $this->ajaxSuccess('操作成功！',['url'=>route('admin.badcash.list')]);
        } else {
            /**
             * 更新坏账
             */

            $queryId            = $request->input('id');
            $queryBadcashAmount = $request->get('badcash_amount');
            $badcash = $this->badcashRepository->find($queryId);

            if(!$this->badcashRepository->checkBadAmount($badcash->delivery_id,$queryBadcashAmount)){
                throw new BusinessException('警告:录入的坏账金额不能大于‘执行金额’！');
            }

            $this->badcashRepository->update($badcash->id, [
                'amount' => $queryBadcashAmount
            ]);
            $this->actionLog->log('badcash.edit',
                    Admin::user()->id,
                    [  'id'    =>$queryId,
                       'amount'  =>$queryBadcashAmount ,
                    ]
            );

            return $this->ajaxSuccess('操作成功！',['url'=>route('admin.badcash.list')]);
        }
    }

    /**
     * 删除坏账
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postDelete(Request $request)
    {
        $queryId = $request->input('id');

        $this->badcashRepository->deleteBadcash($queryId);
        $this->actionLog->log('badcash.delete',
                Admin::user()->id,
                [  'id'    =>$queryId,
                   'amount'  =>0 ,
                ]
        );

        return $this->ajaxSuccess('删除成功!');
    }

}
