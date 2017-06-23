<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\BusinessTeamRepository;
use App\Repositories\ContractFilesRepository;
use App\Repositories\DictionaryRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Controller;
use App\Traits\Controller\CommonResponse;
use App\Services\Admin\UploadServiceManager;
use App\Exceptions\BusinessException;
use Symfony\Component\Finder\SplFileInfo;
use File;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Validator;
use DB;
use App\Models\Contract;
use App\Repositories\ContractRepository;
use App\Repositories\BusinessRepository;
use App\Services\Admin\UploadService;
use App\Services\ExcelMaker;
use Admin;
use App\Services\Admin\ActionLog;

/**
 * 合同管理
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Http\Controllers\Admin
 */
class ContractController extends Controller
{
    use CommonResponse;

    /**
     * @var ContractRepository
     */
    protected $contractRepository;

    /**
     * @var BusinessRepository
     */
    protected $businessRepository;

    protected $businessTeamRepository;

    protected $dictionaryRepository;

    protected $contractFilesRepository;

    /**
     * @var UploadService
     */
    protected $uploadService;

    /**
     * @var ActionLog
     */
    protected $actionLog;


    /**
     * @param ContractRepository   $contractRepository
     * @param BusinessRepository   $businessRepository
     * @param UploadServiceManager $uploadServiceManager
     * @param ActionLog            $actionLog
     */
    public function __construct(ContractRepository $contractRepository,
                                BusinessTeamRepository $businessTeamRepository,
                                DictionaryRepository $dictionaryRepository,
                                BusinessRepository $businessRepository,
                                UploadServiceManager $uploadServiceManager,
                                ContractFilesRepository $contractFilesRepository,
                                ActionLog $actionLog)
    {
        $this->actionLog          = $actionLog;
        $this->contractRepository = $contractRepository;
        $this->businessRepository = $businessRepository;
        $this->businessTeamRepository=$businessTeamRepository;
        $this->dictionaryRepository=$dictionaryRepository;
        $this->uploadService      = $uploadServiceManager->getUploadService();
        $this->contractFilesRepository=$contractFilesRepository;
    }

    /**
     *获得 登录人所有拥有的业务 同事获得登录人创建的合同得到关联业务
     * 返回所有能查看的业务ID
     * @author YangWei<yangwei@stnts.com>
     *
     * @return string
     */
    public function authContract(){
        $businessIds=$this->businessRepository->getUserBusinessIds();//获得所拥有的业务ID
        $contractIds=[];
        //获得属于登录用书创建的合同id
        $loginUserId=Admin::user()->id;
        $userContracts = $this->contractRepository
                ->applyWhere([
                    ['userid','=',$loginUserId]
                ])
                ->all();
        if(!$userContracts->isEmpty()) {
            foreach ($userContracts as $d) {
                $contractIds[$d->id] = $d->id;
                if(!empty($d->parent_id)){
                    $contractIds[$d->parent_id] = $d->parent_id;
                }
            }
        }

        //获得业务权限上属于这个用户的合同id
        if(!empty($businessIds)) {
            $businessContracts = $this->contractRepository
                    ->with(['businesses'])
                    ->applyWhere([
                            function ($query) use ($businessIds) {
                                $query->whereHas('businesses', function ($subQuery) use ($businessIds) {
                                    $subQuery->whereRaw('business_id in (' . $businessIds . ')');
                                });
                            }
                    ])
                    ->all();
            if (!$businessContracts->isEmpty()) {
                foreach ($businessContracts as $d) {
                    $contractIds[$d->id] = $d->id;
                    if(!empty($d->parent_id)){
                        $contractIds[$d->parent_id] = $d->parent_id;
                    }
                }
            }
        }
        if(!empty($contractIds)){
            return implode(',',$contractIds);
        }
        return '';
    }



