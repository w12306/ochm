<?php

namespace App\Repositories;

use Housekeeper\Eloquent\BaseRepository;
use App\Models\TpspNetbar;
use Housekeeper\Traits\Repository\Cacheable;
use Housekeeper\Action;
use DB;

class TpspNetbarRepository extends BaseRepository
{

    use Cacheable;


    /**
     * Return the name of model that this repository used.
     *
     * @return string
     */
    protected function model()
    {
        return TpspNetbar::class;
    }

    /**
     * 获得所有正常网吧的GID
     *
     * @return array
     */
    public function allGids()
    {
        return $this->wrap(function () {
            $gids = [];

            DB::table($this->model->getTable())
                ->select(['gid'])
                ->where('status', '=', '1')
                ->chunk(1000, function ($netbars) use (&$gids) {
                    foreach ($netbars as $netbar) {
                        $gids[] = $netbar->gid;
                    }
                });

            return $gids;

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

    /**
     * 获得指定地区的正常网吧GID
     *
     * @param array $cityNames
     * @return mixed
     */
    public function allByCityNames(array $cityNames)
    {
        return $this->wrap(function (array $cityNames) {

            $gids = [];

            DB::table($this->model->getTable())
                ->select(['gid'])
                ->whereIn('dqcity', $cityNames)
                ->where('status', '=', '1')
                ->chunk(1000, function ($netbars) use (&$gids) {
                    foreach ($netbars as $netbar) {
                        $gids[] = $netbar->gid;
                    }
                });

            return $gids;

        }, new Action(__METHOD__, func_get_args(), Action::READ));
    }

}
