<?php

namespace App\Repositories\Criterias;

use Housekeeper\Contracts\CriteriaInterface;
use Housekeeper\Contracts\RepositoryInterface;

/**
 * 从新到旧排序
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Repositories\Criterias
 */
class LatestCriteria implements CriteriaInterface
{

    /**
     * @param RepositoryInterface $repository
     */
    public function apply(RepositoryInterface $repository)
    {
        $repository->applyOrder('updated_at', 'DESC');
    }

}