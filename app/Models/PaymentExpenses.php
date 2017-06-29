<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**付款信息关联 支出信息表
 * Class InvoiceModel
 * @package App\Models
 */
class PaymentExpenses extends Model
{
    /**
     * @var string
     */
    protected $table = 'payment_expenses';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'payment_id',
            'payment_key',
            'expenses_id',
            'expenses_delivery_id',
            'expenses_type',
            'delivery_id',
            'team_id',
            'partner_id',
            'cooperation_type',
            'under_partner_id',
            'active_amount',
            'team',
            'team_month',
            'team_amount',
            'business_key',
            'remark',
            'isshow',
    ];

    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
    ];

    public function expenses_delivey()
    {
        return $this->belongsTo(ExpensesDeliveyModel::class,'expenses_delivery_id','id' );
    }

    public function payment()
    {
        return $this->belongsTo(Payment::class,'payment_id','id' );
    }

    public function business(){
        return $this->belongsTo(BusinessModel::class,'business_key','business_key' );
    }

    public function delivery(){
        return $this->belongsTo(DeliveryModel::class,'delivery_id','id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function expensess()
    {
        return $this->belongsTo(ExpensesModel::class,'expenses_id','id' );
    }


}
