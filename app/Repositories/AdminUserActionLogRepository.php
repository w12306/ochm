<?php

namespace App\Repositories;

use Housekeeper\Action;
use Housekeeper\Eloquent\BaseRepository;
use App\Models\AdminUserActionLog;
use DB;
use App\Exceptions\BusinessException;
use Illuminate\Contracts\Foundation\Application;

/**
 * 管理员操作日志仓库
 *
 * @package App\Repositories
 */
class AdminUserActionLogRepository extends BaseRepository
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
        return AdminUserActionLog::class;
    }

}