    /**
     * 合同列表
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function getList(Request $request)
    {
        $renderData = [];

        //获得通用数据 ----------------------------------------------------------

        //合同类型
        $renderData['contractTypeList'] = Contract::$typeList;

        //查询条件 --------------------------------------------------------------

        $where = [];

        //如果需要检索合同类型
        $queryContractTypes = $request->get('contract_types', []);
        if ( ! empty($queryContractTypes)) {
            $where[] = function ($query) use ($queryContractTypes) {
                $query->whereIn('type', $queryContractTypes);
            };
        }
        $renderData['queryContractTypes'] = $queryContractTypes;

        //签约时间段过滤
		$btime=	$request->get('btime');
		$etime=	$request->get('etime');
		if ( !empty($btime) && !empty($etime) ) {
			$where[] = function ($query) use ($btime,$etime) {
				$query->whereRaw(' signtime  between "'.$btime.'" and "'.$etime.'"');
			};
		}

        //业务编号
        if ($request->has('business_key')) {
            $queryBusinessKey = $request->get('business_key');
            //通过业务编号获得业务ID
            try {
                $business = $this->businessRepository->findByKey($queryBusinessKey);
                $where[] = function ($query) use ($business) {
                    $query->whereHas('businesses', function ($subQuery) use ($business) {
                        $subQuery->where('business_id', '=', $business->id);
                    });
                };
            } catch (ModelNotFoundException $e) {
                $where[] = ['id', '=', 0];
            }
        }
        ///合同查看权限限制////////
        if(!session('isSystem')){
            $contractIds=$this->authContract();//获得所拥有的合同ID
            if(empty($contractIds)){
                $contractIds='0';
            }
            $where[] = function ($query) use ($contractIds) {
                $query->whereRaw('id in ('. $contractIds.')');
            };
        }
        //查询数据 --------------------------------------------------------------
        $contracts = $this->contractRepository
            ->with(['businesses'])
            ->applyWhere($where)
            ->applyOrder('updated_at', 'desc')
            ->paginate(15);
        $renderData['contracts'] = $contracts;

        return view('admin.contract.list', $renderData);
    }

    /**
     * 合同内容详细页面
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function detail($id){
        $data=$this->contractRepository
                ->with(['businesses','contractfiles'])
                ->find($id);
        $returnArr['data']  =$data;
        //获得所属框架合同
        $parent_id=$data->parent_id;
        $returnArr['parent']="";
        if(!empty($parent_id)){
            $parent=$this->contractRepository->find($parent_id);
            $returnArr['parent']=$parent->ckey;
        }
        return view('admin.contract.detail',$returnArr);
    }

    /**
     * 合同列表导出Excel
     *
     * @param Request    $request
     * @param ExcelMaker $excelMaker
     */
    public function getExportExcel(Request $request, ExcelMaker $excelMaker)
    {
        //获取数据
        $contracts = $this->contractRepository
            ->with(['businesses'])
            ->all();

        //表头
        $headers = [
            '合同编号',
            '相关业务',
            '签约时间',
            '合同金额',
            '合同补充内容',
            '合同类型',
        ];

        //格式化数据
        $rows = $contracts->map(function ($contract) {
            return [
                $contract->ckey,
                $contract->businesses->pluck('business_key')->toBase()->implode(','),
                $contract->signtime,
                $contract->amount,
                $contract->remark,
                $contract->type_text,
            ];
        });

        $excel = $excelMaker->makeExcel($headers, $rows);

        $excel->download('xls');
    }

    /**
     * 添加合同
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getAdd()
    {
        return view('admin.contract.edit');
    }

    /**
     * 保存合同
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     * @throws BusinessException
     */
    public function postStore(Request $request)
    {
        DB::beginTransaction();

        $contract = $this->createOrUpdateContract($request->all());
        $this->actionLog->log('contract.create',
            Admin::user()->id,
            array_merge($contract->toArray())
        );

        DB::commit();

        return $this->ajaxSuccess('添加合同成功。', [
            'url' => route('admin.contract.list')
        ]);
    }

