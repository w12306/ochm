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
 * 历史操作记录管理
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers\Admin
 */
class ActionLogController extends Controller
{
    use CommonResponse;

    /**
     * @var AdminUserRepository
     */
    protected $adminUserRepository;

    /**
     * @var DictionaryRepository
     */
    protected $dictionaryRepository;

    /**
     * @var AdminUserTeamRepository
     */
    protected $adminUserTeamRepository;

    /**
     * @var AdminUserActionLogRepository
     */
    protected $adminUserActionLogRepository;

    /**
     * @var BusinessRepository
     */
    protected $businessRepository;

    /**
     * @var DeliveryRepository
     */
    protected $deliveryRepository;

    /**
     * @var ContractRepository
     */
    protected $contractRepository;

    /**
     * @var BadcashRepository
     */
    protected $badcashRepository;

    /**
     * @var EarnestcashRepository
     */
    protected $earnestcashRepository;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var CompanyRepository
     */
    protected $companyRepository;

    /**
     * @var UnderCompanyRepository
     */
    protected $underCompanyRepository;

    /**
     * @var PartnersRepository
     */
    protected $partnersRepository;

    /**
     * @var UnderPartnersRepository
     */
    protected $underPartnersRepository;

    /**
     * @var StandExpensesRepository
     */
    protected $standExpensesRepository;


    protected $invoiceModel;

    protected $backcashModel;

    protected $expensesModel;

    protected $payment;

    protected $advancecash;

    protected $standPaymentRepository;


    /**
     * @param AdminUserRepository  $adminUserRepository
     * @param DictionaryRepository $dictionaryRepository
     */
    public function __construct(AdminUserRepository $adminUserRepository,
                                AdminUserTeamRepository $adminUserTeamRepository,
                                AdminUserActionLogRepository $adminUserActionLogRepository,
                                BusinessRepository $businessRepository,
                                DeliveryRepository $deliveryRepository,
                                ContractRepository $contractRepository,
                                BadcashRepository $badcashRepository,
                                EarnestcashRepository $earnestcashRepository,
                                ProductRepository $productRepository,
                                CompanyRepository $companyRepository,
                                UnderCompanyRepository $underCompanyRepository,
                                PartnersRepository $partnersRepository,
                                StandExpensesRepository $standExpensesRepository,
                                StandPaymentRepository $standPaymentRepository,
                                UnderPartnersRepository $underPartnersRepository,
                                DictionaryRepository $dictionaryRepository,
                                InvoiceModel $invoiceModel,
                                BackcashModel $backcashModel,
                                ExpensesModel $expensesModel,
                                Payment $payment,
                                Advancecash $advancecash)
    {
        $this->adminUserRepository          = $adminUserRepository;
        $this->adminUserActionLogRepository = $adminUserActionLogRepository;
        $this->adminUserTeamRepository      = $adminUserTeamRepository;
        $this->dictionaryRepository         = $dictionaryRepository;
        $this->businessRepository           = $businessRepository;
        $this->deliveryRepository           = $deliveryRepository;
        $this->contractRepository           = $contractRepository;
        $this->badcashRepository            = $badcashRepository;
        $this->earnestcashRepository        = $earnestcashRepository;
        $this->productRepository            = $productRepository;
        $this->companyRepository            = $companyRepository;
        $this->underCompanyRepository       = $underCompanyRepository;
        $this->partnersRepository           = $partnersRepository;
        $this->underPartnersRepository      = $underPartnersRepository;
        $this->standExpensesRepository      = $standExpensesRepository;
        $this->invoiceModel                 = $invoiceModel;
        $this->backcashModel                = $backcashModel;
        $this->expensesModel                = $expensesModel;
        $this->payment                      = $payment;
        $this->advancecash                  = $advancecash;
        $this->standPaymentRepository       = $standPaymentRepository;
    }

