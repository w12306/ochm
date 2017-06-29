<?php

namespace App\Console\Commands\Sync;

use Illuminate\Console\Command;
use App\Services\SyncApiDataService;

/**定时同步标列表到本地
 * @package App\Console\Commands
 */
class SyncLoands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sync:loands';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时同步标列表到本地';

    /**
     * MakeAccount constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param SyncApiDataService $sdk
     */
    public function handle(SyncApiDataService $sdk)
    {
        //dd($sdk->getUserList());
        $sdk->whileToSet3();

    }
}