    /**
     * 添加/更新 合同及其相关联的业务
     *
     * @param array $inputs
     * @param null  $contractId
     * @return \Illuminate\Database\Eloquent\Model|mixed|null
     * @throws BusinessException
     */
    protected function createOrUpdateContract(array $inputs, $contractId = null)
    {
        DB::beginTransaction();

        if(empty($contractId) && $inputs['type']!=1){
            if($inputs['type']!=1){
                $inputs['ckey']=$inputs['ckey'];//暂时不需要自动加上合同前缀
                //$this->madeCkey($businessesIds[0],$inputs['ckey']);
            }
        }

        //检查新的合同号是否和其他合同有重复
        if ($this->contractRepository->isCkeyExists($inputs['ckey'], $contractId)) {
            throw new BusinessException('该合同号已存在');
        }

        $oldContract = $contractId ?$this->contractRepository->find($contractId):null;

        $oldcontractbusiness=null;
        if(!empty($contractId)){
            $oldcontractbusiness=$this->contractRepository->with('businesses')->find($contractId);
        }
        /**
         * 根据合同类型分别处理保存逻辑
         */
        $contract = null;
        switch ($inputs['type']) {
            case Contract::TYPE_FRAMEWORK:
                /**
                 * 处理框架合同
                 */
                $contract = $this->saveFrameworkContract($inputs, $oldContract);

                break;
            case Contract::TYPE_SUB:
                /**
                 * 处理子合同
                 */
                $contract = $this->saveSubContract($inputs, $oldContract);

                break;
            case Contract::TYPE_INDEPENDENT:
                /**
                 * 处理独立合同
                 */
                $contract = $this->saveIndependentContract($inputs, $oldContract);

                break;
            default:
                throw new BusinessException('不存在的合同类型：' . $inputs['type']);
                break;
        }
        //编辑关联业务是 会增加或减少业务 需要同步更新业务的数据
        $this->updateBusinessContract($contract->id,$inputs,$oldcontractbusiness);

        //关联上传的文件
        if (empty($contract) || empty($contract->id)) {
            throw new BusinessException('合同信息保存失败！，请检查');
        }
        //保存上传的文件到正式目录//2016-06-03 支持多个文件上传
        if(isset($inputs['filepath'])) {
            $files = $inputs['filepath'];
            if (count($files) > 5) {
                return $this->ajaxError('最多只能上传五个文件！');
            }
            //查询改合同 已经上传过的文件
            $filesArr = $this->contractFilesRepository->getFilesArrByContractId($contract->id);
            foreach ($files as $path) {
                if (!empty($filesArr) && isset($filesArr[$path])) {
                    continue;
                }
                $filepath = $this->uploadService->saveUploaded($path);
                if (!empty($filepath)) {
                    $fileData['contract_id'] = $contract->id;
                    $fileData['filepath'] = $filepath;
                    $this->contractFilesRepository->create($fileData);
                }
            }
        }

        DB::commit();

        return $contract;
    }

    /**
     * 更新合同时 更新被关联的业务数据中的合同信息
     * @author YangWei<yangwei@stnts.com>
     *
     */
    protected function updateBusinessContract($contract_id,$newcontract,$oldcontractbusiness){
        $new_businessesIds = array_filter(explode(',', $newcontract['business_ids']));
        if(empty($oldcontractbusiness)){//新增合同的时候
            foreach($new_businessesIds as $k=>$bid){
                $updata=['contract_type'=>$newcontract['type'],'contract_id'=>$contract_id];
                $this->businessRepository->update($bid,$updata);
            }
        }else{//编辑合同的时候

            $old_businessids=[];
            foreach($oldcontractbusiness->businesses as $business ){
                $old_businessids[]=$business->id;
            }

            //判断有没有被编辑掉的业务
            foreach($old_businessids as $k=>$bid){
                if(!in_array($bid,$new_businessesIds)){
                    $updata=['contract_type'=>0,'contract_id'=>''];
                    $this->businessRepository->update($bid,$updata);
                }
            }
            //判断有没有编辑时新增的业务
            foreach($new_businessesIds as $k=>$bid){
                if(!in_array($bid,$old_businessids)){
                    $updata=['contract_type'=>$newcontract['type'],'contract_id'=>$contract_id];
                    $this->businessRepository->update($bid,$updata);
                }
            }
        }
    }

    /**
     * 更新旧合同的关联数据
     *
     * @param null|Contract $oldContract
     * @param               $saveData
     * @throws BusinessException
     */
    protected function cleanUpOldRelatedData($oldContract, $saveData)
    {
        /**
         * 当修改合同类型时…
         */
        if ($oldContract && $oldContract->type != $saveData['type']) {
            switch ($oldContract->type) {
                case Contract::TYPE_FRAMEWORK:
                    /**
                     * 如果老合同是框架合同，则删除所有业务关联及所有子合同
                     */
                    $oldContract->businesses()->detach();

                    $subContracts = $this->contractRepository
                        ->applyWhere([
                            ['parent_id', '=', $oldContract->id]
                        ])
                        ->all(['id']);
                    foreach ($subContracts as $subContract) {
                        $this->contractRepository->delete($subContract->id);
                    }

                    break;
                case Contract::TYPE_SUB:
                    /**
                     * 如果老合同是子合同则删除其关联的业务，并更新其父级合同关联的业务
                     */
                    $oldContract->businesses()->detach();

                    $this->contractRepository
                        ->refreshBusinessRelation($oldContract->parent_id);

                    break;
                case Contract::TYPE_INDEPENDENT:
                    /**
                     * 如果老合同是子合同则删除其关联的业务
                     */
                    $oldContract->businesses()->detach();

                    break;
                default:
                    throw new BusinessException('不存在的合同类型：' . $saveData['type']);
                    break;
            }
        } elseif (
            $oldContract &&
            $oldContract->type == Contract::TYPE_SUB &&
            $oldContract->parent_id != $saveData['parent_id']
        ) {
            /**
             * 当变更子合同的所属父级框架合同时，刷新旧父级框架关联的业务
             */
            $this->contractRepository
                ->refreshBusinessRelation($oldContract->parent_id);
        }
    }

