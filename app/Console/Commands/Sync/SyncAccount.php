<?php

namespace App\Console\Commands\Sync;

use Illuminate\Console\Command;
use App\Services\SyncApiDataService;

/**定时刷新本地用户的token
 * @package App\Console\Commands
 */
class SyncAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:refresh-account';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时刷新本地用户的token';

    /**
     * MakeAccount constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @author YangWei<yangwei@stnts.com>
     *
     * @param SyncApiDataService $sdk
     */
    public function handle(SyncApiDataService $sdk)
    {
        $sdk->refresh_token();
        $this->info("更新账号完成!");

    }
}
