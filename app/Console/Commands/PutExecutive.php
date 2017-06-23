<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExecutiveInfo;

/**定时给广告部的数据库推送业务排期明细数据
 * Class PutExecutive
 * @package App\Console\Commands
 */
class PutExecutive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'put:executive';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '定时给广告部的数据库推送业务排期明细数据';

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
     * @param ExecutiveInfo $executiveInfo
     */
    public function handle(ExecutiveInfo $executiveInfo)
    {
        $this->info('开始往广告部5.5机器上推送排期数据...');
        $executiveInfo->saveData();
        $this->comment('处理完成!');

    }
}
