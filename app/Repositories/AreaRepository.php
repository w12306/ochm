<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use Housekeeper\Traits\Repository\Cacheable;
use App\Models\Area;
use Housekeeper\Action;

class AreaRepository extends BaseRepository
{
    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return Area::class;
    }

    /**
     * @param array $ids
     * @param array $columns
     * @return mixed
     */
    public function allByIds(array $ids, $columns = ['*'])
    {
        return $this->wrap(function ($ids, $columns = ['*']) {

            return $this->model->whereIn('id', $ids)->get($columns);

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 获得地区数据，按照前端的通用格式组成树状格式
     *
     * @return mixed
     */
    public function asTree()
    {
        return $this->wrap(function () {

            $areas = $this->all();

            $dataTree = new \ArrayTree\Tree($areas->toArray());

            $dataTree->setIdKey('id');
            $dataTree->setParentIdKey('pid');
            $dataTree->setResultChildKey('children');
            $dataTree->setResultParentIdsKey('parent_ids');

            return $dataTree->getArrayTree();

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }


}