    /**
     * 历史操作列表
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $renderData = [];

        //合作方数据
        $companies               = $this->companyRepository->all();
        $renderData['companies'] = $companies;

        //获得查询条件 ----------------------------------------------------------

        $where = [];

        //按管理员ID查询
        if ($request->has('admin_user_real_name')) {
            $where[] = function ($query) use ($request) {
                $query->whereHas('adminUser', function ($subQuery) use ($request) {
                    $subQuery
                        ->where('real_name', 'like', '%' . $request->input('admin_user_real_name') . '%');
                });
            };
        }

        //按操作时间查询
        $where[] = static::getWhereConditionForTimeSpan(
            'created_at',
            'created_at',
            $request->input('btime'),
            $request->input('etime')
        );

        //按模块查询
        if ($request->has('module')) {
            $queryModule = $request->input('module');

            $where[] = ['module', '=', $queryModule];
        }

        //按日志记录的数据主键搜索
        if ($request->has('object_data')) {
            $queryObjectData = $request->input('object_data');

            $where[] = ['main_id', '=', $queryObjectData];
        }

        //按合作方搜索
        if ($request->has('company_id')) {
            $queryCompanyId = $request->input('object_data');

            $where[] = ['company_id', '=', $queryCompanyId];
        }

        //获得通用数据 ----------------------------------------------------------

        $modules               = config('services.actionLog.modules');
        $renderData['modules'] = $modules;

        //获得数据 -------------------------------------------------------------

        $logs = $this->adminUserActionLogRepository
            ->with(['adminUser'])
            ->applyWhere($where)
            ->applyOrder('created_at', 'desc')
            ->paginate(15);

        //附加日志对应的真实数据信息
        $this->attachMainObjectDataToLogs($logs);

        $renderData['logs'] = $logs;
        return view('admin.action-log.list', $renderData);
    }

    /**
     * TODO 待完善
     * 附加日志对应的真实数据信息
     *
     * @param $logs
     * @throws \Exception
     */
    protected function attachMainObjectDataToLogs(&$logs)
    {
        foreach ($logs as $key => $log) {
            $mainId     = $log->main_id;
            $mainObject = null;

            try {
                switch ($log->module) {
                    case 'business':
                        $mainObject = $this->businessRepository->find($mainId);
                        break;
                    case 'delivery':
                        $mainObject = $this->deliveryRepository->find($mainId);
                        break;
                    case 'contract':
                        $mainObject = $this->contractRepository->find($mainId);
                        break;
                    case 'invoice':
                        $mainObject = $this->invoiceModel->where('id',$mainId)->first();
                        break;
                    case 'backcash':
                        $mainObject = $this->backcashModel->where('id',$mainId)->first();
                        break;
                    case 'expenses':
                        $mainObject = $this->expensesModel->where('id',$mainId)->first();
                        break;
                    case 'payment-expenses':
                        $mainObject = $this->payment->where('id',$mainId)->first();
                        break;
                    case 'stand-payment-expenses':
                        $mainObject = $this->standPaymentRepository->find($mainId);
                        break;
                    case 'badcash':
                        $mainObject = $this->badcashRepository->find($mainId);
                        break;
                    case 'earnestcash':
                        $mainObject = $this->earnestcashRepository->find($mainId);
                        break;
                    case 'advancecash':
                        $mainObject = $this->advancecash->find($mainId);
                        break;
                    case 'product':
                        $mainObject = $this->productRepository->find($mainId);
                        break;
                    case 'company':
                        $mainObject = $this->companyRepository->find($mainId);
                        break;
                    case 'under-company':
                        $mainObject = $this->underCompanyRepository->find($mainId);
                        break;
                    case 'partner':
                        $mainObject = $this->partnersRepository->find($mainId);
                        break;
                    case 'under-partner':
                        $mainObject = $this->underPartnersRepository->find($mainId);
                        break;
                    case 'permission-role':
                        $mainObject = Role::find($mainId);
                        break;
                    case 'admin-user':
                        $mainObject = $this->adminUserRepository->find($mainId);
                        break;
                    case 'stand-expenses':
                        $mainObject = $this->standExpensesRepository->find($mainId);
                        break;
                    default:
                        throw new \InvalidArgumentException('日志指向了不存在的模块');
                        break;
                }
            } catch (ModelNotFoundException $e) {
                $mainObject = null;
            }
            $logs[$key]['mainObject'] = $mainObject;
        }
    }

    /**
     * 获取历史操作记录列表页不同功能模块需要显示的控件信息接口
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postModuleWidget(Request $request)
    {
//        $returnData1 = [
//            'column' => '合作方',
//            'type'   => 'select',
//            'data'   => [
//                [
//                    'key'   => '1',
//                    'value' => '你好',
//                ],
//                [
//                    'key'   => '2',
//                    'value' => '你不好',
//                ],
//            ],
//        ];
//
//        $returnData2 = [
//            'column' => '业务编号',
//            'type'   => 'text',
//        ];

        $queryModule = $request->input('id');

        $returnData = [];

        switch ($queryModule) {
            case 'business':
                $returnData = [
                    'column' => '业务ID',
                    'type'   => 'text',
                ];
                break;
            case 'delivery':
                $returnData = [
                    'column' => '业务执行月ID',
                    'type'   => 'text',
                ];
                break;
            case 'contract':
                $returnData = [
                    'column' => '合同ID',
                    'type'   => 'text',
                ];
                break;
            case 'invoice':
                $returnData = [
                    'column' => '发票ID',
                    'type'   => 'text',
                ];
                break;
            case 'backcash':
                $returnData = [
                    'column' => '回款ID',
                    'type'   => 'text',
                ];
                break;
            case 'expenses':
                $returnData = [
                    'column' => '开支ID',
                    'type'   => 'text',
                ];
                break;
            case 'payment-expenses':
                $returnData = [
                    'column' => '付款开支ID',
                    'type'   => 'text',
                ];
                break;
            case 'badcash':
                $returnData = [
                    'column' => '坏账ID',
                    'type'   => 'text',
                ];
                break;
            case 'earnestcash':
                $returnData = [
                    'column' => '保证金ID',
                    'type'   => 'text',
                ];
                break;
            case 'product':
                $returnData = [
                    'column' => '产品ID',
                    'type'   => 'text',
                ];
                break;
            case 'company':
                $returnData = [
                    'column' => '客户ID',
                    'type'   => 'text',
                ];
                break;
            case 'under-company':
                $returnData = [
                    'column' => '下游客户ID',
                    'type'   => 'text',
                ];
                break;
            case 'partner':
                $returnData = [
                    'column' => '合作方ID',
                    'type'   => 'text',
                ];
                break;
            case 'under-partner':
                $returnData = [
                    'column' => '下游合作方ID',
                    'type'   => 'text',
                ];
                break;
            case 'permission-role':
                $returnData = [
                    'column' => '角色ID',
                    'type'   => 'text',
                ];
                break;
            case 'admin-user':
                $returnData = [
                    'column' => '管理员ID',
                    'type'   => 'text',
                ];
                break;
            default:
                throw new \InvalidArgumentException('日志指向了不存在的模块');
                break;
        }

        $returnData['name'] = 'object_data';

        return $this->ajaxSuccess('', $returnData);
    }


}
