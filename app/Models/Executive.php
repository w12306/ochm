<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * 执行单 主表
 *
 * @package App\Models
 */
class Executive extends Model
{

    /**
     * @var string
     */
    protected $table = 'ad_executive';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'key',
		'name',
        'product_id',
        'pd_sname',
        'company_id',
        'partner_id',
        'business_cooperation',
        'target_type',
        'gid_string',
		'city_ids',
        'status',
		'business_id',
		'isshow',
        'bz',
        'updated_at',
    ];

	static public $sell_type=[
		['key'=>'1', 'value'=>'购买'],
		['key'=>"2", 'value'=>'配送'],
		['key'=>"3", 'value'=>'框架'],
		['key'=>"4", 'value'=>'额外支持'],
	];

    //创建执行单中的类型的颜色设置
    static public $sell_type_color=[
        "购买"=>'rgb(255, 114, 114)',
        "配送"=>'rgb(146, 208, 80)',
        "框架"=>'rgb(75, 186, 197)',
        "额外支持"=>'rgb(117, 152, 184)',
    ];
    //大排期中的类型的颜色设置
    static public $gather_sell_type_color=[
        "购买"=>'rgb(255, 0, 0)',
        "配送"=>'rgb(0, 176, 80)',
        "框架"=>'rgb(0, 176, 240)',
        "额外支持"=>'rgb(47, 117, 181)',
    ];




	public function getTargetTypeTextAttribute()
	{
		$target_type=['1'=>'全国','2'=>'城市','3'=>'网吧'];
		if(isset($target_type[$this->attributes['target_type']])){
			return $target_type[$this->attributes['target_type']];
		}else{
			return '';
		}
	}

	public function getStatusTextAttribute()
	{
		$status=['1'=>'草稿','2'=>'占坑','3'=>'下单'];
		if(isset($status[$this->attributes['status']])){
			return $status[$this->attributes['status']];
		}else{
			return '';
		}
	}

	public function partner(){
		return $this->belongsTo(PartnersModel::class,'partner_id', 'id');
	}
	public function company(){
		return $this->belongsTo(CompanyModel::class,'company_id', 'id');
	}
	public function product(){
		return $this->belongsTo(ProductModel::class,'product_id', 'id');
	}

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
	 */
	public function executive_rows()
	{
		return $this->hasMany(ExecutiveRows::class,'parent_id','id');
	}
}
