<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;


/**业务小组资金分配
 * Class BusinessTeamModel
 * @package App\Models
 */
class BusinessTeamModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'business_team';

    protected $primaryKey = 'id';

    public $incrementing = true;
    protected $touches = ['business'];//更新子表 update字段会刷新主表的update字段

    /**
     * @var array
     */
    protected $fillable = [
            'business_id',
            'business_key',
            'team',
            'amount',
            'active_amount',
            'isshow',
            'invoice_status',
            'backcash_status',
    ];

    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
            'created_at',
    ];

    /**
     * 发票状态
     *
     * @var array
     */
    static public $invoiceStatusList = [
            0 => '未开全',
            1 => '已开全',
    ];

    /**
     * 回款状态
     *
     * @var array
     */
    static public $backcashStatusList = [
            0 => '未结清',
            1 => '已结清',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function business()
    {
        return $this->belongsTo(BusinessModel::class );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function badcash()
    {
        return $this->hasMany(Badcash::class ,'team_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function deliverys()
    {
        return $this->hasMany(DeliveryModel::class,'team_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoice_deliveys()
    {
        return $this->hasMany(InvoiceDeliveyModel::class,'team_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function backcash_invoices()
    {
        return $this->hasMany(BackcashInvoiceModel::class,'team_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function expenses_deliveys()
    {
        return $this->hasMany(ExpensesDeliveyModel::class,'team_id','id');
    }



    /**
     * @return string
     */
    public function getBackcashStatusTextAttribute()
    {
        return static::$backcashStatusList[$this->attributes['backcash_status']];
    }

    /**
     * @return string
     */
    public function getInvoiceStatusTextAttribute()
    {
        return static::$invoiceStatusList[$this->attributes['invoice_status']];
    }

}