    /**
     * 添加/更新 框架合同
     *
     * @param array         $inputs
     * @param null|Contract $oldContract
     * @return mixed
     */
    protected function saveFrameworkContract(array $inputs, $oldContract = null)
    {
        $saveData = array_only($inputs, [
            'type',
            'ckey',
            'signtime',
            'btime',
            'etime',
            'amount',
            'filepath',
            'remark',
        ]);

        //新增合同设置默认状态
        if ( ! $oldContract) {
            $saveData['status'] = Contract::STATUS_SENDED;
        }

        //默认为空的字段
        $saveData['parent_id'] = null;

        //创建人id
        $saveData['userid'] = Admin::user()->id;

        //复制上传的文件到永久保存目录
        //$saveData['filepath'] = $this->uploadService->saveUploaded($saveData['filepath']);
        if(isset($saveData['filepath'])){
            $saveData['filepath']   =count($saveData['filepath']);
        }

        $contract = $oldContract ?
            $this->contractRepository->update($oldContract->id, $saveData) :
            $this->contractRepository->create($saveData);

        //更新旧合同的关联数据
        $this->cleanUpOldRelatedData($oldContract, $saveData);

        return $contract;
    }

    /**
     * 添加/更新 子合同
     *
     * @param array         $inputs
     * @param null|Contract $oldContract
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws BusinessException
     */
    protected function saveSubContract(array $inputs, $oldContract = null)
    {
        $saveData = array_only($inputs, [
            'type',
            'ckey',
            'signtime',
            'btime',
            'etime',
            'amount',
            'filepath',
            'remark',
        ]);

        //新增合同设置默认状态
        if ( ! $oldContract) {
            $saveData['status'] = Contract::STATUS_SENDED;
        }

        //判断父级框架合同是否存在
        $queryParentId = $inputs['parent_id'];
        if ( ! $this->contractRepository->isFrameworkContractById($queryParentId)) {
            throw new BusinessException('父级框架合同不存在');
        }
        $saveData['parent_id'] = $queryParentId;

        //创建人id
        $saveData['userid'] = Admin::user()->id;

        //复制上传的文件到永久保存目录
        //$saveData['filepath'] = $this->uploadService->saveUploaded($saveData['filepath']);
        if(isset($saveData['filepath'])){
            $saveData['filepath']   =count($saveData['filepath']);
        }

        //保存合同
        $contract = $oldContract ?
            $this->contractRepository->update($oldContract->id, $saveData) :
            $this->contractRepository->create($saveData);


        //更新旧合同的关联数据
        $this->cleanUpOldRelatedData($oldContract, $saveData);

        //保存合同和业务的关系
        $businesses = $this->getBusinessByIdCSVStrictly($inputs['business_ids']);
        $contract->businesses()->detach();
        $contract->businesses()->attach($businesses->pluck('id')->toArray());

        //更新父级框架合同关联的业务编号
        $this->contractRepository->refreshBusinessRelation($queryParentId);

        return $contract;
    }

    /**
     * 添加/更新 独立合同
     *
     * @param array         $inputs
     * @param null|Contract $oldContract
     * @return \Illuminate\Database\Eloquent\Model|mixed
     * @throws BusinessException
     */
    protected function saveIndependentContract(array $inputs, $oldContract = null)
    {
        $saveData = array_only($inputs, [
            'type',
            'ckey',
            'signtime',
            'btime',
            'etime',
            'amount',
            'filepath',
            'remark',
        ]);

        //新增合同设置默认状态
        if ( ! $oldContract) {
            $saveData['status'] = Contract::STATUS_SENDED;
        }

        //设置默认字段
        $saveData['parent_id'] = null;

        //创建人id
        $saveData['userid'] = Admin::user()->id;

        //复制上传的文件到永久保存目录
        //$saveData['filepath'] = $this->uploadService->saveUploaded($saveData['filepath']);
        if(isset($saveData['filepath'])){
            $saveData['filepath']   =count($saveData['filepath']);
        }


        //保存合同
        $contract = $oldContract ?
            $this->contractRepository->update($oldContract->id, $saveData) :
            $this->contractRepository->create($saveData);

        //更新旧合同的关联数据
        $this->cleanUpOldRelatedData($oldContract, $saveData);

        //保存合同和业务的关系
        $businesses = $this->getBusinessByIdCSVStrictly($inputs['business_ids']);
        $contract->businesses()->detach();
        $contract->businesses()->attach($businesses->pluck('id')->toArray());

        return $contract;
    }

