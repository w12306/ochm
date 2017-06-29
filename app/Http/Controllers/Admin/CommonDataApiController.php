<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Repositories\AreaRepository;
use App\Traits\Controller\CommonResponse;
use Cache;
use DB;

/**
 * 公用数据接口
 *
 * @package App\Http\Controllers\Admin
 */
class CommonDataApiController extends Controller
{

    use CommonResponse;

    /**
     * @var AreaRepository
     */
    protected $areaRepository;


    /**
     * @param AreaRepository $areaRepository
     */
    public function __construct(AreaRepository $areaRepository)
    {
        $this->areaRepository = $areaRepository;
    }

    /**
     * 得到地区数据
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function getArea()
    {
        $cacheKey = 'common-areas';

        $cached = Cache::get($cacheKey);
        if ( ! is_null($cached)) {
            return $this->ajaxSuccess('', $cached);
        }

        $returnData = [
            'alist' => [
                [
                    'id'    => 1,
                    'name'  => '全国',
                    'pid'   => 0,
                    'level' => 1,
                ]
            ],
            'plist' => [],
            'clist' => [],
        ];

        $provinceAreas = $this->areaRepository
            ->applyWhere([
                ['level', '=', 2],
            ])
            ->all();
        $cityAreas     = $this->areaRepository
            ->applyWhere([
                ['level', '=', 3],
            ])
            ->all();

        $provinceAreaArr = $provinceAreas->map(function ($area) {
            return [
                'id'    => (string)$area->id,
                'name'  => $area->name,
                'pid'   => (string)$area->pid,
                'level' => (string)$area->level,
            ];
        })->toBase()->groupBy('pid');

        $cityAreaArr = $cityAreas->map(function ($area) {
            return [
                'id'    => (string)$area->id,
                'name'  => $area->name,
                'pid'   => (string)$area->pid,
                'level' => (string)$area->level,
            ];
        })->toBase()->groupBy('pid');

        $returnData['plist'] = $provinceAreaArr;
        $returnData['clist'] = $cityAreaArr;

        Cache::put($cacheKey, $returnData, 60);

        return $this->ajaxSuccess('', $returnData);
    }

}
