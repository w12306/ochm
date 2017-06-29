<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**支出关联月执行额信息内容
 * Class InvoiceModel
 * @package App\Models
 */
class ExpensesDeliveyModel extends Model
{
    /**
     * @var string
     */
    protected $table = 'expenses_delivey';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'expenses_id',
            'expenses_key',
            'delivey_id',
            'team_id',
            'partner_id',
            'under_partner_id',
            'active_amount',
            'team',
            'team_month',
            'team_amount',
            'business_key',
            'product_id',
            'isshow',
            'remark',
    ];

    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
    ];

    public function delivery()
    {
        return $this->belongsTo(DeliveryModel::class,'delivey_id','id' );
    }


    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function expenses()
    {
        return $this->belongsTo(ExpensesModel::class );
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business()
    {
        return $this->belongsTo(BusinessModel::class,'business_key','business_key' );
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function product()
    {
        return $this->belongsTo(ProductModel::class,'product_id' ,'id');
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payment_expensess()
    {
        return $this->hasMany(PaymentExpenses::class,'expenses_delivery_id' ,'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function under_partner()
    {
        return $this->belongsTo(UnderPartnersModel::class,'under_partner_id','id' );
    }

    /**
     *
     * @author YangWei<yangwei@foxmail.com>
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function business_team()
    {
        return $this->belongsTo(BusinessTeamModel::class, 'team_id', 'id' );
    }

}
