<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use App\Http\Controllers\Controller as BaseController;

abstract class Controller extends BaseController
{

    /**
     * 获得时间段查询条件
     *
     * 很多地方带有时间段查询框，一个开始时间、一个结束时间，并且还可以不填写其中一个时间。
     * 这个函数就是专门生成这种查询条件的。
     * 例如被查询的字段叫“start_time”和“end_time”，需要查询2015-11-11之后的数据，那么
     * 在controller中可以这么写：
     * $where[] = static::getWhereConditionForTimeSpan(
     *     'start_time', 'end_time',
     *     '2015-11-11', null
     * );
     * 就可以获取查询条件了。将条件丢到Repository的applyWhere或者ORM的where方法即可。
     *
     * @author AaronLiu <liukan0926@foxmail.com>
     *
     * @param $startTimeColumn
     * @param $endTimeColumn
     * @param $startTime
     * @param $endTime
     * @return \Closure
     */
    static public function getWhereConditionForTimeSpan($startTimeColumn,
                                                        $endTimeColumn,
                                                        $startTime,
                                                        $endTime)
    {
        $returnWhere = function () {

        };

        if ($startTime != '' && $endTime != '') {
            $returnWhere = function ($query) use ($startTime, $endTime, $startTimeColumn, $endTimeColumn) {
                $query->where(function ($subQuery) use ($startTime, $endTime, $startTimeColumn, $endTimeColumn) {
                    $subQuery->where($startTimeColumn, '<=', $startTime)
                        ->where($endTimeColumn, '>=', $endTime);
                })
                    ->orWhere(function ($subQuery) use ($startTime, $endTime, $startTimeColumn) {
                        $subQuery->where($startTimeColumn, '<=', $endTime)
                            ->where($startTimeColumn, '>=', $startTime);
                    })
                    ->orWhere(function ($subQuery) use ($startTime, $endTime, $endTimeColumn) {
                        $subQuery->where($endTimeColumn, '<=', $endTime)
                            ->where($endTimeColumn, '>=', $startTime);
                    });
            };
        } elseif ($startTime != '') {
            $returnWhere = [$startTimeColumn, '>=', $startTime];
        } elseif ($endTime != '') {
            $returnWhere = [$endTimeColumn, '<=', $endTime];
        }

        return $returnWhere;
    }

    /**
     *生成编号 前面两个字符  后面8位数字 不够的补0
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param        $id
     * @param string $prefix
     * @param int    $length
     * @return bool|string
     */
    static public function makeNumber($id, $prefix = '', $length = 5)
    {
        $id_len = strlen($id);
        if ($id_len > $length) {
            return false;
        }
        $lostlen   = $length - $id_len;//获得还需要补多少个0
        $middleStr = '';
        for ($i = 0; $i < $lostlen; $i++) {
            $middleStr .= '0';
        }
        $prefix="";
        return $prefix . $middleStr . $id;
    }

    static public function serialInStr($str){
        if(strpos($str,',')===false){return '"'.$str.'"';}
        $strArr=explode(',',$str);
        $reStr='';
        foreach($strArr as $v){
            if(empty($reStr)){
                $reStr='"'.$v.'"';
            }else{
                $reStr=$reStr.',"'.$v.'"';
            }
        }
        return $reStr;
    }

    /**
     * 后台格式化数字函数
     * @author YangWei<yangwei@foxmail.com>
     *
     * @param $num
     * @param int $ext
     * @return string
     */
    static  public function numFormat($num,$ext=2){
        if(!is_numeric($num)){
            return $num;
        }
        $num=number_format($num,$ext);
        if($num=='-0.00'){
            $num='0.00';
        }
        return $num;
    }
}
