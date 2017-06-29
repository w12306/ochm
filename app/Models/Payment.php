<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

/**付款主体信息内容
 * Class InvoiceModel
 * @package App\Models
 */
class Payment extends Model
{
    /**
     * @var string
     */
    protected $table = 'payment';

    protected $primaryKey = 'id';

    public $incrementing = true;


    protected $dates = ['created_at'];

    /**
     * @var array
     */
    protected $fillable = [
            'payment_key',
            'payment_time',
            'invoice_num',
            'invoice_amount',
            'invoice_type',
            'amount',
            'remark',
            'isshow',
    ];



    /**
     * @var array
     */
    protected $hidden = [
            'updated_at',
    ];

    public function payment_expensess()
    {
        return $this->hasMany(PaymentExpenses::class,'payment_id','id' );
    }

}
