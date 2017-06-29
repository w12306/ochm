<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * 广告位
 *
 * @package App\Models
 */
class Advertis extends Model
{

    /**
     * @var string
     */
    protected $table = 'ad_advertis';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'name',
        'code',
        'max_rounds',
        'usually_price',
        'weekend_price',
        'usually_discount',
        'weekend_discount',
        'usually_value',
		'weekend_value',
        'remark',
		'ishotgame',
		'isshow',
		'sequence',
    ];

	public static $hotgames=[
			'热门游戏1',
			'热门游戏2',
			'热门游戏3',
			'热门游戏4',
			'热门游戏5',
			'热门游戏6',
			'热门游戏7',
			'热门游戏8',
			'热门游戏9',
			'热门游戏10',
	];
	public function getIsshowTextAttribute()
	{
		if($this->attributes['isshow']==1){
			return '生效中';
		}else{
			return '已删除';
		}
	}
}
