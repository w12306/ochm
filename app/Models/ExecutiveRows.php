<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


/**
 * 执行单 详细数据表
 *
 * @package App\Models
 */
class ExecutiveRows extends Model
{

    /**
     * @var string
     */
    protected $table = 'ad_executive_rows';

    protected $primaryKey = 'id';

    public $incrementing = true;

    /**
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'advertis_id',
		'advertis_json',
        'sell_type',
        'month',
		'ishotgame',
        'd1',
        'd2',
        'd3',
		'd4',
        'd5',
		'd6',
		'd7',
		'd8',
		'd9',
		'd10',
		'd11',
		'd12',
		'd13',
		'd14',
		'd15',
		'd16',
		'd17',
		'd18',
		'd19',
		'd20',
		'd21',
		'd22',
		'd23',
		'd24',
		'd25',
		'd26',
		'd27',
		'd28',
		'd29',
		'd30',
		'd31',
    ];

	public function executive(){
		return $this->belongsTo(Executive::class,'parent_id', 'id');
	}

}
