<?php

namespace App\Services;

use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Writers\LaravelExcelWriter;
use Maatwebsite\Excel\Classes\LaravelExcelWorksheet;
use Illuminate\Support\Collection;

/**
 * 负责生成数据Excel
 *
 * @author  AaronLiu <liukan0926@stnts.com>
 * @package App\Services\Admin
 */
class ExcelMaker
{
    /**
     * @var Excel
     */
    protected $excel;

    /**
     *
     */
    public function __construct(Excel $excel)
    {
        $this->excel = $excel;
    }

    /**
     * 将数据转换为Excel格式
     *
     * @param array            $headers
     * @param array|Collection $rows
     * @return LaravelExcelWriter
     */
    public function makeExcel(array $headers, $rows, $filename = null)
    {
        $filename = $filename ?: (date('Y-m-d_H:i:s') . '_' . mt_rand(1000, 9999));

        return $this->excel->create($filename, function ($excel) use (&$headers, &$rows) {
            /**
             * @var $excel LaravelExcelWriter
             */
            $excel
                ->setCreator('ABMP')
                ->setCompany('Stnts Co.,Ltd.');

            $excel->sheet('default', function ($sheet) use (&$headers, &$rows) {
                /**
                 * @var $sheet LaravelExcelWorksheet
                 */
                $sheet->setOrientation('landscape');

                $sheet->rows([$headers]);

                $sheet->rows($rows);

            });

        });
    }

}