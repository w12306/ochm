<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TpspNetbar extends Model
{

    /**
     * @var string
     */
    protected $table = 'tpsp_netbar';

    /**
     * 所属二级广告位（又称广告位类型）
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'dqcity', 'name');
    }

}
