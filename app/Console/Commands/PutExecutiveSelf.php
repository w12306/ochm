<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExecutiveInfo;

/**
 * 手动同步广告部的 排期数据
 * Class PutExecutive
 * @package App\Console\Commands
 */
class PutExecutiveSelf extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */

    protected $signature = 'put:ext {--date=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '手动同步广告部的 排期数据';

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
        if (!$date = $this->option('date')) {
            $date = $this->ask("请输入一个开始同步数据的日期(格式如：2016-01-01)：");
        }
        $this->info('开始往广告部5.5机器上推送排期数据...');
        $executiveInfo->saveData($date);

        $this->comment('处理完成!');

    }
}
