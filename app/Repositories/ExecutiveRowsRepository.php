<?php

namespace App\Repositories;

use App\Models\ExecutiveRows;
use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 执行单 详细数据
 *
 * @package App\Repositories
 */
class ExecutiveRowsRepository extends BaseRepository
{

    /**
     * @param Application $application
     */
    public function __construct(Application $application)
    {
        parent::__construct($application);
    }

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return ExecutiveRows::class;
    }

	/**
	 *批量删除执行单数据
	 * @author YangWei<yangwei@stnts.com>
	 *
	 * @param $parent_id
	 */
	public function deleteRows($parent_id){
		$this->model->where('parent_id','=',$parent_id)->delete();
	}
}
