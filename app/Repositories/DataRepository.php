<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use App\Models\DataRepository as DataRepositoryModel;
use Housekeeper\Action;

/**
 * Class RepositoryRepository
 *
 * @package App\Repositories
 */
class DataRepository extends BaseRepository
{

    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return DataRepositoryModel::class;
    }

    /**
     * 判断某个内容是否存在
     *
     * @param $id
     * @return mixed
     */
    public function exists($id)
    {
        return $this->wrap(function ($id) {

            return $this->model->where('key', $id)->exists();

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

}
