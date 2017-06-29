<?php

namespace App\Repositories\Criterias;

use Housekeeper\Contracts\CriteriaInterface;
use Housekeeper\Contracts\RepositoryInterface;

/**
 * 普通状态条件
 *
 * @package App\Repositories\Criterias
 */
class NormalStatusCriteria implements CriteriaInterface
{

    /**
     * @param RepositoryInterface $repository
     */
    public function apply(RepositoryInterface $repository)
    {
        $repository->applyWhere([
            'status' => 'normal'
        ]);
    }



}