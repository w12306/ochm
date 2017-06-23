<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
/**
 * 坏账
 *
 * @package App\Models
 */
class Badcash extends Model
{

    /**
     * @var string
     */
    protected $table = 'badcash';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'badcash_key',
        'amount',
        'delivery_id',
        'team_id',
        'business_id',
        'isshow',
        'remark',
    ];


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function delivery()
    {
        return $this->belongsTo(
            DeliveryModel::class,
            'delivery_id',
            'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function business()
    {
        return $this->belongsTo(
                BusinessModel::class,
                'business_id',
                'id'
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function businessTeam()
    {
        return $this->belongsTo(
                BusinessTeamModel::class,
                'team_id',
                'id'
        );
    }


    /**
     * 更新单号
     */
    public function updateKey()
    {
        $this->badcash_key = sprintf('HZ%\'010d', $this->attributes['id']);
        $this->save();

        $this->fresh();
    }

}