    /**
     * 通过（逗号）分隔的业务ID字符串获得业务信息，默认只取业务ID字段
     *
     * @param        $idCSV
     * @param array  $columns
     * @param string $separator
     * @return mixed
     * @throws BusinessException
     */
    protected function getBusinessByIdCSVStrictly($idCSV, $columns = ['id'], $separator = ',')
    {
        $businessesIds = array_filter(explode($separator, $idCSV));
        if (empty($businessesIds)) {
            throw new BusinessException('请输入合同需要关联的合同编号');
        }

        $businesses = $this->businessRepository->getIn('id', $businessesIds, $columns);

        if ($businesses->isEmpty()) {
            throw new BusinessException('填写的合同编号不存在，请检查');
        }
        return $businesses;
    }

    /**
     * 修改合同
     *
     * @param Request $request
     * @param         $contractKey
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getEdit(Request $request, $contractKey)
    {
        $contract = $this->contractRepository->find($contractKey);

        return view('admin.contract.edit', compact('contract'));
    }

    /**
     * 获得 新增/编辑 合同的表单数据
     *
     * @param Request $request
     * @param null    $contractId
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getApiGetContractFormData(Request $request, $contractId = null)
    {
        $returnData = [
            'id'                     => '',
            'ckey'                   => '',
            'type'                   => [],
            'framework_contract_ids' => [],
            'business_ids'           => [],
            'signtime'               => '',
            'btime'                  => '',
            'etime'                  => '',
            'amount'                 => '',
            'savetime'               => '',
            'filepath'               => '',
            'remark'                 => '',
        ];

        //如果是编辑合同则用对应的合同信息填充数据
        $contract      = null;
        $businessesIds = collect();
        $business_ids=[];//业务数据下拉框数据
        if ($contractId) {
            $contract = $this->contractRepository
                ->with('businesses','contractfiles')
                ->find($contractId);

            $returnData['id']     = $contractId;
            $returnData['ckey']     = $contract->ckey;
            $returnData['signtime'] = $contract->signtime;
            $returnData['btime']    = $contract->btime;
            $returnData['etime']    = $contract->etime;
            $returnData['amount']   = $contract->amount;
            $returnData['savetime'] = $contract->savetime;
            $returnData['filepathnum'] = $contract->filepath;//文件个数
            $returnData['remark']   = $contract->remark;

            //组织关联的合同文件数组
            $filesArr=[];
            foreach($contract->contractfiles as $f){
                $filesArr[$f->id]=$f->filepath;
            }
            $returnData['filepath'] =$filesArr;
            //框架合同是间接关联到业务的，所以不需要传递给前端显示
            if ( ! $contract->isFramework()) {
                $businessesIds = $contract->businesses->pluck('id')->toBase();
            }
            foreach($contract->businesses as $bus){
                $business_ids[]=[
                    'key'  => $bus->id,
                    'name' => $bus->business_key,
                    's'    => ($businessesIds->search($bus->id) !== false ? 1 : 0),
                ];
            }
        }

        //获得所有合同类型并格式化为前端的数据格式
        $returnData['type'] = collect(Contract::$typeList)
            ->map(function ($value, $key) use (&$contract) {
                return [
                    'key'   => $key,
                    'value' => $value,
                    's'     => (($contract && $key == $contract->type) || ( ! $contract && $key == 2) ? 1 : 0),
                ];
            })
            ->values();

        //获得所有框架合同并格式化为前端的数据格式
        $returnData['framework_contract_ids'] = $this->contractRepository
            ->allFrameworkContracts(['id', 'ckey'], $contractId)
            ->map(function ($frameworkContract) use (&$contract) {
                return [
                    'key'  => $frameworkContract['id'],
                    'name' => $frameworkContract['ckey'],
                    's'    => (($contract && $contract->parent_id == $frameworkContract->id) ? 1 : 0),
                ];
            })
            ->values();

        //获得所有合同编号并格式化为前端的数据格式
        //查询出已经关联了业务的合同数据
        $contract_used = $this->contractRepository->with('businesses')->all();

        //得到已经被合同关联的业务数据
        $lostb=[];
        foreach($contract_used as $k){
            foreach($k->businesses as $b){
                $lostb[$b->business_key]=$b->id;
            }

        }
        //查询全部的业务数据
        $businesses_all= $this->businessRepository->applyWhere([
            ['audit_status','=',1],
            ['del_time','=','0000-00-00 00:00:00']
        ])->all(['id', 'business_key']);

        //根据已经被合同关联的业务数据和全部业务数据 过滤出 还未关联合同的业务
        foreach($businesses_all as $k){
            if(!isset($lostb[$k->business_key])){
                $business_ids[]=[
                    'key'  => $k->id,
                    'name' => $k->business_key,
                    's'    => ($businessesIds->search($k->id) !== false ? 1 : 0),
                ];
            }
        }
        $returnData['business_ids']=$business_ids;

        return $this->ajaxSuccess('', $returnData);
    }

    /**
     * 更新合同
     *
     * @param Request $request
     * @param         $contractCkey
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postUpdate(Request $request, $contractCkey)
    {
        DB::beginTransaction();

        $oldContract = $this->contractRepository->find($contractCkey);

        $contract = $this->createOrUpdateContract($request->all(), $oldContract->id);

        $this->actionLog->log('contract.edit',
            Admin::user()->id,
            array_merge($contract->toArray(), [
                'company_id' => 0,//$contract->business->partner_id
            ]),
            $oldContract,
            $contract
        );

        DB::commit();

        return $this->ajaxSuccess('修改合同成功。', [
            'url' => route('admin.contract.list')
        ]);
    }

    /**
     * 下载电子版合同
     *
     * @param $contractCkey
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws BusinessException
     */
    public function getDownloadDigitizedContract($fileid)
    {
        $file = $this->contractFilesRepository->find($fileid);

        $fileinfo = new \SplFileInfo(base_path(
            env('RESOURCE_RELATIVE_PATH') .
            $file->filepath
        ));

        try {
            return response()
                ->download(
                    $fileinfo,
                    date('Ymd') . "_".$fileinfo->getFilename()
                );
        } catch (FileNotFoundException $e) {
            throw new BusinessException('电子版合同文件不存在');
        }
    }

