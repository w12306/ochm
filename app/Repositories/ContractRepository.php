<?php

namespace App\Repositories;

use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\Contract;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 合同
 *
 * @package App\Repositories
 */
class ContractRepository extends BaseRepository
{

    /**
     * @var BusinessRepository
     */
    protected $businessRepository;


    /**
     * @param Application        $application
     * @param BusinessRepository $businessRepository
     */
    public function __construct(Application $application,
                                BusinessRepository $businessRepository)
    {
        parent::__construct($application);

        $this->businessRepository = $businessRepository;
    }

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return Contract::class;
    }

    /**
     * 根据合同 类型获得该类型下的所有合同
     *
     * @author YangWei<yangwei@stnts.com>
     * @param $type
     * @return \Illuminate\Support\Collection
     */
    public function getContractByType($type)
    {
        return $this->model->where('type', '=', $type)->lists('ckey', 'id');
    }

    /**
     * 根据框架合同获得下面的全部子合同
     *
     * @author YangWei<yangwei@stnts.com>
     * @param $type
     * @return \Illuminate\Support\Collection
     */
    public function getContractByParentId($pid)
    {
        return $this->model->where('parent_id', '=', $pid)->lists('ckey', 'id');
    }

    /**
     * 根据ckey得到合同信息
     *
     * @author YangWei<yangwei@stnts.com>
     * @param $type
     * @return \Illuminate\Support\Collection
     */
    public function getContractByCKey($ckey)
    {
        return $this->model->where('ckey', '=', $ckey)->all();
    }

    /**
     *获得所有合同的 id - ckey list
     *
     * @author YangWei<yangwei@stnts.com>
     *
     */
    public function getAllContractList()
    {
        $data      = $this->model->lists('id', 'ckey')->toArray();
        $returnArr = array();

        foreach ($data as $k => $v) {
            $returnArr[$v] = $k;
        }

        return $returnArr;
    }

    /**
     * 获得所有框架合同
     *
     * @param array $columns
     * @return \Illuminate\Database\Eloquent\Collection|mixed
     */
    public function allFrameworkContracts($columns = ['*'], $exceptId = null)
    {
        return $this->wrap(function ($columns = ['*'], $exceptId = null) {

            $this->model = $this->model->where('type', '=', Contract::TYPE_FRAMEWORK);

            if ($exceptId) {
                $this->model = $this->model->where('id', '!=', $exceptId);
            }

            return $this->model->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 检查合同编号是否已存在
     *
     * @param  string     $ckey
     * @param null|string $exceptId
     * @return bool
     */
    public function isCkeyExists($ckey, $exceptId = null)
    {
        return $this->wrap(
            array($this, '_isCkeyExists'),
            new Action(__METHOD__, func_get_args(), Action::READ)
        );
    }

    /**
     * @param      $ckey
     * @param null $exceptId
     * @return bool
     */
    protected function _isCkeyExists($ckey, $exceptId = null)
    {
        $this->model = $this->model->where('ckey', '=', $ckey);

        if ($exceptId) {
            $this->model = $this->model->where('id', '!=', $exceptId);
        }

        return $this->model->exists();
    }

    /**
     * 检查对应合同编号判断是不是框架合同
     *
     * @param $ckey
     * @return mixed
     */
    public function isFrameworkContractByCkey($ckey)
    {
        return $this->wrap(function ($ckey) {

            return $this->model
                ->where('ckey', '=', $ckey)
                ->where('type', '=', Contract::TYPE_FRAMEWORK)
                ->exists();

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 检查对应合同ID判断是不是框架合同
     *
     * @param $id
     * @return mixed
     */
    public function isFrameworkContractById($id)
    {
        return $this->wrap(function ($id) {

            return $this->model
                ->where('id', '=', $id)
                ->where('type', '=', Contract::TYPE_FRAMEWORK)
                ->exists();

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 刷新合同和业务的关系
     * 如果是框架合同，则获得其所有子合同的业务编号然后关联到这个框架合同
     *
     * @param $contractId
     * @return mixed|void
     */
    public function refreshBusinessRelation($contractId)
    {
        return $this->wrap(function ($contractId) {
            DB::beginTransaction();

            $contract = $this->model->findOrFail($contractId, ['id', 'type']);

            if ($contract->isFramework()) {
                $businessIds = $this->model
                    ->with('businesses')
                    ->where('parent_id', '=', $contract->id)
                    ->get(['id'])
                    ->pluck('businesses')
                    ->collapse()
                    ->pluck('id')
                    ->toBase()
                    ->unique()
                    ->toArray();

                $contract->businesses()->detach();
                $contract->businesses()->attach($businessIds);


            }

            DB::commit();
        }, new Action(__METHOD__, func_get_args(), Action::UPDATE));
    }


    public function saveCt($ctid,$business_id){
        DB::beginTransaction();
        $contract=$this->model->find($ctid);
        //保存合同和业务的关系
        $businesses = $this->businessRepository->find($business_id);
        $contract->businesses()->detach();
        $contract->businesses()->attach($business_id);
        DB::commit();
    }


}