    /**
     * 合同存档接口
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function postApiArchive(Request $request)
    {
        $contract = $this->contractRepository->find($request->get('id'));

        //是否已存档
        if ($contract->status == Contract::STATUS_ARCHIVED) {
            return $this->ajaxError('该合同已存档');
        }

        $this->contractRepository->update($contract->id, [
            'status' => Contract::STATUS_ARCHIVED
        ]);

        return $this->ajaxSuccess('合同已存档');
    }

    /**
     *
     * 生成合同编号 规则: 业务所属部门的编号+业务类型+手填合同编号（如有所个业务任选一个）
     * @author YangWei<yangwei@stnts.com>
     *
     * @param $business_id
     * @param $rkey
     * @return string
     * @throws BusinessException
     */
    public function madeCkey($business_id,$rkey){
        //获得业务类型
        $business=$this->businessRepository->getBusinessById($business_id);
        $business_type=$business->business_type;

        //获得业务的所属部门
        $businessTeam=$this->businessTeamRepository->getTeamByBusinessId($business_id);
        $team=$this->dictionaryRepository->find($businessTeam->team);
        $deptCode=$team->v2;
        if(empty($deptCode) || empty($business_type) || empty($rkey)){
            throw new BusinessException('生成合同编号失败:'.$deptCode.'-'.$business_type.'-'.$rkey);
        }
        return $deptCode.'-'.$business_type.'-'.$rkey;
    }

    /**
     * 编辑合同  删除合同的附属文件
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function delContractFile(Request $request){
        $contractid=$request->input('contractid');
        $file_id    =$request->input('fileid');
        DB::beginTransaction();
        $contractFiles=$this->contractFilesRepository->find($file_id);

        if(!$this->uploadService->deleteUploadFile($contractFiles->filepath)){
            return $this->ajaxError('删除失败!');
        }
        $contractFiles->delete($file_id);

        /*$contract=$this->contractRepository->find($contractid);
        $contract->update($contractid, [
                'filepath' => $contract->filepath+1
        ]);*/
        DB::commit();
        return $this->ajaxSuccess('删除成功!');
    }

}
